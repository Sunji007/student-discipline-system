<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Student extends Model {
    protected $primaryKey = 'StudentID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['StudentID', 'UserID', 'FirstName', 'FirstName_EN', 'LastName', 'LastName_EN', 'GradeLevel', 'Classroom', 'BehaviorScore', 'RiskStatus', 'ParentID', 'Gender', 'Photo'];
    
    public function getFullNameAttribute() {
        return $this->FirstName . ' ' . $this->LastName;
    }
    
    public function user() { return $this->belongsTo(User::class, 'UserID', 'UserID'); }
    public function parent() { return $this->hasOne(ParentGuardian::class, 'StudentID', 'StudentID'); }
    public function parents() { return $this->hasMany(ParentGuardian::class, 'StudentID', 'StudentID'); }
    public function behaviorRecords() { return $this->hasMany(BehaviorRecord::class, 'StudentID', 'StudentID')->orderBy('RecordDate', 'desc'); }
    public function attendances() { return $this->hasMany(Attendance::class, 'StudentID', 'StudentID'); }
    public function appeals() { return $this->hasMany(Appeal::class, 'StudentID', 'StudentID'); }
    public function prayerRecords() { return $this->hasMany(PrayerRecord::class, 'StudentID', 'StudentID'); }
    public function prayerCorrections() { return $this->hasMany(PrayerCorrection::class, 'StudentID', 'StudentID'); }
    public function getAdvisoryTeacherAttribute()
    {
        $classroom = $this->Classroom;
        $gradeLevel = $this->GradeLevel;
        if (!$classroom) return null;

        $rooms = [$classroom];

        // Extract grade number (e.g., 'ม.1' -> '1')
        $gradeNum = preg_replace('/[^0-9]/', '', $gradeLevel);
        
        // Extract class number (e.g., '6/3' -> '3', '1' -> '1')
        $classNum = $classroom;
        if (str_contains($classroom, '/')) {
            $parts = explode('/', $classroom);
            $classNum = end($parts);
        }
        $classNum = preg_replace('/[^0-9]/', '', $classNum);

        if ($gradeNum && $classNum) {
            $rooms[] = "ม.{$gradeNum}/{$classNum}";
            $rooms[] = "{$gradeNum}/{$classNum}";
        }

        // Also fallback to the old combinations
        if ($gradeLevel && !str_starts_with($classroom, $gradeLevel)) {
            $rooms[] = $gradeLevel . '/' . $classroom;
            $rooms[] = str_replace('ม.', '', $gradeLevel) . '/' . $classroom;
        }

        return Teacher::whereHas('advisoryRooms', function($q) use ($rooms) {
            $q->whereIn('Classroom', array_unique($rooms));
        })->first();
    }

    public function getAdvisoryTeachersAttribute()
    {
        $classroom = $this->Classroom;
        $gradeLevel = $this->GradeLevel;
        if (!$classroom) return collect([]);

        $rooms = [$classroom];
        $gradeNum = preg_replace('/[^0-9]/', '', $gradeLevel);
        
        $classNum = $classroom;
        if (str_contains($classroom, '/')) {
            $parts = explode('/', $classroom);
            $classNum = end($parts);
        }
        $classNum = preg_replace('/[^0-9]/', '', $classNum);

        if ($gradeNum && $classNum) {
            $rooms[] = "ม.{$gradeNum}/{$classNum}";
            $rooms[] = "{$gradeNum}/{$classNum}";
        }

        if ($gradeLevel && !str_starts_with($classroom, $gradeLevel)) {
            $rooms[] = $gradeLevel . '/' . $classroom;
            $rooms[] = str_replace('ม.', '', $gradeLevel) . '/' . $classroom;
        }

        return Teacher::whereHas('advisoryRooms', function($q) use ($rooms) {
            $q->whereIn('Classroom', array_unique($rooms));
        })->get();
    }

    public function scopeInAdvisoryRoom($query, $advisoryRoom)
    {
        if (is_array($advisoryRoom)) {
            $advisoryRoom = array_filter($advisoryRoom);
            if (empty($advisoryRoom)) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where(function ($q) use ($advisoryRoom) {
                foreach ($advisoryRoom as $room) {
                    $q->orWhere(function ($sub) use ($room) {
                        $sub->inAdvisoryRoom($room);
                    });
                }
            });
        }

        if (!$advisoryRoom) {
            return $query->whereRaw('1 = 0');
        }

        $gradeNum = null;
        $classNum = null;
        
        if (str_contains($advisoryRoom, '/')) {
            [$gradePart, $classPart] = explode('/', $advisoryRoom, 2);
            $gradeNum = preg_replace('/[^0-9]/', '', $gradePart);
            $classNum = preg_replace('/[^0-9]/', '', $classPart);
        } else {
            $classNum = preg_replace('/[^0-9]/', '', $advisoryRoom);
        }

        return $query->where(function ($q) use ($advisoryRoom, $gradeNum, $classNum) {
            $q->where('Classroom', $advisoryRoom);
            
            if ($gradeNum && $classNum) {
                $q->orWhere(function ($sub) use ($gradeNum, $classNum) {
                    $sub->where(function ($sub2) use ($classNum) {
                            $sub2->where('Classroom', $classNum)
                                 ->orWhere('Classroom', 'like', '%/' . $classNum);
                        })
                        ->where(function ($sub2) use ($gradeNum) {
                            $sub2->where('GradeLevel', $gradeNum)
                                 ->orWhere('GradeLevel', 'ม.' . $gradeNum);
                        });
                });
            }
        });
    }

    public function getClassroomDisplayAttribute()
    {
        $classroom = (string) $this->Classroom;
        $grade = (string) $this->GradeLevel;
        if (!$classroom && !$grade) return '';
        
        // รูปแบบระดับชั้น เช่น "ม.2"
        $gradeNum = preg_replace('/[^0-9]/', '', $grade);
        if (!$gradeNum && preg_match('/^(ม\.)?(\d)/', $classroom, $gm)) {
            $gradeNum = $gm[2];
        }
        $gradeDisplay = $gradeNum ? "ม.{$gradeNum}" : ($grade ?: 'ม.1');

        // ดึงหมายเลขห้องเรียนตัวสุดท้าย (ตัดเลขระดับชั้นที่อาจซ้ำซ้อนออก)
        // เช่น "1/1" -> ห้อง 1, "ม.1/1" -> ห้อง 1, "2/1" -> ห้อง 1, "1" -> ห้อง 1
        if (str_contains($classroom, '/')) {
            $parts = explode('/', $classroom);
            $roomNum = end($parts);
        } else {
            $roomNum = $classroom;
        }

        $cleanRoomNum = preg_replace('/[^0-9]/', '', $roomNum);
        if (!$cleanRoomNum) {
            $cleanRoomNum = '1';
        }

        return "{$gradeDisplay}/{$cleanRoomNum}";
    }

    public function getPrayerMonthlyStatus($month, $year)
    {
        // 1. Fetch total check-in sessions in this month
        $totalActiveSessions = \App\Models\PrayerRecord::whereYear('RecordDate', $year)
            ->whereMonth('RecordDate', $month)
            ->select('RecordDate', 'Period')
            ->distinct()
            ->get()
            ->count();

        // 2. Fetch student's records for this month
        $records = $this->prayerRecords()
            ->whereYear('RecordDate', $year)
            ->whereMonth('RecordDate', $month)
            ->get();

        $prayedCount = $records->whereIn('Status', ['มา', 'ละหมาด', 'มาละหมาด', 'ละหมาดแล้ว', 'present'])->count();
        $exemptCount = $records->where('Status', 'ละหมาดไม่ได้')->count();

        $eligibleSessions = max(0, $totalActiveSessions - $exemptCount);
        $absentCount = max(0, $eligibleSessions - $prayedCount);

        $percentage = $eligibleSessions > 0 ? ($prayedCount / $eligibleSessions) * 100 : 100;
        $percent = round($percentage, 1);
        $isPassing = $percent >= 80;

        // 3. Check for correction
        $correction = $this->prayerCorrections()
            ->where('Year', $year)
            ->where('Month', $month)
            ->first();

        $isCorrected = !empty($correction);

        $status = 'fail';
        $statusText = 'ไม่ผ่านเกณฑ์';
        if ($isPassing) {
            $status = 'pass';
            $statusText = 'ผ่านเกณฑ์';
        } elseif ($isCorrected) {
            $status = 'corrected';
            $statusText = 'แก้ละหมาดแล้ว';
        }

        return [
            'prayed_count' => $prayedCount,
            'exempt_count' => $exemptCount,
            'total_sessions' => $totalActiveSessions,
            'eligible_sessions' => $eligibleSessions,
            'absent_count' => $absentCount,
            'percentage' => $percent,
            'is_passing_percentage' => $isPassing,
            'is_corrected' => $isCorrected,
            'status' => $status,
            'status_text' => $statusText,
        ];
    }

    public function getBehaviorScoreForSemester($semesterId)
    {
        $netModifier = \Illuminate\Support\Facades\DB::table('behavior_records')
            ->join('behavior_rules', 'behavior_records.RuleID', '=', 'behavior_rules.RuleID')
            ->where('behavior_records.StudentID', $this->StudentID)
            ->where('behavior_records.semester_id', $semesterId)
            ->whereIn('behavior_records.Status', ['อนุมัติ', 'อนุมัติแล้ว', 'อยู่ในระหว่างยื่นอุทธรณ์'])
            ->sum(\Illuminate\Support\Facades\DB::raw("CASE WHEN behavior_rules.RuleType = 'ตัดคะแนน' THEN -ABS(behavior_rules.ScoreModifier) ELSE ABS(behavior_rules.ScoreModifier) END"));

        return max(0, min(100, 100 + ($netModifier ?? 0)));
    }

    public function getRiskStatusForSemester($semesterId)
    {
        $score = $this->getBehaviorScoreForSemester($semesterId);
        return match(true) {
            $score >= 80 => 'ปกติ',
            $score >= 60 => 'ตักเตือน',
            default      => 'ทัณฑ์บน',
        };
    }
}