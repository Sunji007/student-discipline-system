<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerCorrection extends Model
{
    protected $table = 'prayer_corrections';
    
    protected $primaryKey = 'prayer_correction_id';
    
    protected $fillable = [
        'StudentID',
        'Year',
        'Month',
        'Status',
        'RecordedBy',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'StudentID', 'StudentID');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'RecordedBy', 'UserID');
    }
}
