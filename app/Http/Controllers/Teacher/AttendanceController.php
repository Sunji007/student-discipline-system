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
        $rooms   = $teacher?->advisory_rooms ?? [];
        
        $selectedRoom = $request->get('room');
        if (!$selectedRoom || !in_array($selectedRoom, $rooms)) {
            $selectedRoom = $rooms[0] ?? null;
        }

        $date = $request->get('date', today()->format('Y-m-d'));

        $students = Student::inAdvisoryRoom($selectedRoom)
            ->orderBy('FirstName')
            ->orderBy('LastName')
            ->get();

        // โหลดสถานะวันที่เลือก
        $attendanceMap = Attendance::where('Date', $date)
            ->whereIn('StudentID', $students->pluck('StudentID'))
            ->pluck('Status', 'StudentID');

        $classroom = $selectedRoom;

        return view('teacher.attendance.index', compact('students', 'attendanceMap', 'date', 'classroom', 'rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'attendance'  => 'required|array',
            'attendance.*'=> 'required|in:มา,สาย,ขาด',
        ]);

        $semesterId = $this->getSelectedSemesterId();
        $recordedBy = auth()->user()->UserID;

        foreach ($request->attendance as $studentId => $status) {
            Attendance::updateOrCreate(
                ['StudentID' => $studentId, 'Date' => $request->date],
                [
                    'AttendanceID' => (string) Str::uuid(),
                    'RecordedBy'   => $recordedBy,
                    'Status'       => $status,
                    'semester_id'  => $semesterId,
                ]
            );
        }

        // Auto-deduct behavior score for students reaching absence/tardiness threshold
        $deductionService = app(\App\Services\AttendanceDeductionService::class);
        $deductedStudentsCount = 0;
        $totalDeductionPoints = 0;

        foreach ($request->attendance as $studentId => $status) {
            $change = $deductionService->syncStudentDeductions(
                $studentId,
                $semesterId,
                $recordedBy,
                $request->date
            );
            if ($change > 0) {
                $deductedStudentsCount++;
                $totalDeductionPoints += ($change * 5);
            }
        }

        $msg = 'บันทึกการเข้าแถวเรียบร้อยแล้ว';
        if ($deductedStudentsCount > 0) {
            $msg .= " (ระบบตัดคะแนนความประพฤติอัตโนมัติแก่นักเรียนที่ขาด/สายสะสมครบเกณฑ์จำนวน {$deductedStudentsCount} คน รวม -{$totalDeductionPoints} คะแนน)";
        }

        return back()->with('success', $msg);
    }
}