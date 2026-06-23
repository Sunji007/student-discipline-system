<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
    use HasFactory, Notifiable;
    protected $primaryKey = 'UserID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['UserID', 'Username', 'Password', 'FullName', 'Role', 'Email', 'Phone', 'Status'];
    protected $hidden = ['Password', 'remember_token'];
    protected function casts(): array { return ['Password' => 'hashed']; }
    public function getAuthPasswordName(): string { return 'Password'; }
    public function teacher() { return $this->hasOne(Teacher::class, 'UserID', 'UserID'); }
    public function student() { return $this->hasOne(Student::class, 'UserID', 'UserID'); }
    public function parent() { return $this->hasOne(ParentGuardian::class, 'UserID', 'UserID'); }
    public function parentGuardian() { return $this->hasOne(ParentGuardian::class, 'UserID', 'UserID'); }
    public function disciplineOfficer() { return $this->hasOne(DisciplineStaff::class, 'UserID', 'UserID'); }
}