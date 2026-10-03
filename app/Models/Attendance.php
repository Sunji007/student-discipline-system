<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Attendance extends Model {
    protected $primaryKey = 'AttendanceID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['AttendanceID', 'StudentID', 'Date', 'Status', 'RecordedBy', 'semester_id'];
    public function setDateAttribute($value)
    {
        $this->attributes['Date'] = is_string($value) ? substr($value, 0, 10) : \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    public function getDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->AttendanceID)) {
                $model->AttendanceID = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function student() { return $this->belongsTo(Student::class, 'StudentID', 'StudentID'); }
    public function recorder() { return $this->belongsTo(User::class, 'RecordedBy', 'UserID'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id'); }
}