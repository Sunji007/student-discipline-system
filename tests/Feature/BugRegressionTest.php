<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UserController;
use App\Models\RolePermission;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BugRegressionTest extends TestCase
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
        $user = $this->createStudent('ChangedPassword!123');

        $this->postJson('/login', [
            'Username' => '06000', 'Password' => '1234567890123',
        ])->assertStatus(422);

        $this->assertGuest();
        $this->assertTrue(Hash::check('ChangedPassword!123', $user->fresh()->Password));
    }

    public function test_student_can_log_in_with_current_password(): void
    {
        $user = $this->createStudent('ChangedPassword!123');

        $this->postJson('/login', [
            'Username' => '06000', 'Password' => 'ChangedPassword!123',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_student_can_use_citizen_id_when_it_is_the_actual_initial_password(): void
    {
        $user = $this->createStudent('1234567890123');

        $this->postJson('/login', [
            'Username' => '06000', 'Password' => '1234567890123',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_demoted_admin_cannot_keep_admin_access_from_session(): void
    {
        $user = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $this->actingAs($user)->withSession([
            'active_role' => 'ผู้ดูแลระบบ',
            'user_available_roles_' . $user->UserID => ['ผู้ดูแลระบบ'],
        ]);

        // Another administrator changes this user's role while they are logged in.
        User::where('UserID', $user->UserID)->update(['Role' => 'ครู']);
        $this->actingAs($user->fresh());

        $this->get(route('admin.users.index'))->assertRedirect(route('home'));
        $this->assertFalse($user->fresh()->canAccess('users'));
    }

    public function test_revoked_role_cannot_access_routes_that_only_check_module_permissions(): void
    {
        $user = User::factory()->create(['Role' => 'ครู']);

        $this->actingAs($user)->withSession([
            'active_role' => 'ผู้ดูแลระบบ',
            'user_available_roles_' . $user->UserID => ['ผู้ดูแลระบบ'],
        ])->get(route('prayer.export-select'))->assertForbidden();
    }

    public function test_removed_additional_role_cannot_be_selected_from_stale_session_cache(): void
    {
        $user = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $teacher = Teacher::create(['TeacherID' => '20001', 'UserID' => $user->UserID]);
        $this->actingAs($user)->withSession([
            'user_available_roles_' . $user->UserID => ['ผู้ดูแลระบบ', 'ครู'],
        ]);
        $teacher->delete();

        $this->postJson(route('select-role.store'), ['role' => 'ครู'])
            ->assertStatus(422);
    }

    public function test_discipline_additional_role_can_open_prayer_scan(): void
    {
        $user = $this->createTeacherWithDisciplineRole();

        $this->actingAs($user)->withSession(['active_role' => 'ฝ่ายปกครอง'])
            ->get(route('prayer.scan'))->assertOk();
    }

    public function test_discipline_additional_role_can_record_prayer_scan(): void
    {
        $user = $this->createTeacherWithDisciplineRole();
        $studentUser = $this->createStudent('Password!123');

        $this->actingAs($user)->withSession(['active_role' => 'ฝ่ายปกครอง'])
            ->postJson(route('prayer.scan.store'), [
                'student_id' => $studentUser->student->StudentID,
                'period' => 'ซุฮรี', 'status' => 'ละหมาด',
            ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('prayer_records', [
            'StudentID' => '06000', 'RecordedBy' => $user->UserID,
            'Period' => 'ซุฮรี', 'Status' => 'ละหมาด',
        ]);
    }

    public function test_discipline_profile_does_not_grant_scanning_when_teacher_role_is_active(): void
    {
        $user = $this->createTeacherWithDisciplineRole('ฝ่ายปกครอง');
        $this->allowAttendance('ครู');

        $this->actingAs($user)->withSession(['active_role' => 'ครู'])
            ->get(route('prayer.scan'))->assertForbidden();
    }

    public function test_creating_additional_teacher_profile_avoids_username_prefix_collision(): void
    {
        $this->createExistingTeacher();
        $request = $this->validatedUserRequest($this->userData());

        (new UserController())->store($request);

        $newUser = User::where('Username', 'abcdefghij2')->firstOrFail();
        $this->assertNotNull($newUser->teacher);
        $this->assertNotSame('abcdefghij', $newUser->teacher->TeacherID);
        $this->assertSame(2, Teacher::count());
    }

    public function test_failed_profile_creation_rolls_back_new_user(): void
    {
        $this->createExistingTeacher();
        $request = $this->validatedUserRequest($this->userData() + ['TeacherID' => 'abcdefghij']);

        // Simulate a collision after validation, e.g. a concurrent request.
        try {
            (new UserController())->store($request);
            $this->fail('Expected a teacher ID constraint violation');
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            $this->assertStringContainsString('teachers.TeacherID', $e->getMessage());
        }

        $this->assertDatabaseMissing('users', ['Username' => 'abcdefghij2']);
        $this->assertSame(1, Teacher::count());
    }

    public function test_adding_teacher_role_to_existing_user_avoids_username_prefix_collision(): void
    {
        $this->createExistingTeacher();
        $user = User::factory()->create(['Username' => 'abcdefghij2', 'Role' => 'ผู้ดูแลระบบ']);
        $request = $this->validatedUserRequest($this->userData());

        (new UserController())->update($request, $user);

        $this->assertNotNull($user->fresh()->teacher);
        $this->assertNotSame('abcdefghij', $user->fresh()->teacher->TeacherID);
    }

    public function test_failed_profile_update_rolls_back_user_changes(): void
    {
        $user = User::factory()->create(['Role' => 'ผู้ดูแลระบบ', 'FirstName' => 'Original']);
        $data = $this->userData();
        $data['advisory_rooms'] = ['1/1'];
        $request = $this->validatedUserRequest($data);

        // Fail after the teacher profile is created, during room persistence.
        \App\Models\TeacherAdvisoryRoom::creating(function () {
            throw new \RuntimeException('Room persistence failed');
        });
        try {
            (new UserController())->update($request, $user);
            $this->fail('Expected room persistence failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Room persistence failed', $e->getMessage());
        } finally {
            \App\Models\TeacherAdvisoryRoom::flushEventListeners();
        }

        $this->assertSame('Original', $user->fresh()->FirstName);
        $this->assertDatabaseMissing('teachers', ['UserID' => $user->UserID]);
    }

    private function createStudent(string $password): User
    {
        $user = User::factory()->create([
            'Username' => '06000', 'CitizenID' => '1234567890123',
            'Role' => 'นักเรียน', 'Password' => Hash::make($password),
        ]);
        Student::create([
            'StudentID' => '06000', 'UserID' => $user->UserID,
            'FirstName' => 'Test', 'LastName' => 'Student',
        ]);

        return $user;
    }

    private function createTeacherWithDisciplineRole(string $primaryRole = 'ครู'): User
    {
        $user = User::factory()->create(['Role' => $primaryRole]);
        Teacher::create(['TeacherID' => '20001', 'UserID' => $user->UserID]);
        // SQLite retains the historical enum because conversion to TINYINT is MySQL-only.
        DB::table('discipline_staff')->insert([
            'StaffID' => (string) Str::uuid(), 'UserID' => $user->UserID, 'Level' => 'บันทึกได้',
        ]);
        $this->allowAttendance('ฝ่ายปกครอง');

        return $user;
    }

    private function allowAttendance(string $role): void
    {
        RolePermission::create([
            'PermissionID' => (string) Str::uuid(), 'Role' => $role,
            'ModuleName' => 'attendance', 'CanAccess' => 1,
        ]);
    }

    private function createExistingTeacher(): void
    {
        $user = User::factory()->create(['Username' => 'abcdefghij1', 'Role' => 'ผู้ดูแลระบบ']);
        Teacher::create(['TeacherID' => 'abcdefghij', 'UserID' => $user->UserID]);
    }

    private function userData(): array
    {
        return [
            'Username' => 'abcdefghij2', 'CitizenID' => '1234567890123',
            'Password' => 'StrongPassword!123', 'FirstName' => 'Test', 'LastName' => 'User',
            'FirstName_EN' => 'Test', 'LastName_EN' => 'User', 'Email' => 'bugaudit@gmail.com',
            'Role' => 'ผู้ดูแลระบบ', 'Status' => 'ปกติ',
        ];
    }

    private function validatedUserRequest(array $data): Request
    {
        // Isolate persistence from the external DNS check in email validation.
        $request = \Mockery::mock(Request::class)->makePartial();
        $request->initialize([], $data + ['additional_roles' => ['ครู']]);
        $request->setMethod('POST');
        $request->shouldReceive('validate')->once()->andReturn($data);

        return $request;
    }
}
