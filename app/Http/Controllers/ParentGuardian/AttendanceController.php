<?php

namespace App\Http\Controllers\ParentGuardian;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->parentGuardian?->student;
        abort_if(!$student, 404, 'ไม่พบข้อมูลบุตรหลาน');

        $month = $request->get('month', now()->format('Y-m'));

        [$year, $mon] = explode('-', $month);

        $records = Attendance::where('StudentID', $student->StudentID)
            ->whereYear('Date', $year)
            ->whereMonth('Date', $mon)
            ->orderBy('Date')
            ->get()
            ->keyBy(fn($r) => $r->Date->format('Y-m-d'));

        $summary = [
            'มา'   => Attendance::where('StudentID', $student->StudentID)->where('Status', 'มา')->count(),
            'สาย'  => Attendance::where('StudentID', $student->StudentID)->where('Status', 'สาย')->count(),
            'ขาด'  => Attendance::where('StudentID', $student->StudentID)->where('Status', 'ขาด')->count(),
        ];

        // สร้าง calendar grid
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mon, $year);

        return view('parent.attendance.index', compact(
            'student', 'records', 'month', 'daysInMonth', 'year', 'mon', 'summary'
        ));
    }
}