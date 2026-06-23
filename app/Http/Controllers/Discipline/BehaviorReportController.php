<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\BehaviorRecord;
use App\Models\BehaviorRule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BehaviorReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Dropdown Filters Options
        $classrooms = Student::select('Classroom')->distinct()->orderBy('Classroom')->pluck('Classroom');
        $grades = Student::select('GradeLevel')->distinct()->orderBy('GradeLevel')->pluck('GradeLevel');

        // 2. Fetch parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $grade = $request->input('grade');
        $classroom = $request->input('classroom');

        // If no date range specified, default to start of this month to today
        if (!$startDate && !$endDate) {
            $startDate = Carbon::today()->startOfMonth()->toDateString();
            $endDate = Carbon::today()->toDateString();
        }

        // 3. Build Base Query for Behavior Records
        $recordsQuery = BehaviorRecord::query()
            ->join('behavior_rules', 'behavior_records.RuleID', '=', 'behavior_rules.RuleID')
            ->join('students', 'behavior_records.StudentID', '=', 'students.StudentID')
            ->where('behavior_records.Status', 'อนุมัติแล้ว');

        if ($startDate) {
            $recordsQuery->whereDate('behavior_records.RecordDate', '>=', $startDate);
        }
        if ($endDate) {
            $recordsQuery->whereDate('behavior_records.RecordDate', '<=', $endDate);
        }
        if ($grade) {
            $recordsQuery->where('students.GradeLevel', $grade);
        }
        if ($classroom) {
            $recordsQuery->where('students.Classroom', $classroom);
        }

        // 4. Calculate Behavior Record Stats
        $totalRecords = (clone $recordsQuery)->count();
        
        $demeritCount = (clone $recordsQuery)->where('behavior_rules.RuleType', 'ตัดคะแนน')->count();
        $demeritPoints = abs((clone $recordsQuery)->where('behavior_rules.RuleType', 'ตัดคะแนน')->sum('behavior_rules.ScoreModifier'));
        
        $meritCount = (clone $recordsQuery)->where('behavior_rules.RuleType', 'เพิ่มคะแนน')->count();
        $meritPoints = (clone $recordsQuery)->where('behavior_rules.RuleType', 'เพิ่มคะแนน')->sum('behavior_rules.ScoreModifier');

        // 5. Build Base Query for Students (current state, but filtered by grade/classroom)
        $studentsQuery = Student::query();
        if ($grade) {
            $studentsQuery->where('GradeLevel', $grade);
        }
        if ($classroom) {
            $studentsQuery->where('Classroom', $classroom);
        }

        $totalStudents = (clone $studentsQuery)->count();
        $avgScore = round((clone $studentsQuery)->avg('BehaviorScore') ?? 100, 1);

        $riskNormal = (clone $studentsQuery)->where('RiskStatus', 'ปกติ')->count();
        $riskWatch = (clone $studentsQuery)->where('RiskStatus', 'เฝ้าระวัง')->count();
        $riskCritical = (clone $studentsQuery)->where('RiskStatus', 'วิกฤต')->count();

        // 6. Behavior Category Breakdown for Chart
        $categoryCounts = (clone $recordsQuery)
            ->select('behavior_rules.Category')
            ->selectRaw('count(*) as count')
            ->groupBy('behavior_rules.Category')
            ->pluck('count', 'Category')
            ->toArray();

        // 7. Classroom summaries (Avg Score + merits/demerits count in range)
        $classroomStats = Student::query()
            ->select('Classroom', 'GradeLevel')
            ->selectRaw('count(*) as student_count')
            ->selectRaw('avg(BehaviorScore) as avg_score')
            ->when($grade, fn($q) => $q->where('GradeLevel', $grade))
            ->when($classroom, fn($q) => $q->where('Classroom', $classroom))
            ->groupBy('Classroom', 'GradeLevel')
            ->orderBy('Classroom')
            ->get();

        $classroomRecords = BehaviorRecord::query()
            ->join('students', 'behavior_records.StudentID', '=', 'students.StudentID')
            ->join('behavior_rules', 'behavior_records.RuleID', '=', 'behavior_rules.RuleID')
            ->select('students.Classroom')
            ->selectRaw("sum(case when behavior_rules.RuleType = 'ตัดคะแนน' then 1 else 0 end) as demerit_count")
            ->selectRaw("sum(case when behavior_rules.RuleType = 'เพิ่มคะแนน' then 1 else 0 end) as merit_count")
            ->where('behavior_records.Status', 'อนุมัติแล้ว')
            ->when($startDate, fn($q) => $q->whereDate('RecordDate', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('RecordDate', '<=', $endDate))
            ->groupBy('students.Classroom')
            ->get()
            ->keyBy('Classroom');

        foreach ($classroomStats as $class) {
            $records = $classroomRecords->get($class->Classroom);
            $class->demerit_count = $records ? $records->demerit_count : 0;
            $class->merit_count = $records ? $records->merit_count : 0;
            $class->avg_score = round($class->avg_score, 1);
        }

        // 8. Top Students lists
        $lowestScoreStudents = (clone $studentsQuery)
            ->orderBy('BehaviorScore', 'asc')
            ->take(10)
            ->get();

        $highestScoreStudents = (clone $studentsQuery)
            ->orderBy('BehaviorScore', 'desc')
            ->take(10)
            ->get();

        // 9. Most frequent rules violated/rewarded
        $frequentRules = BehaviorRecord::query()
            ->join('behavior_rules', 'behavior_records.RuleID', '=', 'behavior_rules.RuleID')
            ->join('students', 'behavior_records.StudentID', '=', 'students.StudentID')
            ->select('behavior_rules.RuleName', 'behavior_rules.RuleType', 'behavior_rules.Category')
            ->selectRaw('count(*) as record_count')
            ->selectRaw('abs(sum(behavior_rules.ScoreModifier)) as total_points')
            ->where('behavior_records.Status', 'อนุมัติแล้ว')
            ->when($startDate, fn($q) => $q->whereDate('RecordDate', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('RecordDate', '<=', $endDate))
            ->when($grade, fn($q) => $q->where('students.GradeLevel', $grade))
            ->when($classroom, fn($q) => $q->where('students.Classroom', $classroom))
            ->groupBy('behavior_rules.RuleName', 'behavior_rules.RuleType', 'behavior_rules.Category')
            ->orderByDesc('record_count')
            ->take(10)
            ->get();

        return view('discipline.behavior-report.index', compact(
            'classrooms', 'grades', 'startDate', 'endDate', 'grade', 'classroom',
            'totalRecords', 'demeritCount', 'demeritPoints', 'meritCount', 'meritPoints',
            'totalStudents', 'avgScore', 'riskNormal', 'riskWatch', 'riskCritical',
            'categoryCounts', 'classroomStats', 'lowestScoreStudents', 'highestScoreStudents',
            'frequentRules'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $grade = $request->input('grade');
        $classroom = $request->input('classroom');

        // Build query for behavior records details
        $query = BehaviorRecord::with(['student', 'rule', 'recorder'])
            ->where('Status', 'อนุมัติแล้ว');

        if ($startDate) {
            $query->whereDate('RecordDate', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('RecordDate', '<=', $endDate);
        }
        if ($grade || $classroom) {
            $query->whereHas('student', function ($q) use ($grade, $classroom) {
                if ($grade) $q->where('GradeLevel', $grade);
                if ($classroom) $q->where('Classroom', $classroom);
            });
        }

        $records = $query->orderBy('RecordDate', 'desc')->get();

        // 1. Export as Excel/CSV
        if ($request->has('excel')) {
            $fileName = "behavior_report_" . ($startDate ?: 'all') . "_to_" . ($endDate ?: 'all') . ".csv";
            $headers = [
                "Content-type"        => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename={$fileName}",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function () use ($records, $startDate, $endDate, $grade, $classroom) {
                $file = fopen('php://output', 'w');
                // Write UTF-8 BOM so Excel opens it with correct Thai characters
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Title header
                fputcsv($file, ["รายงานสรุปพฤติกรรมนักเรียน ฝ่ายปกครอง"]);
                fputcsv($file, ["ช่วงวันที่: " . ($startDate ?: 'ทั้งหมด') . " ถึง " . ($endDate ?: 'ทั้งหมด')]);
                fputcsv($file, ["ระดับชั้น: " . ($grade ?: 'ทั้งหมด') . " | ห้องเรียน: " . ($classroom ?: 'ทั้งหมด')]);
                fputcsv($file, []);

                // Column Headers
                fputcsv($file, ["ลำดับ", "วันที่บันทึก", "รหัสนักเรียน", "ชื่อ-สกุล", "ระดับชั้น/ห้อง", "ประเภทพฤติกรรม", "คะแนน", "คะแนนคงเหลือ", "รายละเอียด", "ครูประจำชั้น"]);

                // Data Rows
                $i = 1;
                foreach ($records as $row) {
                    $modifier = $row->rule->ScoreModifier;
                    if ($row->rule->RuleType === 'ตัดคะแนน') {
                        $modifier = -abs($modifier);
                    } else {
                        $modifier = abs($modifier);
                    }

                    fputcsv($file, [
                        $i++,
                        Carbon::parse($row->RecordDate)->format('d/m/Y'),
                        $row->student->StudentID,
                        $row->student->FullName,
                        $row->student->classroom_display,
                        $row->rule->RuleName . " (" . $row->rule->RuleType . ")",
                        $modifier,
                        $row->student->BehaviorScore,
                        $row->Description ?: '-',
                        $row->student->advisory_teacher->user->FullName ?? '-'
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // 2. Export as Printable HTML
        $reportTitle = "รายงานสรุปพฤติกรรมนักเรียน ฝ่ายปกครอง";
        $periodText = "ข้อมูลพฤติกรรมตั้งแต่วันที่ " . 
            ($startDate ? Carbon::parse($startDate)->locale('th')->isoFormat('D MMMM YYYY') : 'เริ่มต้นระบบ') . 
            " ถึง " . 
            ($endDate ? Carbon::parse($endDate)->locale('th')->isoFormat('D MMMM YYYY') : 'ปัจจุบัน');

        return view('discipline.behavior-report.export', compact('records', 'reportTitle', 'periodText', 'grade', 'classroom'));
    }
}
