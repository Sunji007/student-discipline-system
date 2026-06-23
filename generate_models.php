<?php

$models = [
    'User' => <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
    use HasFactory, Notifiable;
    protected \$primaryKey = 'UserID';
    protected \$fillable = ['Username', 'Password', 'FullName', 'Role', 'Email', 'Phone', 'Status'];
    protected \$hidden = ['Password', 'remember_token'];
    protected function casts(): array { return ['Password' => 'hashed']; }
    public function getAuthPasswordName(): string { return 'Password'; }
    public function teacher() { return \$this->hasOne(Teacher::class, 'UserID', 'UserID'); }
    public function student() { return \$this->hasOne(Student::class, 'UserID', 'UserID'); }
    public function parent() { return \$this->hasOne(ParentGuardian::class, 'UserID', 'UserID'); }
}
EOT,
    'Student' => <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Student extends Model {
    protected \$primaryKey = 'StudentID';
    public \$incrementing = false;
    protected \$keyType = 'string';
    protected \$fillable = ['StudentID', 'UserID', 'FullName', 'GradeLevel', 'Classroom', 'BehaviorScore', 'RiskStatus', 'ParentID'];
    public function user() { return \$this->belongsTo(User::class, 'UserID', 'UserID'); }
    public function parent() { return \$this->belongsTo(ParentGuardian::class, 'ParentID', 'ParentID'); }
    public function behaviorRecords() { return \$this->hasMany(BehaviorRecord::class, 'StudentID', 'StudentID')->orderBy('RecordDate', 'desc'); }
}
EOT,
    'Teacher' => <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Teacher extends Model {
    protected \$primaryKey = 'TeacherID';
    protected \$fillable = ['UserID', 'FullName', 'Subject', 'AdvisoryRoom'];
    public function user() { return \$this->belongsTo(User::class, 'UserID', 'UserID'); }
}
EOT,
    'ParentGuardian' => <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ParentGuardian extends Model {
    protected \$table = 'parents';
    protected \$primaryKey = 'ParentID';
    protected \$fillable = ['UserID', 'FullName', 'StudentID'];
    public function user() { return \$this->belongsTo(User::class, 'UserID', 'UserID'); }
    public function student() { return \$this->hasMany(Student::class, 'ParentID', 'ParentID'); }
}
EOT,
    'BehaviorRecord' => <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BehaviorRecord extends Model {
    protected \$primaryKey = 'RecordID';
    protected \$fillable = ['StudentID', 'RuleID', 'RecordDate', 'Description', 'RecorderID', 'Status', 'Penalty'];
    public function student() { return \$this->belongsTo(Student::class, 'StudentID', 'StudentID'); }
    public function rule() { return \$this->belongsTo(BehaviorRule::class, 'RuleID', 'RuleID'); }
    public function recorder() { return \$this->belongsTo(User::class, 'RecorderID', 'UserID'); }
    public function appeal() { return \$this->hasOne(Appeal::class, 'RecordID', 'RecordID'); }
}
EOT,
    'BehaviorRule' => <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BehaviorRule extends Model {
    protected \$primaryKey = 'RuleID';
    protected \$fillable = ['RuleName', 'RuleType', 'ScoreModifier', 'Category', 'Description'];
}
EOT,
    'Appeal' => <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Appeal extends Model {
    protected \$primaryKey = 'AppealID';
    protected \$fillable = ['RecordID', 'StudentID', 'Reason', 'EvidenceFile', 'AppealDate', 'Status', 'ReviewerID', 'ReviewDate', 'ReviewNotes'];
    public function behaviorRecord() { return \$this->belongsTo(BehaviorRecord::class, 'RecordID', 'RecordID'); }
    public function student() { return \$this->belongsTo(Student::class, 'StudentID', 'StudentID'); }
}
EOT,
    'Attendance' => <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Attendance extends Model {
    protected \$primaryKey = 'AttendanceID';
    protected \$fillable = ['StudentID', 'Date', 'Status', 'RecorderID'];
    public function student() { return \$this->belongsTo(Student::class, 'StudentID', 'StudentID'); }
    public function recorder() { return \$this->belongsTo(User::class, 'RecorderID', 'UserID'); }
}
EOT
];

foreach ($models as $name => $content) {
    file_put_contents(__DIR__ . "/app/Models/{$name}.php", $content);
}
echo "Models generated.\n";
