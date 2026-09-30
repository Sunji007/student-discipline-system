<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $primaryKey = 'semester_id';

    protected $fillable = ['academic_year', 'term', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static ?self $cachedCurrentSemester = null;

    protected static function booted()
    {
        static::saved(function () {
            static::clearCache();
        });
        static::deleted(function () {
            static::clearCache();
        });
    }

    public static function clearCache(): void
    {
        static::$cachedCurrentSemester = null;
        \Illuminate\Support\Facades\Cache::forget('current_active_semester');
        \Illuminate\Support\Facades\Cache::forget('semesters_list_all');
    }

    public static function allCached()
    {
        return \Illuminate\Support\Facades\Cache::remember('semesters_list_all', 3600, function() {
            return static::orderBy('academic_year', 'desc')->orderBy('term', 'desc')->get();
        });
    }

    /**
     * ดึงหรือสร้างข้อมูลปีการศึกษาและภาคเรียนปัจจุบันโดยอัตโนมัติตามปฏิทินการศึกษาไทย
     * ภาคเรียนที่ 1: พฤษภาคม - ตุลาคม (เดือน 5-10)
     * ภาคเรียนที่ 2: พฤศจิกายน - เมษายน (เดือน 11-4)
     */
    public static function current(): self
    {
        if (static::$cachedCurrentSemester !== null) {
            return static::$cachedCurrentSemester;
        }

        $now = now();
        $month = (int) $now->format('n');
        $yearAD = (int) $now->format('Y');
        $yearBE = $yearAD + 543;

        if ($month >= 5 && $month <= 10) {
            $academicYear = $yearBE;
            $term = 1;
        } elseif ($month >= 11) {
            $academicYear = $yearBE;
            $term = 2;
        } else {
            $academicYear = $yearBE - 1;
            $term = 2;
        }

        $active = \Illuminate\Support\Facades\Cache::remember('current_active_semester', 3600, function() {
            return static::where('is_active', true)->first();
        });

        // ตรวจสอบว่าภาคเรียน active ปัจจุบันล้าสมัยกว่าปฏิทินจริงหรือไม่ (เช่น กาลเวลาข้ามเข้าสู่ปีการศึกษาใหม่)
        $isOutdated = !$active 
            || ($academicYear > (int) $active->academic_year)
            || ($academicYear == (int) $active->academic_year && $term > (int) $active->term);

        // หากมีภาคเรียน active อยู่ และไม่ล้าสมัยกว่าปฏิทินจริง ให้คืนค่านั้นเสมอ เพื่อเคารพการตั้งค่าของผู้ดูแลระบบ
        if ($active && !$isOutdated) {
            static::$cachedCurrentSemester = $active;
            return $active;
        }

        // ปิด active ภาคเรียนเดิมทั้งหมด เพื่อป้องกันไม่ให้มี is_active ซ้ำซ้อน 2 รายการ
        static::where('is_active', true)->update(['is_active' => false]);

        $semester = static::firstOrCreate(
            ['academic_year' => $academicYear, 'term' => $term],
            ['is_active' => true]
        );

        if (!$semester->is_active) {
            $semester->update(['is_active' => true]);
        }

        // ตรวจสอบและประมวลผลเลื่อนชั้นอัตโนมัติเมื่อเริ่มต้นปีการศึกษาใหม่ (ภาคเรียนที่ 1)
        static::checkAndTriggerPromotion($semester);

        static::clearCache();
        static::$cachedCurrentSemester = $semester;
        \Illuminate\Support\Facades\Cache::put('current_active_semester', $semester, 3600);

        return $semester;
    }

    /**
     * ตรวจสอบและประมวลผลเลื่อนชั้นอัตโนมัติเมื่อเริ่มต้นปีการศึกษาใหม่ (ภาคเรียนที่ 1)
     */
    public static function checkAndTriggerPromotion(Semester $semester): void
    {
        if ((int) $semester->term !== 1) {
            return;
        }

        $year = (int) $semester->academic_year;

        // 1. ตรวจสอบจากประวัติในฐานข้อมูลอย่างถาวร (ป้องกันการเลื่อนซ้ำเมื่อมีการล้างแคช)
        if (\App\Models\StudentPromotion::isYearPromoted($year)) {
            return;
        }

        // 2. ป้องกันการเลื่อนชั้นถอยหลัง: ปีการศึกษาต้องมากกว่าปีที่เคยบันทึกประวัติการเลื่อนชั้นล่าสุด
        if (\Illuminate\Support\Facades\Schema::hasTable('student_promotions')) {
            $maxPromotedYear = \App\Models\StudentPromotion::max('academic_year');
            if ($maxPromotedYear && $year <= (int) $maxPromotedYear) {
                return;
            }
        }

        // 3. ดึงภาคเรียนก่อนหน้าตามลำดับเวลาจริง (ต้องน้อยกว่าปีการศึกษานี้ ห้ามเลือกภาคเรียนในอนาคต)
        $prevSemester = static::where(function ($q) use ($year) {
            $q->where('academic_year', '<', $year);
        })
        ->orderBy('academic_year', 'desc')
        ->orderBy('term', 'desc')
        ->first();

        // 3. ประมวลผลเลื่อนชั้น โดยการบันทึกลงฐานข้อมูลและแคชจะถูกบันทึกเมื่อประมวลผลสำเร็จเท่านั้น
        \App\Services\StudentPromotionService::autoPromoteByBehaviorScore(50, $prevSemester?->semester_id, $year);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'semester_id');
    }

    public function prayerRecords()
    {
        return $this->hasMany(PrayerRecord::class, 'semester_id');
    }

    public function behaviorRecords()
    {
        return $this->hasMany(BehaviorRecord::class, 'semester_id');
    }
}
