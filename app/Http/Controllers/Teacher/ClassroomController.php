<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;

class ClassroomController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $teacher  = auth()->user()->teacher;
        $rooms    = $teacher?->advisory_rooms ?? [];
        
        $selectedRoom = $request->get('room');
        if (!$selectedRoom || !in_array($selectedRoom, $rooms)) {
            $selectedRoom = $rooms[0] ?? null;
        }

        $students = Student::with('parent')
            ->inAdvisoryRoom($selectedRoom)
            ->orderBy('FirstName')
            ->orderBy('LastName')
            ->get();

        $classroom = $selectedRoom;

        return view('teacher.classroom.index', compact('students', 'classroom', 'rooms'));
    }
}