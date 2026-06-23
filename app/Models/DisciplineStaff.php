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
}
