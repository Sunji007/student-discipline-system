<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $date = $request->get('date', today()->format('Y-m-d'));

        $students = Student::inAdvisoryRoom($teacher->AdvisoryRoom ?? '')
            ->orderBy('FullName')->get();

        // โหลดสถานะวันที่เลือก
        $attendanceMap = Attendance::where('Date', $date)
            ->whereIn('StudentID', $students->pluck('StudentID'))
            ->pluck('Status', 'StudentID');

        return view('teacher.attendance.index', compact('students', 'attendanceMap', 'date'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'attendance'  => 'required|array',
            'attendance.*'=> 'required|in:มา,สาย,ขาด',
        ]);

        foreach ($request->attendance as $studentId => $status) {
            Attendance::updateOrCreate(
                ['StudentID' => $studentId, 'Date' => $request->date],
                [
                    'AttendanceID' => Str::uuid(),
                    'RecordedBy'   => auth()->user()->UserID,
                    'Status'       => $status,
                ]
            );
        }

        return back()->with('success', 'บันทึกการเข้าแถวเรียบร้อยแล้ว');
    }
}