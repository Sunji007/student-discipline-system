<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Attendance extends Model {
    protected $primaryKey = 'AttendanceID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['AttendanceID', 'StudentID', 'Date', 'Status', 'RecordedBy'];
    protected $casts = [
        'Date' => 'date',
    ];
    public function student() { return $this->belongsTo(Student::class, 'StudentID', 'StudentID'); }
    public function recorder() { return $this->belongsTo(User::class, 'RecordedBy', 'UserID'); }
}