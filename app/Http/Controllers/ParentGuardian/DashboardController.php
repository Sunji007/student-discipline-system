<?php

namespace App\Http\Controllers\ParentGuardian;

use App\Http\Controllers\Controller;
use App\Models\Message;

class DashboardController extends Controller
{
    public function index()
    {
        $parent  = auth()->user()->parentGuardian;
        $student = $parent?->student;

        if (!$student) {
            return view('parent.dashboard', ['student' => null]);
        }

        $recentRecords = $student->behaviorRecords()
            ->with('rule')
            ->orderBy('RecordDate', 'desc')
            ->take(5)
            ->get();

        $recentAttendance = $student->attendances()
            ->orderBy('Date', 'desc')
            ->take(7)
            ->get();

        $unreadMessages = Message::where('ReceiverID', auth()->user()->UserID)
            ->where('IsRead', false)
            ->count();

        return view('parent.dashboard', compact(
            'student',
            'recentRecords',
            'recentAttendance',
            'unreadMessages'
        ));
    }
}