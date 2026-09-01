<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplineStaff extends Model
{
    protected $table = 'discipline_staff';
    protected $primaryKey = 'StaffID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['StaffID', 'UserID', 'Position', 'Level'];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function setLevelAttribute($value)
    {
        $mapping = [
            'บันทึกได้' => 1,
            'อนุมัติผล/ตั้งค่า' => 2
        ];
        $this->attributes['Level'] = $mapping[$value] ?? $value;
    }

    public function getLevelAttribute($value)
    {
        $mapping = [
            1 => 'บันทึกได้',
            2 => 'อนุมัติผล/ตั้งค่า'
        ];
        return $mapping[$value] ?? $value;
    }
}
