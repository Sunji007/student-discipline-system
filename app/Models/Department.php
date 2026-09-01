<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'department_id';

    protected $fillable = [
        'name',
        'short_name',
        'code',
    ];

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'department_id', 'department_id');
    }
}
