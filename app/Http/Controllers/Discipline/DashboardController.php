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
        $stats = [
            'pending'     => BehaviorRecord::where('Status', 'รออนุมัติ')->count(),
            'appeals'     => Appeal::where('Status', 'รอตรวจสอบ')->count(),
            'risk'        => Student::whereIn('RiskStatus', ['เฝ้าระวัง', 'วิกฤต'])->count(),
            'today_absent'=> Attendance::where('Date', today())->where('Status', 'ขาด')->count(),
        ];

        $recentRecords = BehaviorRecord::with(['student', 'rule', 'recorder'])
            ->latest('RecordDate')
            ->take(10)
            ->get();

        $riskStudents = Student::whereIn('RiskStatus', ['เฝ้าระวัง', 'วิกฤต'])
            ->orderBy('BehaviorScore')
            ->take(5)
            ->get();

        return view('discipline.dashboard', compact('stats', 'recentRecords', 'riskStudents'));
    }

    public function riskStudents()
    {
        $students = Student::whereIn('RiskStatus', ['เฝ้าระวัง', 'วิกฤต'])
            ->orderBy('BehaviorScore')
            ->paginate(20);

        return view('discipline.risk-students', compact('students'));
    }
}