<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $teacher  = auth()->user()->teacher;
        $rooms    = $teacher?->advisory_rooms ?? [];
        
        $roomParam = $request->get('room');
        if (count($rooms) > 1) {
            if ($roomParam && in_array($roomParam, $rooms)) {
                $selectedRoom = $roomParam;
            } else {
                $selectedRoom = 'all';
            }
        } else {
            $selectedRoom = $rooms[0] ?? null;
        }

        $targetRoom = ($selectedRoom === 'all') ? $rooms : $selectedRoom;
        $statusFilter = $request->get('status');

        $query = Student::with('parent')
            ->inAdvisoryRoom($targetRoom)
            ->orderBy('FirstName')
            ->orderBy('LastName');

        if ($statusFilter === 'risk' || $statusFilter === 'เสี่ยง') {
            $query->where(function ($q) {
                $q->whereIn('RiskStatus', ['เฝ้าระวัง', 'ตักเตือน', 'วิกฤต', 'ทัณฑ์บน'])
                  ->orWhere(function ($sub) {
                      $sub->where('RiskStatus', '!=', 'ปกติ')
                          ->whereNotNull('RiskStatus')
                          ->where('RiskStatus', '!=', '');
                  });
            });
        } elseif ($statusFilter === 'ตักเตือน') {
            $query->whereIn('RiskStatus', ['ตักเตือน', 'เฝ้าระวัง']);
        } elseif ($statusFilter === 'ทัณฑ์บน') {
            $query->whereIn('RiskStatus', ['ทัณฑ์บน', 'วิกฤต']);
        } elseif ($statusFilter === 'ปกติ') {
            $query->where(function ($q) {
                $q->where('RiskStatus', 'ปกติ')
                  ->orWhereNull('RiskStatus')
                  ->orWhere('RiskStatus', '');
            });
        }

        $students = $query->get();

        $allStudentsInRoom = !empty($targetRoom) ? Student::inAdvisoryRoom($targetRoom)->get() : collect();
        $statusCounts = [
            'all'      => $allStudentsInRoom->count(),
            'risk'     => $allStudentsInRoom->whereIn('RiskStatus', ['เฝ้าระวัง', 'ตักเตือน', 'วิกฤต', 'ทัณฑ์บน'])->count(),
            'ปกติ'     => $allStudentsInRoom->filter(fn($s) => $s->RiskStatus === 'ปกติ' || empty($s->RiskStatus))->count(),
            'ตักเตือน' => $allStudentsInRoom->whereIn('RiskStatus', ['ตักเตือน', 'เฝ้าระวัง'])->count(),
            'ทัณฑ์บน'  => $allStudentsInRoom->whereIn('RiskStatus', ['ทัณฑ์บน', 'วิกฤต'])->count(),
        ];

        $classroom = $selectedRoom;

        return view('teacher.classroom.index', compact('students', 'classroom', 'rooms', 'statusFilter', 'statusCounts'));
    }
}