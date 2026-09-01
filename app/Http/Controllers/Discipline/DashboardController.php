<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\BehaviorRecord;
use App\Models\Appeal;
use App\Models\Attendance;

class DashboardController extends Controller
{
    public function index()
    {
        $selectedSemesterId = $this->getSelectedSemesterId();

        $netModifiers = \Illuminate\Support\Facades\DB::table('behavior_records')
            ->join('behavior_rules', 'behavior_records.RuleID', '=', 'behavior_rules.RuleID')
            ->where('behavior_records.semester_id', $selectedSemesterId)
            ->whereIn('behavior_records.Status', ['อนุมัติ', 'อนุมัติแล้ว', 'อยู่ในระหว่างยื่นอุทธรณ์'])
            ->select('StudentID')
            ->selectRaw("SUM(CASE WHEN behavior_rules.RuleType = 'ตัดคะแนน' THEN -ABS(behavior_rules.ScoreModifier) ELSE ABS(behavior_rules.ScoreModifier) END) as net_modifier")
            ->groupBy('StudentID')
            ->pluck('net_modifier', 'StudentID');

        $allStudents = Student::all();
        $riskStudentList = collect();

        foreach ($allStudents as $st) {
            $score = isset($netModifiers[$st->StudentID]) 
                ? max(0, min(100, 100 + $netModifiers[$st->StudentID])) 
                : ($st->BehaviorScore ?? 100);
            $st->BehaviorScore = $score;
            $st->RiskStatus = match(true) {
                $score >= 80 => 'ปกติ',
                $score >= 60 => 'ตักเตือน',
                default      => 'ทัณฑ์บน',
            };
            if ($score < 80) {
                $riskStudentList->push($st);
            }
        }

        $riskStudents = $riskStudentList->sortBy('BehaviorScore')->take(5);

        $stats = [
            'pending'       => BehaviorRecord::where('Status', 'รออนุมัติ')->where('semester_id', $selectedSemesterId)->count(),
            'appeals'       => Appeal::where('Status', 'รอตรวจสอบ')->whereHas('behaviorRecord', fn($q) => $q->where('semester_id', $selectedSemesterId))->count(),
            'risk'          => $riskStudentList->count(),
            'today_reports' => \App\Models\InformantReport::where('Status', 'เรื่องใหม่')->count(),
        ];

        // Category & Type statistics for Charts
        $categoryStats = \Illuminate\Support\Facades\DB::table('behavior_records')
            ->join('behavior_rules', 'behavior_records.RuleID', '=', 'behavior_rules.RuleID')
            ->where('behavior_records.semester_id', $selectedSemesterId)
            ->select('behavior_rules.Category', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
            ->groupBy('behavior_rules.Category')
            ->pluck('count', 'Category');

        $ruleTypeStats = \Illuminate\Support\Facades\DB::table('behavior_records')
            ->join('behavior_rules', 'behavior_records.RuleID', '=', 'behavior_rules.RuleID')
            ->where('behavior_records.semester_id', $selectedSemesterId)
            ->select('behavior_rules.RuleType', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
            ->groupBy('behavior_rules.RuleType')
            ->pluck('count', 'RuleType');

        $recentRecords = BehaviorRecord::with(['student', 'rule', 'recorder'])
            ->where('semester_id', $selectedSemesterId)
            ->latest('RecordDate')
            ->take(10)
            ->get();

        return view('discipline.dashboard', compact('stats', 'recentRecords', 'riskStudents', 'categoryStats', 'ruleTypeStats'));
    }

    public function riskStudents()
    {
        $selectedSemesterId = $this->getSelectedSemesterId();

        $netModifiers = \Illuminate\Support\Facades\DB::table('behavior_records')
            ->join('behavior_rules', 'behavior_records.RuleID', '=', 'behavior_rules.RuleID')
            ->where('behavior_records.semester_id', $selectedSemesterId)
            ->whereIn('behavior_records.Status', ['อนุมัติ', 'อนุมัติแล้ว', 'อยู่ในระหว่างยื่นอุทธรณ์'])
            ->select('StudentID')
            ->selectRaw("SUM(CASE WHEN behavior_rules.RuleType = 'ตัดคะแนน' THEN -ABS(behavior_rules.ScoreModifier) ELSE ABS(behavior_rules.ScoreModifier) END) as net_modifier")
            ->groupBy('StudentID')
            ->pluck('net_modifier', 'StudentID');

        $allStudents = Student::all();
        $riskStudentList = collect();

        foreach ($allStudents as $st) {
            $score = isset($netModifiers[$st->StudentID]) 
                ? max(0, min(100, 100 + $netModifiers[$st->StudentID])) 
                : ($st->BehaviorScore ?? 100);
            $st->BehaviorScore = $score;
            $st->RiskStatus = match(true) {
                $score >= 80 => 'ปกติ',
                $score >= 60 => 'ตักเตือน',
                default      => 'ทัณฑ์บน',
            };
            if ($score < 80) {
                $riskStudentList->push($st);
            }
        }

        $sorted = $riskStudentList->sortBy('BehaviorScore');

        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $perPage = 20;
        $pagedData = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        $students = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $sorted->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('discipline.risk-students', compact('students'));
    }
}