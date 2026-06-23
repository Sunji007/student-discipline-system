<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Teacher extends Model {
    protected $primaryKey = 'TeacherID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['TeacherID', 'UserID', 'Department', 'AdvisoryRoom'];
    public function user() { return $this->belongsTo(User::class, 'UserID', 'UserID'); }
}