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
    protected $fillable = ['UserID', 'Username', 'CitizenID', 'Password', 'FirstName', 'FirstName_EN', 'LastName', 'LastName_EN', 'Role', 'Email', 'Phone', 'Status', 'AdditionalInfo'];
    protected $hidden = ['Password', 'remember_token'];
    protected function casts(): array { return ['Password' => 'hashed']; }
    public function getAuthPasswordName(): string { return 'Password'; }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->UserID)) {
                $model->UserID = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function getUserIDAttribute($value)
    {
        if (empty($value)) {
            $newUuid = (string) \Illuminate\Support\Str::uuid();
            if (isset($this->attributes['id']) && $this->attributes['id']) {
                \Illuminate\Support\Facades\DB::table('users')->where('id', $this->attributes['id'])->update(['UserID' => $newUuid]);
            }
            $this->attributes['UserID'] = $newUuid;
            return $newUuid;
        }
        return $value;
    }

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
    public function teacher() { return $this->hasOne(Teacher::class, 'UserID', 'UserID'); }
    public function student() { return $this->hasOne(Student::class, 'UserID', 'UserID'); }
    public function parent() { return $this->hasOne(ParentGuardian::class, 'UserID', 'UserID'); }
    public function parentGuardian() { return $this->hasOne(ParentGuardian::class, 'UserID', 'UserID'); }
    public function parentStudents()
    {
        return $this->hasManyThrough(
            Student::class,
            ParentGuardian::class,
            'UserID',      // Foreign key on ParentGuardian table
            'StudentID',   // Foreign key on Student table
            'UserID',      // Local key on User table
            'StudentID'    // Local key on ParentGuardian table
        );
    }
    public function disciplineOfficer() { return $this->hasOne(DisciplineStaff::class, 'UserID', 'UserID'); }
    
    public function getAvailableRoles(): array
    {
        $roles = [];
        $primary = $this->Role;

        if ($primary) {
            $roles[] = $primary;
        }

        // Check if teacher profile exists
        $hasTeacher = \App\Models\Teacher::where('UserID', $this->UserID)->exists();
        if ($hasTeacher && !in_array('ครู', $roles)) {
            $roles[] = 'ครู';
        }

        // Check Discipline Staff profile
        $hasDiscipline = \App\Models\DisciplineStaff::where('UserID', $this->UserID)->exists();
        if ($hasDiscipline && !in_array('ฝ่ายปกครอง', $roles)) {
            $roles[] = 'ฝ่ายปกครอง';
        }

        // Check Parent record
        $hasParent = \App\Models\ParentGuardian::where('UserID', $this->UserID)->exists()
            || ($this->CitizenID && \App\Models\ParentGuardian::where('CitizenID', $this->CitizenID)->exists())
            || $this->parentStudents()->count() > 0;
            
        if ($hasParent || $primary === 'ผู้ปกครอง') {
            if (!in_array('ผู้ปกครอง', $roles)) {
                $roles[] = 'ผู้ปกครอง';
            }
        }

        return array_values(array_unique($roles));
    }

    public function canAccess(string $module): bool
    {
        $role = session('active_role', $this->Role);
        if (!$role) {
            return false;
        }

        // Always allow student core modules
        if ($role === 'นักเรียน' && in_array($module, ['dashboard', 'behavior-records', 'appeals', 'attendance', 'messages', 'informant-reports'])) {
            return true;
        }

        // Always allow parent core modules
        if ($role === 'ผู้ปกครอง' && in_array($module, ['dashboard', 'behavior-records', 'attendance', 'messages'])) {
            return true;
        }

        // Admin has full access
        if ($role === 'ผู้ดูแลระบบ') {
            return true;
        }

        try {
            if (class_exists(\App\Models\RolePermission::class)) {
                return \App\Models\RolePermission::where('Role', $role)
                    ->where('ModuleName', $module)
                    ->where('CanAccess', 1)
                    ->exists();
            }
        } catch (\Throwable $e) {
            return true;
        }

        return true;
    }

    public function getEmailForPasswordReset()
    {
        return $this->Email;
    }

    public function routeNotificationForMail($notification)
    {
        return $this->Email;
    }
}