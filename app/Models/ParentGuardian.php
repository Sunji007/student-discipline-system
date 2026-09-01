<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ParentGuardian extends Model {
    protected $table = 'parents';
    protected $primaryKey = 'ParentID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['ParentID', 'UserID', 'StudentID', 'Relationship', 'FirstName', 'FirstName_EN', 'LastName', 'LastName_EN', 'CitizenID', 'Phone', 'Email', 'Address'];
    
    public function getFullNameAttribute() {
        return $this->FirstName . ' ' . $this->LastName;
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['Phone'] = $value ? preg_replace('/\D/', '', $value) : null;
    }

    public function getPhoneAttribute($value)
    {
        if (!$value) return null;
        $clean = preg_replace('/\D/', '', $value);
        if (strlen($clean) === 10) {
            return substr($clean, 0, 3) . '-' . substr($clean, 3, 3) . '-' . substr($clean, 6);
        }
        return $value;
    }
    
    public function user() { return $this->belongsTo(User::class, 'UserID', 'UserID'); }
    public function student() { return $this->belongsTo(Student::class, 'StudentID', 'StudentID'); }

    public function setRelationshipAttribute($value)
    {
        $mapping = [
            'บิดา' => 1,
            'พ่อ' => 1,
            'มารดา' => 2,
            'แม่' => 2,
            'ผู้ปกครอง' => 3,
            'ญาติ' => 3,
            'ปู่' => 3,
            'ย่า' => 3,
            'ตา' => 3,
            'ยาย' => 3,
            'ลุง' => 3,
            'ป้า' => 3,
            'น้า' => 3,
            'อา' => 3,
            'พี่' => 3,
            'อื่นๆ' => 3
        ];
        $this->attributes['Relationship'] = $mapping[$value] ?? 3;
    }

    public function getRelationshipAttribute($value)
    {
        $mapping = [
            1 => 'บิดา',
            2 => 'มารดา',
            3 => 'ผู้ปกครอง'
        ];
        return $mapping[$value] ?? 'ผู้ปกครอง';
    }
}