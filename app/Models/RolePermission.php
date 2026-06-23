<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $primaryKey = 'PermissionID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['PermissionID', 'Role', 'ModuleName', 'CanAccess'];
}
