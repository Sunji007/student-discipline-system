<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ParentGuardian extends Model {
    protected $table = 'parents';
    protected $primaryKey = 'ParentID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['ParentID', 'UserID', 'StudentID', 'Relationship', 'FullName', 'Phone', 'Email', 'Address'];
    public function user() { return $this->belongsTo(User::class, 'UserID', 'UserID'); }
    public function student() { return $this->hasOne(Student::class, 'ParentID', 'ParentID'); }
}