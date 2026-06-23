<?php

namespace App\Http\Controllers\Prayer;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\PrayerRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PrayerController extends Controller
{
    // 1. Scan Page
    public function scan()
    {
        // Require role to be Discipline staff
        $role = strtolower(auth()->user()->Role);
        if (!in_array($role, ['ฝ่ายปกครอง', 'discipline'])) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าใช้งานหน้านี้');
        }

        // Auto determine period based on current time (Noon if < 14:00, otherwise Afternoon)
        $currentHour = now()->hour;
        $defaultPeriod = $currentHour < 14 ? 'เที่ยง' : 'บ่าย';

        return view('prayer.scan', compact('defaultPeriod'));
    }

    // 2. Store Scan Record (AJAX POST)
    public function store(Request $request)
    {
        $user = auth()->user();
        $role = strtolower($user->Role);

        $request->validate([
            'student_id' => 'required|string',
            'period'     => 'nullable|in:เที่ยง,บ่าย',
            'status'     => 'nullable|in:ละหมาด,ละหมาดไม่ได้',
        ]);

        $scannedInput = $request->input('student_id');
        $studentId = $scannedInput;
        $currentHour = now()->hour;
        $period = $request->input('period') ?: ($currentHour < 14 ? 'เที่ยง' : 'บ่าย');
        $status = $request->input('status') ?: 'ละหมาด';

        // 1. Try parsing JSON if QR Payload is passed (Format: {"id":"10001","period":"เที่ยง","status":"ละหมาด"})
        $decoded = json_decode($scannedInput, true);
        if (is_array($decoded) && isset($decoded['id'])) {
            $studentId = $decoded['id'];
            if (isset($decoded['period']) && in_array($decoded['period'], ['เที่ยง', 'บ่าย'])) {
                $period = $decoded['period'];
            }
            if (isset($decoded['status']) && in_array($decoded['status'], ['ละหมาด', 'ละหมาดไม่ได้'])) {
                $status = $decoded['status'];
            }
        } else {
            // 2. Try parsing delimited string from 1D Barcode (Format: {StudentID}-{noon/asr}-{pray/exempt})
            $parts = explode('-', $scannedInput);
            if (count($parts) === 3) {
                $studentId = $parts[0];
                
                if ($parts[1] === 'noon') {
                    $period = 'เที่ยง';
                } elseif ($parts[1] === 'asr') {
                    $period = 'บ่าย';
                }
                
                if ($parts[2] === 'pray') {
                    $status = 'ละหมาด';
                } elseif ($parts[2] === 'exempt') {
                    $status = 'ละหมาดไม่ได้';
                }
            }
        }

        // Authorize
        if (in_array($role, ['นักเรียน', 'student'])) {
            $myStudent = $user->student;
            if (!$myStudent || $myStudent->StudentID !== $studentId) {
                return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์บันทึกข้อมูลของนักเรียนคนอื่น'], 403);
            }
        } elseif (!in_array($role, ['ฝ่ายปกครอง', 'discipline'])) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์บันทึกข้อมูล'], 403);
        }

        // Find Student
        $student = Student::where('StudentID', $studentId)->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => "ไม่พบข้อมูลนักเรียน (รหัส: {$studentId})"
            ], 404);
        }

        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->toTimeString();

        // Find existing record or prepare a new one (do NOT overwrite PrayerRecordID on update)
        $record = PrayerRecord::where([
            'StudentID'  => $student->StudentID,
            'RecordDate' => $today,
            'Period'     => $period,
        ])->first();

        if ($record) {
            // Update only mutable fields
            $record->Status      = $status;
            $record->RecordTime  = $nowTime;
            $record->RecordedBy  = auth()->user()->UserID;
            $record->save();
        } else {
            // Create brand-new record with a fresh UUID
            $record = PrayerRecord::create([
                'PrayerRecordID' => (string) Str::uuid(),
                'StudentID'      => $student->StudentID,
                'RecordDate'     => $today,
                'RecordTime'     => $nowTime,
                'Period'         => $period,
                'Status'         => $status,
                'RecordedBy'     => auth()->user()->UserID,
            ]);
        }

        return response()->json([
            'success'   => true,
            'message'   => 'บันทึกข้อมูลสำเร็จ',
            'student'   => [
                'id'        => $student->StudentID,
                'name'      => $student->FullName,
                'class'     => "{$student->GradeLevel}/{$student->Classroom}",
                'gender'    => $student->Gender,
                'photo'     => $student->Photo ? asset('storage/' . $student->Photo) : null,
            ],
            'record'    => [
                'period'    => $record->Period,
                'status'    => $record->Status,
                'time'      => Carbon::parse($record->RecordTime)->format('H:i น.'),
            ]
        ]);
    }

    // 3. Calendar View
    public function calendar(Request $request)
    {
        $user = auth()->user();
        $role = strtolower($user->Role);
        $studentId = $request->input('student_id');

        // Lock Student/Parent filters or block unauthorized access
        $isLocked = false;
        if (in_array($role, ['นักเรียน', 'student'])) {
            $studentId = $user->student->StudentID ?? null;
            $isLocked = true;
        } elseif (in_array($role, ['ผู้ปกครอง', 'parent'])) {
            $studentId = $user->parent->StudentID ?? null;
            $isLocked = true;
        } elseif (!in_array($role, ['ฝ่ายปกครอง', 'discipline'])) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าใช้งานหน้านี้');
        }

        // Get Month / Year
        $month = $request->input('month', Carbon::today()->month);
        $year = $request->input('year', Carbon::today()->year);

        // Fetch students list for filter dropdown (only for Admins/Teachers/Discipline)
        $filterStudents = collect();
        if (!$isLocked) {
            $filterStudents = Student::orderBy('FullName')->get();
        }

        $student = null;
        $calendarDays = [];
        $monthlyStatus = null;

        if ($studentId) {
            $student = Student::where('StudentID', $studentId)->first();
            if ($student) {
                // Generate calendar grid dates
                $startOfMonth = Carbon::create($year, $month, 1);
                $daysInMonth = $startOfMonth->daysInMonth;
                $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sun) to 6 (Sat)

                // Fetch student records for this month
                $records = PrayerRecord::where('StudentID', $student->StudentID)
                    ->whereYear('RecordDate', $year)
                    ->whereMonth('RecordDate', $month)
                    ->get()
                    ->groupBy(function($item) {
                        return Carbon::parse($item->RecordDate)->day;
                    });

                $calendarDays = [
                    'days_in_month' => $daysInMonth,
                    'first_day_of_week' => $firstDayOfWeek,
                    'records' => $records,
                    'month_name' => $startOfMonth->locale('th')->isoFormat('MMMM YYYY'),
                ];

                $monthlyStatus = $student->getPrayerMonthlyStatus($month, $year);
            }
        }

        return view('prayer.calendar', compact('student', 'filterStudents', 'calendarDays', 'month', 'year', 'isLocked', 'studentId', 'monthlyStatus'));
    }

    // 4. Dashboard / Analytics View
    public function dashboard(Request $request)
    {
        $role = strtolower(auth()->user()->Role);
        if (!in_array($role, ['ฝ่ายปกครอง', 'discipline'])) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงรายงานสรุปผล');
        }

        $month = $request->input('month', Carbon::today()->month);
        $year = $request->input('year', Carbon::today()->year);
        $classroom = $request->input('classroom');
        $grade = $request->input('grade');

        // Fetch active check-in sessions in this month/year (dates/periods where records exist)
        $activeSessionsQuery = PrayerRecord::query()
            ->whereYear('RecordDate', $year)
            ->whereMonth('RecordDate', $month);

        if ($classroom || $grade) {
            $activeSessionsQuery->whereHas('student', function($q) use ($classroom, $grade) {
                if ($classroom) $q->where('Classroom', $classroom);
                if ($grade) $q->where('GradeLevel', $grade);
            });
        }

        $totalActiveSessions = $activeSessionsQuery->select('RecordDate', 'Period')
            ->distinct()
            ->get()
            ->count();

        // Query students matching filter
        $studentQuery = Student::query();
        if ($classroom) $studentQuery->where('Classroom', $classroom);
        if ($grade) $studentQuery->where('GradeLevel', $grade);
        $students = $studentQuery->orderBy('StudentID')->get();

        $passingStatus = $request->input('passing_status');

        // Calculate statistics per student
        $studentStats = [];
        $schoolTotalPrayed = 0;
        $schoolTotalAbsent = 0;

        foreach ($students as $s) {
            $statusData = $s->getPrayerMonthlyStatus($month, $year);
            $prayedCount = $statusData['prayed_count'];
            $exemptCount = $statusData['exempt_count'];
            $absentCount = $statusData['absent_count'];
            $percent = $statusData['percentage'];
            $isPassing = $statusData['is_passing_percentage'];
            $isCorrected = $statusData['is_corrected'];

            $isPass = $isPassing || $isCorrected;

            // Filter by passing status
            if ($passingStatus === 'pass' && !$isPass) continue;
            if ($passingStatus === 'fail' && $isPass) continue;

            $studentStats[] = [
                'student'      => $s,
                'prayed'       => $prayedCount,
                'absent'       => $absentCount,
                'percent'      => $percent,
                'is_passing'   => $isPassing,
                'is_corrected' => $isCorrected,
                'status'       => $statusData['status'],
                'status_text'  => $statusData['status_text']
            ];

            $schoolTotalPrayed += $prayedCount;
            $schoolTotalAbsent += $absentCount;
        }

        // School-Wide Summary
        $schoolTotalStudents = count($studentStats);
        $schoolTotalExpected = $schoolTotalStudents * $totalActiveSessions;
        // Total exempt count
        $schoolTotalExempt = 0;
        foreach ($studentStats as $stat) {
            $exempt = PrayerRecord::where('StudentID', $stat['student']->StudentID)
                ->whereYear('RecordDate', $year)
                ->whereMonth('RecordDate', $month)
                ->where('Status', 'ละหมาดไม่ได้')
                ->count();
            $schoolTotalExempt += $exempt;
        }
        $schoolTotalEligible = max(0, $schoolTotalExpected - $schoolTotalExempt);
        $schoolPercentage = $schoolTotalEligible > 0 ? ($schoolTotalPrayed / $schoolTotalEligible) * 100 : 0;

        // Unique classes for filters
        $classrooms = Student::select('Classroom')->distinct()->orderBy('Classroom')->pluck('Classroom');
        $grades = Student::select('GradeLevel')->distinct()->orderBy('GradeLevel')->pluck('GradeLevel');

        return view('prayer.dashboard', compact(
            'studentStats',
            'schoolTotalStudents',
            'schoolTotalPrayed',
            'schoolTotalAbsent',
            'schoolPercentage',
            'classrooms',
            'grades',
            'month',
            'year',
            'classroom',
            'grade',
            'totalActiveSessions',
            'passingStatus'
        ));
    }

    // 5. Export Report Page
    public function export(Request $request)
    {
        $role = strtolower(auth()->user()->Role);
        if (!in_array($role, ['ฝ่ายปกครอง', 'discipline'])) {
            abort(403, 'ไม่มีสิทธิ์ส่งออกรายงาน');
        }

        $type = $request->input('type', 'daily'); // daily, weekly, monthly, term
        $date = $request->input('date', Carbon::today()->toDateString());
        $month = $request->input('month', Carbon::today()->month);
        $year = $request->input('year', Carbon::today()->year);
        $classroom = $request->input('classroom');
        $grade = $request->input('grade');
        $passingStatus = $request->input('passing_status');

        // Determine date range based on report type
        $startDate = $date;
        $endDate = $date;

        if ($type === 'weekly') {
            $carbonDate = Carbon::parse($date);
            $startDate = $carbonDate->startOfWeek()->toDateString();
            $endDate = $carbonDate->endOfWeek()->toDateString();
        } elseif ($type === 'monthly') {
            $startOfMonth = Carbon::create($year, $month, 1);
            $startDate = $startOfMonth->startOfMonth()->toDateString();
            $endDate = $startOfMonth->endOfMonth()->toDateString();
        } elseif ($type === 'term') {
            // Presume School Terms: Term 1 (May-Sep), Term 2 (Nov-Mar)
            // Or simple custom range
            $term = $request->input('term', 1);
            if ($term == 1) {
                $startDate = "{$year}-05-01";
                $endDate = "{$year}-09-30";
            } else {
                $startDate = "{$year}-11-01";
                $endDate = Carbon::create($year + 1, 3, 31)->toDateString(); // March next year
            }
        }

        // Fetch active check-in sessions in range
        $totalActiveSessions = PrayerRecord::query()
            ->whereBetween('RecordDate', [$startDate, $endDate])
            ->select('RecordDate', 'Period')
            ->distinct()
            ->get()
            ->count();

        // Query students matching filter
        $studentQuery = Student::query();
        if ($classroom) $studentQuery->where('Classroom', $classroom);
        if ($grade) $studentQuery->where('GradeLevel', $grade);
        $students = $studentQuery->orderBy('StudentID')->get();

        // Calculate statistics
        $stats = [];
        foreach ($students as $s) {
            $prayedCount = 0;
            $absentCount = 0;
            $exemptCount = 0;
            $percent = 0.0;
            $isPass = false;
            $isCorrected = false;

            if ($type === 'monthly') {
                $statusData = $s->getPrayerMonthlyStatus($month, $year);
                $prayedCount = $statusData['prayed_count'];
                $exemptCount = $statusData['exempt_count'];
                $absentCount = $statusData['absent_count'];
                $percent = $statusData['percentage'];
                $isPass = $statusData['is_passing_percentage'] || $statusData['is_corrected'];
                $isCorrected = $statusData['is_corrected'];
            } else {
                $records = PrayerRecord::where('StudentID', $s->StudentID)
                    ->whereBetween('RecordDate', [$startDate, $endDate])
                    ->get();

                $prayedCount = $records->where('Status', 'ละหมาด')->count();
                $exemptCount = $records->where('Status', 'ละหมาดไม่ได้')->count();

                $eligibleSessions = max(0, $totalActiveSessions - $exemptCount);
                $absentCount = max(0, $eligibleSessions - $prayedCount);

                $percentage = $eligibleSessions > 0 ? ($prayedCount / $eligibleSessions) * 100 : 100;
                $percent = round($percentage, 1);
                $isPass = $percent >= 80;
            }

            // Apply passing status filter
            if ($passingStatus === 'pass' && !$isPass) continue;
            if ($passingStatus === 'fail' && $isPass) continue;

            $stats[] = [
                'StudentID'    => $s->StudentID,
                'FullName'     => $s->FullName,
                'Class'        => "{$s->GradeLevel}/{$s->Classroom}",
                'Gender'       => $s->Gender,
                'prayed'       => $prayedCount,
                'absent'       => $absentCount,
                'exempt'       => $exemptCount,
                'percent'      => $percent,
                'is_corrected' => $isCorrected,
                'is_pass'      => $isPass
            ];
        }

        // Handle CSV Download
        if ($request->has('excel')) {
            $fileName = "prayer_report_{$type}_{$startDate}_to_{$endDate}.csv";
            $headers = [
                "Content-type"        => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename={$fileName}",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function() use ($stats, $type, $startDate, $endDate) {
                $file = fopen('php://output', 'w');
                // Write UTF-8 BOM so Excel opens it with correct Thai characters
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Title header
                fputcsv($file, ["รายงานการละหมาด ({$type}) ช่วงวันที่: {$startDate} ถึง {$endDate}"]);
                fputcsv($file, []);

                // Column Headers
                fputcsv($file, ["รหัสนักเรียน", "ชื่อ-สกุล", "ระดับชั้น", "เพศ", "จำนวนครั้งที่ละหมาด", "จำนวนครั้งที่ขาด", "จำนวนครั้งที่ละหมาดไม่ได้", "เปอร์เซ็นต์การละหมาด"]);

                // Data Rows
                foreach ($stats as $row) {
                    fputcsv($file, [
                        $row['StudentID'],
                        $row['FullName'],
                        $row['Class'],
                        $row['Gender'],
                        $row['prayed'],
                        $row['absent'],
                        $row['exempt'],
                        $row['percent'] . '%'
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Otherwise return Printable HTML View
        $reportTitle = "รายงานผลการเช็กชื่อการละหมาด";
        $periodText = "ตั้งแต่วันที่ " . Carbon::parse($startDate)->locale('th')->isoFormat('D MMMM YYYY') . 
                      " ถึง " . Carbon::parse($endDate)->locale('th')->isoFormat('D MMMM YYYY');

        return view('prayer.export', compact('stats', 'type', 'startDate', 'endDate', 'reportTitle', 'periodText', 'classroom', 'grade', 'passingStatus'));
    }

    // 6. Toggle Prayer Correction status (POST)
    public function toggleCorrection(Request $request)
    {
        $role = strtolower(auth()->user()->Role);
        if (!in_array($role, ['ฝ่ายปกครอง', 'discipline'])) {
            abort(403, 'ไม่มีสิทธิ์บันทึกข้อมูลการแก้ละหมาด');
        }

        $request->validate([
            'student_id' => 'required|string',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer',
        ]);

        $studentId = $request->input('student_id');
        $month = $request->input('month');
        $year = $request->input('year');

        // Check if student exists
        $student = Student::where('StudentID', $studentId)->first();
        if (!$student) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลนักเรียน');
        }

        // Toggle the record
        $existing = \App\Models\PrayerCorrection::where([
            'StudentID' => $studentId,
            'Month'     => $month,
            'Year'      => $year,
        ])->first();

        if ($existing) {
            $existing->delete();
            $msg = 'ยกเลิกการบันทึกแก้ละหมาดเรียบร้อยแล้ว';
        } else {
            \App\Models\PrayerCorrection::create([
                'StudentID'  => $studentId,
                'Month'      => $month,
                'Year'       => $year,
                'Status'     => 'แก้ละหมาดแล้ว',
                'RecordedBy' => auth()->user()->UserID,
            ]);
            $msg = 'บันทึกการแก้ละหมาดเรียบร้อยแล้ว';
        }

        return redirect()->back()->with('success', $msg);
    }
}
