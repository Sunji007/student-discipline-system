<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Appeal extends Model {
    protected $primaryKey = 'AppealID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['AppealID', 'RecordID', 'StudentID', 'Reason', 'EvidencePath', 'AppealDate', 'Status', 'ReviewerID', 'ReviewDate', 'ReviewNotes'];
    public function behaviorRecord() { return $this->belongsTo(BehaviorRecord::class, 'RecordID', 'RecordID'); }
    public function student() { return $this->belongsTo(Student::class, 'StudentID', 'StudentID'); }
}