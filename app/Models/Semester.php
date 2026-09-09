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

    /**
     * ดึงหรือสร้างข้อมูลปีการศึกษาและภาคเรียนปัจจุบันโดยอัตโนมัติตามปฏิทินการศึกษาไทย
     * ภาคเรียนที่ 1: พฤษภาคม - ตุลาคม (เดือน 5-10)
     * ภาคเรียนที่ 2: พฤศจิกายน - เมษายน (เดือน 11-4)
     */
    public static function current(): self
    {
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

        $semester = static::firstOrCreate(
            ['academic_year' => $academicYear, 'term' => $term],
            ['is_active' => true]
        );

        if (!$semester->is_active) {
            static::where('is_active', true)
                ->where('semester_id', '!=', $semester->semester_id)
                ->update(['is_active' => false]);
            $semester->update(['is_active' => true]);
        }

        return $semester;
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
