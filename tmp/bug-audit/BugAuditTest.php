<?php

use App\Http\Controllers\Admin\UserController;
use App\Models\DisciplineStaff;
use App\Models\RolePermission;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BugAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::flushPermissionsCache();
        Cache::flush();
    }

    public function test_changed_student_password_cannot_be_bypassed_with_citizen_id(): void
    {
        $user = User::factory()->create([
            'Username' => '06000',
            'CitizenID' => '1234567890123',
            'Role' => 'นักเรียน',
            'Password' => Hash::make('ChangedPassword!123'),
        ]);
        Student::create([
            'StudentID' => '06000', 'UserID' => $user->UserID,
            'FirstName' => 'Test', 'LastName' => 'Student',
        ]);

        $response = $this->postJson('/login', [
            'Username' => '06000', 'Password' => '1234567890123',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
        $this->assertTrue(Hash::check('ChangedPassword!123', $user->fresh()->Password));
    }

    public function test_demoted_admin_cannot_use_existing_active_role_to_access_user_management(): void
    {
        $user = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $this->actingAs($user)->withSession(['active_role' => 'ผู้ดูแลระบบ']);

        // A separate administrator changes the role in the database.
        User::where('UserID', $user->UserID)->update(['Role' => 'ครู']);
        $this->actingAs($user->fresh());

        $response = $this->get(route('admin.users.index'));

        $this->assertContains($response->status(), [302, 403],
            'Demoted user retained access to admin.users.index');
    }

    public function test_discipline_additional_role_can_open_prayer_scan(): void
    {
        $user = User::factory()->create(['Role' => 'ครู']);
        Teacher::create(['TeacherID' => '20001', 'UserID' => $user->UserID]);
        // SQLite retains the historical enum; use its text representation
        // for the fixture so this test reaches authorization logic.
        \Illuminate\Support\Facades\DB::table('discipline_staff')->insert([
            'StaffID' => (string) Str::uuid(), 'UserID' => $user->UserID,
            'Level' => 'บันทึกได้',
        ]);
        RolePermission::create([
            'PermissionID' => (string) Str::uuid(), 'Role' => 'ฝ่ายปกครอง',
            'ModuleName' => 'attendance', 'CanAccess' => 1,
        ]);

        $this->actingAs($user)->withSession(['active_role' => 'ฝ่ายปกครอง'])
            ->get(route('prayer.scan'))->assertOk();
    }

    public function test_generated_teacher_id_collision_does_not_leave_partial_user(): void
    {
        $existing = User::factory()->create(['Username' => 'abcdefghij1', 'Role' => 'ผู้ดูแลระบบ']);
        Teacher::create(['TeacherID' => 'abcdefghij', 'UserID' => $existing->UserID]);

        // Isolate persistence from DNS validation. These fields satisfy the
        // controller's validation; TeacherID is nullable and absent.
        $data = [
            'Username' => 'abcdefghij2', 'CitizenID' => '1234567890123',
            'Password' => 'StrongPassword!123', 'FirstName' => 'Test',
            'LastName' => 'User', 'FirstName_EN' => 'Test', 'LastName_EN' => 'User',
            'Email' => 'bugaudit@gmail.com', 'Role' => 'ผู้ดูแลระบบ', 'Status' => 'ปกติ',
        ];
        $request = Mockery::mock(Request::class)->makePartial();
        $request->initialize([], $data + ['additional_roles' => ['ครู']]);
        $request->setMethod('POST');
        $request->shouldReceive('validate')->once()->andReturn($data);

        try {
            (new UserController())->store($request);
            $this->fail('Expected generated TeacherID collision');
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            $this->assertStringContainsString('teachers.TeacherID', $e->getMessage());
        }

        $this->assertDatabaseMissing('users', ['Username' => 'abcdefghij2']);
    }
}
