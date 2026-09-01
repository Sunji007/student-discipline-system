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
