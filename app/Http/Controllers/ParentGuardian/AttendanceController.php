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

        // All attendance records for this student in the selected semester
        $allSemesterRecords = Attendance::where('StudentID', $student->StudentID)
            ->where('semester_id', $selectedSemesterId)
            ->orderBy('Date')
            ->get();

        // Monthly records for calendar
        $records = $allSemesterRecords->filter(function($r) use ($year, $mon) {
            return $r->Date->format('Y') == $year && $r->Date->format('m') == $mon;
        })->keyBy(fn($r) => $r->Date->format('Y-m-d'));

        // Fallback if records were outside semester_id or semester_id wasn't saved on older records
        if ($records->isEmpty()) {
            $fallbackRecords = Attendance::where('StudentID', $student->StudentID)
                ->whereYear('Date', $year)
                ->whereMonth('Date', $mon)
                ->orderBy('Date')
                ->get();
            if ($fallbackRecords->isNotEmpty()) {
                $records = $fallbackRecords->keyBy(fn($r) => $r->Date->format('Y-m-d'));
            }
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mon, $year);

        // Stats for current viewed month
        $monthPresent = $records->where('Status', 'มา')->count();
        $monthLate    = $records->where('Status', 'สาย')->count();
        $monthAbsent  = $records->where('Status', 'ขาด')->count();
        $monthTotal   = $records->count();
        $monthRate    = $monthTotal > 0 ? round(($monthPresent / $monthTotal) * 100, 1) : 0;

        $monthSummary = [
            'มา'    => $monthPresent,
            'สาย'   => $monthLate,
            'ขาด'   => $monthAbsent,
            'total' => $monthTotal,
            'rate'  => $monthRate,
        ];

        // Overall semester summary
        $semPresent = $allSemesterRecords->where('Status', 'มา')->count();
        $semLate    = $allSemesterRecords->where('Status', 'สาย')->count();
        $semAbsent  = $allSemesterRecords->where('Status', 'ขาด')->count();
        $semTotal   = $allSemesterRecords->count();
        $semRate    = $semTotal > 0 ? round(($semPresent / $semTotal) * 100, 1) : 0;

        $summary = [
            'มา'    => $semPresent,
            'สาย'   => $semLate,
            'ขาด'   => $semAbsent,
            'total' => $semTotal,
            'rate'  => $semRate,
        ];

        // Monthly breakdown across all recorded months in the semester
        $monthlyBreakdown = [];
        $groupedByMonth = $allSemesterRecords->groupBy(fn($item) => $item->Date->format('Y-m'));

        if (!$groupedByMonth->has($month)) {
            $groupedByMonth->put($month, $records);
        }

        // Sort chronologically
        $sortedKeys = $groupedByMonth->keys()->sort();

        foreach ($sortedKeys as $ym) {
            $monthRecs = $groupedByMonth->get($ym);
            [$mYear, $mMon] = explode('-', $ym);
            $p = $monthRecs->where('Status', 'มา')->count();
            $l = $monthRecs->where('Status', 'สาย')->count();
            $a = $monthRecs->where('Status', 'ขาด')->count();
            $t = $monthRecs->count();
            $r = $t > 0 ? round(($p / $t) * 100, 1) : 0;

            $dt = \Carbon\Carbon::create((int)$mYear, (int)$mMon, 1)->locale('th');
            $thaiMonthName = $dt->isoFormat('MMMM ') . ((int)$mYear > 2400 ? $mYear : (int)$mYear + 543);

            $monthlyBreakdown[$ym] = [
                'year_month' => $ym,
                'year'       => (int)$mYear,
                'month'      => (int)$mMon,
                'month_name' => $thaiMonthName,
                'present'    => $p,
                'late'       => $l,
                'absent'     => $a,
                'total'      => $t,
                'rate'       => $r,
                'is_current' => ($ym === $month),
            ];
        }

        return view('parent.attendance.index', compact(
            'student', 'records', 'month', 'daysInMonth', 'year', 'mon', 'summary', 'monthSummary', 'monthlyBreakdown'
        ));
    }
}