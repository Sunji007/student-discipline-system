<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAdvisoryRoom extends Model
{
    protected $table = 'teacher_advisory_rooms';

    protected $primaryKey = 'teacher_advisory_room_id';

    protected $fillable = [
        'TeacherID',
        'Classroom',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'TeacherID', 'TeacherID');
    }
}
