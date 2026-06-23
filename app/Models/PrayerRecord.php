<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerRecord extends Model
{
    protected $table = 'prayer_records';
    protected $primaryKey = 'PrayerRecordID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'PrayerRecordID',
        'StudentID',
        'RecordDate',
        'RecordTime',
        'Period',
        'Status',
        'RecordedBy'
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
