<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $currentSemester = Semester::current();
        $selectedSemesterId = $currentSemester ? $currentSemester->semester_id : null;

        // Build list of all available classrooms with student count
        $allRooms = [];
        for ($g = 1; $g <= 6; $g++) {
            for ($r = 1; $r <= 4; $r++) {
                $roomName = "ม.{$g}/{$r}";
                $count = Student::whereHas('user', function($q) {
                        $q->where('Status', '!=', 'สำเร็จการศึกษา');
                    })
                    ->inAdvisoryRoom($roomName)
                    ->count();

                $allRooms[] = [
                    'grade' => "ม.{$g}",
                    'room'  => "{$g}/{$r}",
                    'name'  => $roomName,
                    'count' => $count,
                ];
            }
        }

        $selectedRoom = $request->query('source_room', 'ม.1/1');
        $students = collect();
        $suggestedTargetRoom = '';

        if ($selectedRoom) {
            // Determine suggested next room
            // e.g. ม.1/1 -> ม.2/1, ม.6/1 -> graduate
            if (preg_match('/ม\.(\d)\/(\d)/', $selectedRoom, $matches)) {
                $currentGrade = (int)$matches[1];
                $currentClass = (int)$matches[2];
                if ($currentGrade < 6) {
                    $nextGrade = $currentGrade + 1;
                    $suggestedTargetRoom = "ม.{$nextGrade}/{$currentClass}";
                } else {
                    $suggestedTargetRoom = 'graduate';
                }
            }

            $students = Student::whereHas('user', function($q) {
                    $q->where('Status', '!=', 'สำเร็จการศึกษา');
                })
                ->inAdvisoryRoom($selectedRoom)
                ->with('user')
                ->orderBy('StudentID')
                ->get()
                ->map(function ($s) use ($selectedSemesterId) {
                    $score = $selectedSemesterId ? $s->getBehaviorScoreForSemester($selectedSemesterId) : 100;
                    $s->behavior_score = $score;
                    return $s;
                });
        }

        $semestersList = Semester::withCount(['attendances', 'behaviorRecords', 'prayerRecords'])
            ->orderBy('academic_year', 'desc')
            ->orderBy('term', 'desc')
            ->get();

        return view('admin.promotions.index', compact(
            'currentSemester',
            'allRooms',
            'selectedRoom',
            'suggestedTargetRoom',
            'students',
            'semestersList'
        ));
    }

    public function promote(Request $request)
    {
        $request->validate([
            'source_room' => 'required|string',
            'target_room' => 'required|string',
            'students'    => 'required|array',
            'students.*'  => 'array',
        ]);

        $sourceRoom = $request->input('source_room');
        $targetRoom = $request->input('target_room');
        $studentActions = $request->input('students', []);

        $promotedCount = 0;
        $repeatedCount = 0;
        $graduatedCount = 0;
        $heldCount = 0;

        DB::beginTransaction();
        try {
            foreach ($studentActions as $studentId => $data) {
                $student = Student::with('user')->where('StudentID', $studentId)->first();
                if (!$student) continue;

                $action = $data['action'] ?? 'promote';

                if ($action === 'promote') {
                    if ($targetRoom === 'graduate') {
                        if ($student->user) {
                            $student->user->update(['Status' => 'สำเร็จการศึกษา']);
                        }
                        $graduatedCount++;
                    } else {
                        // Extract target grade and classroom
                        // e.g. "ม.2/1" -> GradeLevel: "ม.2", Classroom: "2/1"
                        $targetGrade = 'ม.1';
                        $targetClass = '1/1';

                        if (preg_match('/^(ม\.)?(\d)\/(\d+)$/', $targetRoom, $m)) {
                            $targetGrade = 'ม.' . $m[2];
                            $targetClass = $m[2] . '/' . $m[3];
                        } else {
                            $targetClass = $targetRoom;
                        }

                        $student->update([
                            'GradeLevel' => $targetGrade,
                            'Classroom'  => $targetClass,
                        ]);

                        if ($student->user) {
                            $student->user->update(['Status' => 'ปกติ']);
                        }
                        $promotedCount++;
                    }
                } elseif ($action === 'graduate') {
                    if ($student->user) {
                        $student->user->update(['Status' => 'สำเร็จการศึกษา']);
                    }
                    $graduatedCount++;
                } elseif ($action === 'repeat') {
                    // Keep in current grade and classroom
                    if ($student->user) {
                        $student->user->update(['Status' => 'ปกติ']);
                    }
                    $repeatedCount++;
                } elseif ($action === 'hold') {
                    if ($student->user) {
                        $student->user->update(['Status' => 'พักการเรียน']);
                    }
                    $heldCount++;
                }
            }

            DB::commit();

            $summaryMsg = "ดำเนินการเลื่อนชั้นปีการศึกษาสำเร็จ!";
            $details = [];
            if ($promotedCount > 0) $details[] = "เลื่อนชั้นไปยัง {$targetRoom}: {$promotedCount} คน";
            if ($graduatedCount > 0) $details[] = "สำเร็จการศึกษา: {$graduatedCount} คน";
            if ($repeatedCount > 0) $details[] = "ซ้ำชั้น (คงเดิม): {$repeatedCount} คน";
            if ($heldCount > 0) $details[] = "พักการเรียน: {$heldCount} คน";

            if (!empty($details)) {
                $summaryMsg .= " (" . implode(', ', $details) . ")";
            }

            return redirect()->route('admin.promotions.index', ['source_room' => $targetRoom !== 'graduate' ? $targetRoom : $sourceRoom])
                ->with('success', $summaryMsg);

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาดในการเลื่อนชั้นปีการศึกษา: ' . $e->getMessage());
        }
    }
}
