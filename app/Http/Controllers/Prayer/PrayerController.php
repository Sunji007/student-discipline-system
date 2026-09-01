<?php

namespace App\Http\Controllers\Prayer;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\PrayerRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PrayerController extends Controller
{
    // 1. Scan Page
    public function scan()
    {
        // Require role to be Discipline staff
        $role = strtolower(auth()->user()->Role);
        if (!in_array($role, ['ฝ่ายปกครอง', 'discipline'])) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าใช้งานหน้านี้');
        }

        // Auto determine period based on current time (Noon if < 14:00, otherwise Afternoon)
        $currentHour = now()->hour;
        $defaultPeriod = $currentHour < 14 ? 'เที่ยง' : 'บ่าย';

        return view('prayer.scan', compact('defaultPeriod'));
    }

    // 2. Store Scan Record (AJAX POST)
    public function store(Request $request)
    {
        $user = auth()->user();
        $role = strtolower($user->Role);

        $request->validate([
            'student_id' => 'required|string',
            'period'     => 'nullable|in:เช้า,เที่ยง,เย็น,บ่าย,ซุฮรี,อัศรี',
            'status'     => 'nullable|in:ละหมาด,ละหมาดไม่ได้',
        ]);

        $scannedInput = $request->input('student_id');
        $studentId = $scannedInput;
        $currentHour = now()->hour;
        $period = $request->input('period') ?: ($currentHour < 14 ? 'ซุฮรี' : 'อัศรี');
        $status = $request->input('status') ?: 'ละหมาด';

        // 1. Try parsing JSON if QR Payload is passed (Format: {"id":"10001","period":"ซุฮรี","status":"ละหมาด"})
        $decoded = json_decode($scannedInput, true);
        if (is_array($decoded) && isset($decoded['id'])) {
            $studentId = $decoded['id'];
            if (isset($decoded['period']) && in_array($decoded['period'], ['เที่ยง', 'บ่าย', 'ซุฮรี', 'อัศรี'])) {
                $period = in_array($decoded['period'], ['เที่ยง', 'ซุฮรี']) ? 'ซุฮรี' : 'อัศรี';
            }
            if (isset($decoded['status']) && in_array($decoded['status'], ['ละหมาด', 'ละหมาดไม่ได้'])) {
                $status = $decoded['status'];
            }
        } else {
            // 2. Try parsing delimited string from 1D Barcode (Format: {StudentID}-{noon/asr}-{pray/exempt})
            $parts = explode('-', $scannedInput);
            if (count($parts) === 3) {
                $studentId = $parts[0];
                
                if ($parts[1] === 'noon' || $parts[1] === 'zuhur') {
                    $period = 'ซุฮรี';
                } elseif ($parts[1] === 'asr') {
                    $period = 'อัศรี';
                }
                
                if ($parts[2] === 'pray') {
                    $status = 'ละหมาด';
                } elseif ($parts[2] === 'exempt') {
                    $status = 'ละหมาดไม่ได้';
                }
            }
        }

        // Authorize
        if (in_array($role, ['นักเรียน', 'student'])) {
            $myStudent = $user->student;
            if (!$myStudent || $myStudent->StudentID !== $studentId) {
                return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์บันทึกข้อมูลของนักเรียนคนอื่น'], 403);
            }
        } elseif (!in_array($role, ['ฝ่ายปกครอง', 'discipline'])) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์บันทึกข้อมูล'], 403);
        }

        // Find Student
        $student = Student::where('StudentID', $studentId)->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => "ไม่พบข้อมูลนักเรียน (รหัส: {$studentId})"
            ], 404);
        }

        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->toTimeString();

        // Find existing record or prepare a new one (do NOT overwrite PrayerRecordID on update)
        $record = PrayerRecord::where([
            'StudentID'  => $student->StudentID,
            'RecordDate' => $today,
            'Period'     => $period,
        ])->first();

        if ($record) {
            // Update only mutable fields
            $record->Status      = $status;
            $record->RecordTime  = $nowTime;
            $record->RecordedBy  = auth()->user()->UserID;
            $record->semester_id = $this->getSelectedSemesterId();
            $record->save();
        } else {
            // Create brand-new record with a fresh UUID
            $record = PrayerRecord::create([
                'PrayerRecordID' => (string) Str::uuid(),
                'StudentID'      => $student->StudentID,
                'RecordDate'     => $today,
                'RecordTime'     => $nowTime,
                'Period'         => $period,
                'Status'         => $status,
                'RecordedBy'     => auth()->user()->UserID,
                'semester_id'    => $this->getSelectedSemesterId(),
            ]);
        }

        return response()->json([
            'success'   => true,
            'message'   => 'บันทึกข้อมูลสำเร็จ',
            'student'   => [
                'id'        => $student->StudentID,
                'name'      => $student->FullName,
                'class'     => "{$student->GradeLevel}/{$student->Classroom}",
                'gender'    => $student->Gender,
                'photo'     => $student->Photo ? asset('storage/' . $student->Photo) : null,
            ],
            'record'    => [
                'period'    => ($record->Period === 'เที่ยง' || $record->Period === 'ซุฮรี') ? 'ละหมาดซุฮรี' : (($record->Period === 'บ่าย' || $record->Period === 'อัศรี') ? 'ละหมาดอัศรี' : $record->Period),
                'status'    => $record->Status,
                'time'      => Carbon::parse($record->RecordTime)->format('H:i น.'),
            ]
        ]);
    }

    // 3. Calendar View
    public function calendar(Request $request)
    {
        $user = auth()->user();
        $role = strtolower($user->Role);
        $studentId = $request->input('student_id');

        // Lock Student/Parent filters or block unauthorized access
        $isLocked = false;
        if (in_array($role, ['นักเรียน', 'student'])) {
            $studentId = $user->student->StudentID ?? null;
            $isLocked = true;
        } elseif (in_array($role, ['ผู้ปกครอง', 'parent'])) {
            $studentId = $user->parent->StudentID ?? null;
            $isLocked = true;
        } elseif (!in_array($role, ['ฝ่ายปกครอง', 'discipline', 'ผู้ดูแลระบบ', 'admin'])) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าใช้งานหน้านี้');
        }

        // If discipline/admin visits /prayer/calendar without a specific student_id, redirect to prayer dashboard
        if (!$isLocked && !$studentId) {
            return redirect()->route('prayer.dashboard');
        }

        // Get Month / Year
        $month = $request->input('month', Carbon::today()->month);
        $year = $request->input('year', Carbon::today()->year);

        // Fetch students list for filter dropdown (only for Admins/Teachers/Discipline)
        $filterStudents = collect();
        if (!$isLocked) {
            $filterStudents = Student::orderBy('FirstName')->orderBy('LastName')->get();
        }

        $student = null;
        $calendarDays = [];
        $monthlyStatus = null;

        if ($studentId) {
            $student = Student::where('StudentID', $studentId)->first();
            if ($student) {
                // Generate calendar grid dates
                $startOfMonth = Carbon::create($year, $month, 1);
                $daysInMonth = $startOfMonth->daysInMonth;
                $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sun) to 6 (Sat)

                // Fetch student records for this month
                $records = PrayerRecord::where('StudentID', $student->StudentID)
                    ->whereYear('RecordDate', $year)
                    ->whereMonth('RecordDate', $month)
                    ->get()
                    ->groupBy(function($item) {
                        return Carbon::parse($item->RecordDate)->day;
                    });

                $calendarDays = [
                    'days_in_month' => $daysInMonth,
                    'first_day_of_week' => $firstDayOfWeek,
                    'records' => $records,
                    'month_name' => $startOfMonth->locale('th')->isoFormat('MMMM ') . ($year + 543),
                ];

                $monthlyStatus = $student->getPrayerMonthlyStatus($month, $year);
            }
        }

        $selectedGrade = $request->input('grade');
        $selectedClassroom = $request->input('classroom');

        // Overview data by Classroom & Grade
        $overviewStats = null;
        $classroomSummaries = [];
        $studentOverviewList = [];
        $classrooms = collect();
        $grades = collect();

        if (!$student && !$isLocked) {
            $classrooms = Student::select('Classroom')->distinct()->whereNotNull('Classroom')->orderBy('Classroom')->pluck('Classroom');
            $grades = Student::select('GradeLevel')->distinct()->whereNotNull('GradeLevel')->orderBy('GradeLevel')->pluck('GradeLevel');

            // 1. Query all students in selected grade (to build full classroom summaries grid)
            $classSummaryQuery = Student::query();
            if ($selectedGrade && $selectedGrade !== 'all') {
                $classSummaryQuery->where('GradeLevel', $selectedGrade);
            }
            $gradeStudents = $classSummaryQuery->orderBy('GradeLevel')->orderBy('Classroom')->orderBy('StudentID')->get();

            $groupedByClass = [];
            foreach ($gradeStudents as $st) {
                $stStatus = $st->getPrayerMonthlyStatus($month, $year);
                $isPass = ($stStatus['status'] === 'pass' || $stStatus['status'] === 'corrected');

                $cName = $st->classroom_display ?: 'ไม่ระบุห้อง';
                if (!isset($groupedByClass[$cName])) {
                    $groupedByClass[$cName] = [
                        'classroom' => $cName,
                        'grade' => $st->GradeLevel,
                        'raw_classroom' => $st->Classroom,
                        'total' => 0,
                        'pass' => 0,
                        'fail' => 0,
                        'percent_sum' => 0,
                    ];
                }

                $groupedByClass[$cName]['total']++;
                if ($isPass) $groupedByClass[$cName]['pass']++;
                else $groupedByClass[$cName]['fail']++;
                $groupedByClass[$cName]['percent_sum'] += $stStatus['percentage'];
            }

            foreach ($groupedByClass as $cName => $cData) {
                $classroomSummaries[] = [
                    'classroom' => $cName,
                    'grade' => $cData['grade'],
                    'raw_classroom' => $cData['raw_classroom'],
                    'total' => $cData['total'],
                    'pass' => $cData['pass'],
                    'fail' => $cData['fail'],
                    'avg_percent' => $cData['total'] > 0 ? round($cData['percent_sum'] / $cData['total'], 1) : 0,
                ];
            }

            // 2. Query students for student table (filtered by classroom if selected)
            $tableQuery = Student::query();
            if ($selectedGrade && $selectedGrade !== 'all') {
                $tableQuery->where('GradeLevel', $selectedGrade);
            }
            if ($selectedClassroom && $selectedClassroom !== 'all') {
                $tableQuery->where(function($q) use ($selectedClassroom) {
                    $q->where('Classroom', $selectedClassroom)
                      ->orWhere('Classroom', str_replace('ม.', '', $selectedClassroom));
                });
            }
            $tableStudents = $tableQuery->orderBy('GradeLevel')->orderBy('Classroom')->orderBy('StudentID')->get();

            $totalStudents = count($tableStudents);
            $passCount = 0;
            $failCount = 0;
            $totalPercentSum = 0;

            foreach ($tableStudents as $st) {
                $stStatus = $st->getPrayerMonthlyStatus($month, $year);
                $isPass = ($stStatus['status'] === 'pass' || $stStatus['status'] === 'corrected');
                if ($isPass) {
                    $passCount++;
                } else {
                    $failCount++;
                }
                $totalPercentSum += $stStatus['percentage'];

                $studentOverviewList[] = [
                    'student' => $st,
                    'status_data' => $stStatus,
                    'is_pass' => $isPass
                ];
            }

            $overviewStats = [
                'total_students' => $totalStudents,
                'pass_count' => $passCount,
                'fail_count' => $failCount,
                'avg_percent' => $totalStudents > 0 ? round($totalPercentSum / $totalStudents, 1) : 0,
                'month_name' => Carbon::create($year, $month, 1)->locale('th')->isoFormat('MMMM YYYY'),
            ];
        }

        return view('prayer.calendar', compact(
            'student',
            'filterStudents',
            'calendarDays',
            'month',
            'year',
            'isLocked',
            'studentId',
            'monthlyStatus',
            'selectedGrade',
            'selectedClassroom',
            'overviewStats',
            'classroomSummaries',
            'studentOverviewList',
            'classrooms',
            'grades'
        ));
    }

    // 4. Dashboard / Analytics View
    public function dashboard(Request $request)
    {
        $role = strtolower(auth()->user()->Role);
        if (!in_array($role, ['ฝ่ายปกครอง', 'discipline', 'ผู้ดูแลระบบ', 'admin'])) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงรายงานสรุปผล');
        }

        $selectedSemesterId = $this->getSelectedSemesterId();
        $semester = \App\Models\Semester::find($selectedSemesterId);
        
        $search = trim($request->input('search', ''));
        $month = (int) $request->input('month', Carbon::today()->month);
        $year = (int) $request->input('year', Carbon::today()->year);
        $classroom = $request->input('classroom');
        $grade = $request->input('grade');
        $gender = $request->input('gender');
        $passingStatus = $request->input('passing_status');

        // 1. Today's Quick Live Status (สถิติละหมาดวันนี้)
        $todayDate = Carbon::today()->toDateString();
        $todayThai = Carbon::today()->locale('th')->isoFormat('D MMMM ') . (Carbon::today()->year + 543);
        $todayRecords = PrayerRecord::whereDate('RecordDate', $todayDate)->get();
        $totalMuslimStudents = Student::count();
        $todayZuhurCount = $todayRecords->where('Period', 'ซุฮรี')->whereIn('Status', ['มา', 'มาละหมาด', 'ละหมาดแล้ว'])->count();
        $todayAsrCount = $todayRecords->where('Period', 'อัศรี')->whereIn('Status', ['มา', 'มาละหมาด', 'ละหมาดแล้ว'])->count();
        $todayExemptCount = $todayRecords->where('Status', 'ละหมาดไม่ได้')->unique('StudentID')->count();
        $todayCheckedStudents = $todayRecords->unique('StudentID')->count();

        // 2. Fetch active check-in sessions in this month/year (dates/periods where records exist)
        $activeSessionsQuery = PrayerRecord::query()
            ->whereYear('RecordDate', $year)
            ->whereMonth('RecordDate', $month);

        if ($classroom || $grade || $gender || $search !== '') {
            $activeSessionsQuery->whereHas('student', function($q) use ($classroom, $grade, $gender, $search) {
                if ($classroom) $q->where('Classroom', $classroom);
                if ($grade) $q->where('GradeLevel', $grade);
                if ($gender) $q->where('Gender', $gender);
                if ($search !== '') {
                    $q->where(function($sq) use ($search) {
                        $sq->where('StudentID', 'like', "%{$search}%")
                           ->orWhere('FirstName', 'like', "%{$search}%")
                           ->orWhere('LastName', 'like', "%{$search}%")
                           ->orWhere(\Illuminate\Support\Facades\DB::raw("CONCAT(FirstName, ' ', LastName)"), 'like', "%{$search}%");
                    });
                }
            });
        }

        $totalActiveSessions = $activeSessionsQuery->select('RecordDate', 'Period')
            ->distinct()
            ->get()
            ->count();

        // 3. Query students matching filter
        $studentQuery = Student::query();
        if ($classroom) $studentQuery->where('Classroom', $classroom);
        if ($grade) $studentQuery->where('GradeLevel', $grade);
        if ($gender) $studentQuery->where('Gender', $gender);
        if ($search !== '') {
            $studentQuery->where(function($q) use ($search) {
                $q->where('StudentID', 'like', "%{$search}%")
                  ->orWhere('FirstName', 'like', "%{$search}%")
                  ->orWhere('LastName', 'like', "%{$search}%")
                  ->orWhere(\Illuminate\Support\Facades\DB::raw("CONCAT(FirstName, ' ', LastName)"), 'like', "%{$search}%");
            });
        }
        $students = $studentQuery->orderBy('StudentID')->get();

        // 4. Calculate statistics per student & classroom breakdown
        $studentStats = [];
        $schoolTotalPrayed = 0;
        $schoolTotalAbsent = 0;
        $schoolTotalExempt = 0;
        $schoolPassCount = 0;
        $schoolFailCount = 0;
        $schoolCorrectedCount = 0;
        $exemptStudentsSet = [];
        $classroomStatsMap = [];

        foreach ($students as $s) {
            $statusData = $s->getPrayerMonthlyStatus($month, $year);
            $prayedCount = $statusData['prayed_count'];
            $exemptCount = $statusData['exempt_count'];
            $absentCount = $statusData['absent_count'];
            $percent = $statusData['percentage'];
            $isPassing = $statusData['is_passing_percentage'];
            $isCorrected = $statusData['is_corrected'];

            $isPass = $isPassing || $isCorrected;

            if ($exemptCount > 0) {
                $exemptStudentsSet[$s->StudentID] = true;
            }
            if ($isCorrected) {
                $schoolCorrectedCount++;
            }

            // Filter by passing status
            if ($passingStatus === 'pass' && !$isPass) continue;
            if ($passingStatus === 'fail' && $isPass) continue;

            if ($isPass) {
                $schoolPassCount++;
            } else {
                $schoolFailCount++;
            }

            $studentStats[] = [
                'student'      => $s,
                'prayed'       => $prayedCount,
                'absent'       => $absentCount,
                'exempt'       => $exemptCount,
                'percent'      => $percent,
                'is_passing'   => $isPassing,
                'is_corrected' => $isCorrected,
                'status'       => $statusData['status'],
                'status_text'  => $statusData['status_text']
            ];

            $schoolTotalPrayed += $prayedCount;
            $schoolTotalAbsent += $absentCount;
            $schoolTotalExempt += $exemptCount;

            // Group by classroom for ranking
            $cName = $s->classroom_display ?: 'ไม่ระบุห้อง';
            if (!isset($classroomStatsMap[$cName])) {
                $classroomStatsMap[$cName] = [
                    'name' => $cName,
                    'total' => 0,
                    'pass' => 0,
                    'fail' => 0,
                    'percent_sum' => 0,
                ];
            }
            $classroomStatsMap[$cName]['total']++;
            if ($isPass) {
                $classroomStatsMap[$cName]['pass']++;
            } else {
                $classroomStatsMap[$cName]['fail']++;
            }
            $classroomStatsMap[$cName]['percent_sum'] += $percent;
        }

        // Build Classroom Ranking Array
        $classroomRanking = [];
        foreach ($classroomStatsMap as $cName => $cData) {
            $avgPct = $cData['total'] > 0 ? round($cData['percent_sum'] / $cData['total'], 1) : 0;
            $passRate = $cData['total'] > 0 ? round(($cData['pass'] / $cData['total']) * 100, 1) : 0;
            $classroomRanking[] = [
                'name' => $cName,
                'total' => $cData['total'],
                'pass' => $cData['pass'],
                'fail' => $cData['fail'],
                'avg_percentage' => $avgPct,
                'pass_rate' => $passRate,
            ];
        }
        usort($classroomRanking, fn($a, $b) => $b['avg_percentage'] <=> $a['avg_percentage']);

        // 5. Period Analytics (ซุฮรี vs อัศรี)
        $monthlyRecordsQuery = PrayerRecord::whereYear('RecordDate', $year)
            ->whereMonth('RecordDate', $month);

        if ($classroom || $grade || $gender) {
            $monthlyRecordsQuery->whereHas('student', function($q) use ($classroom, $grade, $gender) {
                if ($classroom) $q->where('Classroom', $classroom);
                if ($grade) $q->where('GradeLevel', $grade);
                if ($gender) $q->where('Gender', $gender);
            });
        }
        $monthlyRecords = $monthlyRecordsQuery->get();

        $zuhurRecords = $monthlyRecords->filter(fn($r) => in_array($r->Period, ['ซุฮรี', 'เที่ยง', 'zuhur']));
        $asrRecords = $monthlyRecords->filter(fn($r) => in_array($r->Period, ['อัศรี', 'บ่าย', 'asr']));

        $zuhurPrayed = $zuhurRecords->whereIn('Status', ['มา', 'มาละหมาด', 'ละหมาดแล้ว'])->count();
        $zuhurExempt = $zuhurRecords->where('Status', 'ละหมาดไม่ได้')->count();
        $zuhurTotal = $zuhurRecords->count();
        $zuhurPercent = ($zuhurTotal - $zuhurExempt) > 0 ? round(($zuhurPrayed / ($zuhurTotal - $zuhurExempt)) * 100, 1) : ($zuhurTotal > 0 ? 100 : 0);

        $asrPrayed = $asrRecords->whereIn('Status', ['มา', 'มาละหมาด', 'ละหมาดแล้ว'])->count();
        $asrExempt = $asrRecords->where('Status', 'ละหมาดไม่ได้')->count();
        $asrTotal = $asrRecords->count();
        $asrPercent = ($asrTotal - $asrExempt) > 0 ? round(($asrPrayed / ($asrTotal - $asrExempt)) * 100, 1) : ($asrTotal > 0 ? 100 : 0);

        $periodAnalytics = [
            'zuhur' => [
                'total' => $zuhurTotal,
                'prayed' => $zuhurPrayed,
                'exempt' => $zuhurExempt,
                'absent' => max(0, $zuhurTotal - $zuhurPrayed - $zuhurExempt),
                'percent' => $zuhurPercent,
            ],
            'asr' => [
                'total' => $asrTotal,
                'prayed' => $asrPrayed,
                'exempt' => $asrExempt,
                'absent' => max(0, $asrTotal - $asrPrayed - $asrExempt),
                'percent' => $asrPercent,
            ]
        ];

        // 6. At-Risk / Urgent Attention Students (percent < 50% or failed with high absent)
        $urgentStudents = collect($studentStats)
            ->filter(fn($item) => $item['percent'] < 50 || $item['absent'] >= 4)
            ->sortBy('percent')
            ->take(6)
            ->values()
            ->all();

        // School-Wide Summary Calculations
        $schoolTotalStudents = count($studentStats);
        $schoolTotalExpected = $schoolTotalStudents * $totalActiveSessions;
        $schoolTotalEligible = max(0, $schoolTotalExpected - $schoolTotalExempt);
        $schoolPercentage = $schoolTotalEligible > 0 ? ($schoolTotalPrayed / $schoolTotalEligible) * 100 : 0;
        $schoolExemptStudentsCount = count($exemptStudentsSet);

        $todayStats = [
            'date_thai' => $todayThai,
            'total_students' => $totalMuslimStudents,
            'checked_students' => $todayCheckedStudents,
            'zuhur_count' => $todayZuhurCount,
            'asr_count' => $todayAsrCount,
            'exempt_count' => $todayExemptCount,
        ];

        // Unique classes for filters
        $classrooms = Student::select('Classroom')->distinct()->whereNotNull('Classroom')->orderBy('Classroom')->pluck('Classroom');
        $grades = Student::select('GradeLevel')->distinct()->whereNotNull('GradeLevel')->orderBy('GradeLevel')->pluck('GradeLevel');

        return view('prayer.dashboard', compact(
            'studentStats',
            'schoolTotalStudents',
            'schoolTotalPrayed',
            'schoolTotalAbsent',
            'schoolPassCount',
            'schoolFailCount',
            'schoolPercentage',
            'schoolTotalExempt',
            'schoolExemptStudentsCount',
            'schoolCorrectedCount',
            'todayStats',
            'classroomRanking',
            'periodAnalytics',
            'urgentStudents',
            'classrooms',
            'grades',
            'month',
            'year',
            'classroom',
            'grade',
            'gender',
            'totalActiveSessions',
            'passingStatus',
            'search'
        ));
    }

    // 5. Export Report Page
    public function export(Request $request)
    {
        $role = strtolower(auth()->user()->Role);
        if (!in_array($role, ['ฝ่ายปกครอง', 'discipline'])) {
            abort(403, 'ไม่มีสิทธิ์ส่งออกรายงาน');
        }

        $search = trim($request->input('search', ''));
        $type = $request->input('type', 'daily'); // daily, weekly, monthly, term
        $date = $request->input('date', Carbon::today()->toDateString());
        $month = $request->input('month', Carbon::today()->month);
        $year = $request->input('year', Carbon::today()->year);
        $classroom = $request->input('classroom');
        $grade = $request->input('grade');
        $gender = $request->input('gender');
        $passingStatus = $request->input('passing_status');

        // Determine date range based on report type
        $startDate = $date;
        $endDate = $date;

        if ($type === 'weekly') {
            $carbonDate = Carbon::parse($date);
            $startDate = $carbonDate->startOfWeek()->toDateString();
            $endDate = $carbonDate->endOfWeek()->toDateString();
        } elseif ($type === 'monthly') {
            $startOfMonth = Carbon::create($year, $month, 1);
            $startDate = $startOfMonth->startOfMonth()->toDateString();
            $endDate = $startOfMonth->endOfMonth()->toDateString();
        } elseif ($type === 'term') {
            $selectedSemesterId = $this->getSelectedSemesterId();
            $semesterObj = \App\Models\Semester::find($selectedSemesterId);
            if ($semesterObj) {
                $term = $semesterObj->term;
                $year = $semesterObj->academic_year - 543;
            } else {
                $term = $request->input('term', 1);
            }
            if ($term == 1) {
                $startDate = "{$year}-05-01";
                $endDate = "{$year}-09-30";
            } else {
                $startDate = "{$year}-11-01";
                $endDate = Carbon::create($year + 1, 3, 31)->toDateString(); // March next year
            }
        }

        // Fetch active check-in sessions in range
        $totalActiveSessions = PrayerRecord::query()
            ->whereBetween('RecordDate', [$startDate, $endDate])
            ->select('RecordDate', 'Period')
            ->distinct()
            ->get()
            ->count();

        // Query students matching filter
        $studentQuery = Student::query();
        if ($classroom) $studentQuery->where('Classroom', $classroom);
        if ($grade) $studentQuery->where('GradeLevel', $grade);
        if ($gender) $studentQuery->where('Gender', $gender);
        if ($search !== '') {
            $studentQuery->where(function($q) use ($search) {
                $q->where('StudentID', 'like', "%{$search}%")
                  ->orWhere('FirstName', 'like', "%{$search}%")
                  ->orWhere('LastName', 'like', "%{$search}%")
                  ->orWhere(\Illuminate\Support\Facades\DB::raw("CONCAT(FirstName, ' ', LastName)"), 'like', "%{$search}%");
            });
        }
        $students = $studentQuery->orderBy('StudentID')->get();

        // Calculate statistics
        $stats = [];
        foreach ($students as $s) {
            $prayedCount = 0;
            $absentCount = 0;
            $exemptCount = 0;
            $percent = 0.0;
            $isPass = false;
            $isCorrected = false;

            if ($type === 'monthly') {
                $statusData = $s->getPrayerMonthlyStatus($month, $year);
                $prayedCount = $statusData['prayed_count'];
                $exemptCount = $statusData['exempt_count'];
                $absentCount = $statusData['absent_count'];
                $percent = $statusData['percentage'];
                $isPass = $statusData['is_passing_percentage'] || $statusData['is_corrected'];
                $isCorrected = $statusData['is_corrected'];
            } else {
                $records = PrayerRecord::where('StudentID', $s->StudentID)
                    ->whereBetween('RecordDate', [$startDate, $endDate])
                    ->get();

                $prayedCount = $records->whereIn('Status', ['มา', 'ละหมาด', 'มาละหมาด', 'ละหมาดแล้ว', 'present'])->count();
                $exemptCount = $records->where('Status', 'ละหมาดไม่ได้')->count();

                $eligibleSessions = max(0, $totalActiveSessions - $exemptCount);
                $absentCount = max(0, $eligibleSessions - $prayedCount);

                $percentage = $eligibleSessions > 0 ? ($prayedCount / $eligibleSessions) * 100 : 100;
                $percent = round($percentage, 1);
                $isPass = $percent >= 80;
            }

            // Apply passing status filter
            if ($passingStatus === 'pass' && !$isPass) continue;
            if ($passingStatus === 'fail' && $isPass) continue;

            $stats[] = [
                'StudentID'    => $s->StudentID,
                'FullName'     => $s->FullName,
                'Class'        => "{$s->GradeLevel}/{$s->Classroom}",
                'Gender'       => $s->Gender,
                'prayed'       => $prayedCount,
                'absent'       => $absentCount,
                'exempt'       => $exemptCount,
                'percent'      => $percent,
                'is_corrected' => $isCorrected,
                'is_pass'      => $isPass
            ];
        }

        // Handle CSV Download
        if ($request->has('excel')) {
            $fileName = "prayer_report_{$type}_{$startDate}_to_{$endDate}.csv";
            $headers = [
                "Content-type"        => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename={$fileName}",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function() use ($stats, $type, $startDate, $endDate) {
                $file = fopen('php://output', 'w');
                // Write UTF-8 BOM so Excel opens it with correct Thai characters
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Title header
                fputcsv($file, ["รายงานการละหมาด ({$type}) ช่วงวันที่: {$startDate} ถึง {$endDate}"]);
                fputcsv($file, []);

                // Column Headers
                fputcsv($file, ["รหัสนักเรียน", "ชื่อ-สกุล", "ระดับชั้น", "เพศ", "จำนวนครั้งที่ละหมาด", "จำนวนครั้งที่ขาด", "จำนวนครั้งที่ละหมาดไม่ได้", "เปอร์เซ็นต์การละหมาด"]);

                // Data Rows
                foreach ($stats as $row) {
                    fputcsv($file, [
                        $row['StudentID'],
                        $row['FullName'],
                        $row['Class'],
                        $row['Gender'],
                        $row['prayed'],
                        $row['absent'],
                        $row['exempt'],
                        $row['percent'] . '%'
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Otherwise return Printable HTML View
        $reportTitle = "รายงานผลการเช็กชื่อการละหมาด";
        $periodText = "ตั้งแต่วันที่ " . Carbon::parse($startDate)->locale('th')->isoFormat('D MMMM YYYY') . 
                      " ถึง " . Carbon::parse($endDate)->locale('th')->isoFormat('D MMMM YYYY');

        return view('prayer.export', compact('stats', 'type', 'startDate', 'endDate', 'reportTitle', 'periodText', 'classroom', 'grade', 'passingStatus', 'search'));
    }

    // 6. Toggle Prayer Correction status (POST)
    public function toggleCorrection(Request $request)
    {
        $role = strtolower(auth()->user()->Role);
        if (!in_array($role, ['ฝ่ายปกครอง', 'discipline'])) {
            abort(403, 'ไม่มีสิทธิ์บันทึกข้อมูลการแก้ละหมาด');
        }

        $request->validate([
            'student_id' => 'required|string',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer',
        ]);

        $studentId = $request->input('student_id');
        $month = $request->input('month');
        $year = $request->input('year');

        // Check if student exists
        $student = Student::where('StudentID', $studentId)->first();
        if (!$student) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลนักเรียน');
        }

        // Toggle the record
        $existing = \App\Models\PrayerCorrection::where([
            'StudentID' => $studentId,
            'Month'     => $month,
            'Year'      => $year,
        ])->first();

        if ($existing) {
            $existing->delete();
            $msg = 'ยกเลิกการบันทึกแก้ละหมาดเรียบร้อยแล้ว';
        } else {
            \App\Models\PrayerCorrection::create([
                'StudentID'  => $studentId,
                'Month'      => $month,
                'Year'       => $year,
                'Status'     => 'แก้ละหมาดแล้ว',
                'RecordedBy' => auth()->user()->UserID,
            ]);
            $msg = 'บันทึกการแก้ละหมาดเรียบร้อยแล้ว';
        }

        return redirect()->back()->with('success', $msg);
    }
}
