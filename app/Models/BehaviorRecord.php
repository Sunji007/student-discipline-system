<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BehaviorRecord extends Model {
    protected $primaryKey = 'RecordID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['RecordID', 'StudentID', 'RuleID', 'RecordDate', 'Description', 'RecordedBy', 'Status', 'Penalty'];
    public function student() { return $this->belongsTo(Student::class, 'StudentID', 'StudentID'); }
    public function rule() { return $this->belongsTo(BehaviorRule::class, 'RuleID', 'RuleID'); }
    public function recorder() { return $this->belongsTo(User::class, 'RecordedBy', 'UserID'); }
    public function appeal() { return $this->hasOne(Appeal::class, 'RecordID', 'RecordID'); }
}