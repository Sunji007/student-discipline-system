<?php

namespace App\Http\Controllers\ParentGuardian;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $students = auth()->user()->parentStudents;
        abort_if($students->isEmpty(), 404, 'ไม่พบข้อมูลบุตรหลาน');

        $selectedStudentId = session('selected_student_id', $students->first()->StudentID);
        $student = $students->firstWhere('StudentID', $selectedStudentId) ?? $students->first();

        $month = $request->get('month', now()->format('Y-m'));

        [$year, $mon] = explode('-', $month);

        $selectedSemesterId = $this->getSelectedSemesterId();

        $records = Attendance::where('StudentID', $student->StudentID)
            ->where('semester_id', $selectedSemesterId)
            ->whereYear('Date', $year)
            ->whereMonth('Date', $mon)
            ->orderBy('Date')
            ->get()
            ->keyBy(fn($r) => $r->Date->format('Y-m-d'));

        $summary = [
            'มา'   => Attendance::where('StudentID', $student->StudentID)->where('semester_id', $selectedSemesterId)->where('Status', 'มา')->count(),
            'สาย'  => Attendance::where('StudentID', $student->StudentID)->where('semester_id', $selectedSemesterId)->where('Status', 'สาย')->count(),
            'ขาด'  => Attendance::where('StudentID', $student->StudentID)->where('semester_id', $selectedSemesterId)->where('Status', 'ขาด')->count(),
        ];

        // สร้าง calendar grid
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mon, $year);

        return view('parent.attendance.index', compact(
            'student', 'records', 'month', 'daysInMonth', 'year', 'mon', 'summary'
        ));
    }
}