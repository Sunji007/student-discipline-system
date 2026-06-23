<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Student extends Model {
    protected $primaryKey = 'StudentID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['StudentID', 'UserID', 'FullName', 'GradeLevel', 'Classroom', 'BehaviorScore', 'RiskStatus', 'ParentID', 'Gender', 'Photo'];
    public function user() { return $this->belongsTo(User::class, 'UserID', 'UserID'); }
    public function parent() { return $this->belongsTo(ParentGuardian::class, 'ParentID', 'ParentID'); }
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

        return Teacher::whereIn('AdvisoryRoom', array_unique($rooms))->first();
    }

    public function scopeInAdvisoryRoom($query, $advisoryRoom)
    {
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
        $classroom = $this->Classroom;
        $grade = $this->GradeLevel;
        if (!$classroom) return '';
        if (!$grade) return $classroom;
        
        if (str_starts_with($classroom, $grade)) {
            return $classroom;
        }
        
        // Handle e.g. "6/3" and "ม.6" -> "ม.6/3"
        $cleanGrade = str_replace('ม.', '', $grade);
        if (str_starts_with($classroom, $cleanGrade . '/')) {
            return 'ม.' . $classroom;
        }
        
        return $grade . '/' . $classroom;
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

        $prayedCount = $records->where('Status', 'ละหมาด')->count();
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
}