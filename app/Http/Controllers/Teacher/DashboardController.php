<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\BehaviorRecord;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher  = auth()->user()->teacher;
        $room     = $teacher?->AdvisoryRoom;

        $students = Student::inAdvisoryRoom($room)->get();
        $ids      = $students->pluck('StudentID');

        $stats = [
            'total'        => $students->count(),
            'risk'         => $students->whereIn('RiskStatus', ['เฝ้าระวัง', 'วิกฤต'])->count(),
            'today_absent' => Attendance::where('Date', today())
                                ->whereIn('StudentID', $ids)
                                ->where('Status', 'ขาด')->count(),
            'pending'      => BehaviorRecord::whereIn('StudentID', $ids)
                                ->where('Status', 'รออนุมัติ')->count(),
        ];

        $recentAttendance = Attendance::with('student')
            ->whereIn('StudentID', $ids)
            ->where('Date', today())
            ->get();

        return view('teacher.dashboard', compact('stats', 'recentAttendance', 'room', 'students'));
    }
}