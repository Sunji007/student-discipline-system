<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Teacher extends Model {
    protected $primaryKey = 'TeacherID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['TeacherID', 'UserID', 'department_id'];

    public function user() { return $this->belongsTo(User::class, 'UserID', 'UserID'); }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function advisoryRooms()
    {
        return $this->hasMany(TeacherAdvisoryRoom::class, 'TeacherID', 'TeacherID');
    }

    public function getAdvisoryRoomsAttribute(): array
    {
        $rooms = $this->advisoryRooms()->pluck('Classroom')->toArray();
        $cleaned = array_unique(array_map(function($r) {
            return preg_replace('/^ม\./', '', trim($r));
        }, $rooms));
        return array_values(array_filter($cleaned));
    }
}