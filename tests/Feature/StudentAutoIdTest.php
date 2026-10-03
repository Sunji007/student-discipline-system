<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\RolePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class StudentAutoIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::flushPermissionsCache();
        \Illuminate\Support\Facades\Cache::flush();

        RolePermission::create([
            'PermissionID' => (string) Str::uuid(),
            'Role' => 'ผู้ดูแลระบบ',
            'ModuleName' => 'users',
            'CanAccess' => 1
        ]);
        RolePermission::create([
            'PermissionID' => (string) Str::uuid(),
            'Role' => 'ผู้ดูแลระบบ',
            'ModuleName' => 'dashboard',
            'CanAccess' => 1
        ]);
    }

    private function createAdminUser(): User
    {
        return User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => '40001',
            'CitizenID' => '1234567890123',
            'FirstName' => 'Admin',
            'LastName'  => 'System',
            'Password'  => bcrypt('password'),
            'Role'      => 'ผู้ดูแลระบบ',
            'Status'    => 'ปกติ',
        ]);
    }

    public function test_initial_student_id_starts_at_06000()
    {
        $nextId = Student::generateNextStudentId();
        $this->assertEquals('06000', $nextId);
    }

    public function test_student_id_increments_sequentially()
    {
        // Create first student
        $user1 = User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => '06000',
            'CitizenID' => '1111111111111',
            'FirstName' => 'นักเรียน',
            'LastName'  => 'หนึ่ง',
            'Password'  => bcrypt('password'),
            'Role'      => 'นักเรียน',
            'Status'    => 'ปกติ',
        ]);

        Student::create([
            'StudentID'     => '06000',
            'UserID'        => $user1->UserID,
            'FirstName'     => 'นักเรียน',
            'LastName'      => 'หนึ่ง',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => 'ม.1/1',
            'Gender'        => 'ชาย',
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        $nextId = Student::generateNextStudentId();
        $this->assertEquals('06001', $nextId);

        // Create second student
        $user2 = User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => '06001',
            'CitizenID' => '2222222222222',
            'FirstName' => 'นักเรียน',
            'LastName'  => 'สอง',
            'Password'  => bcrypt('password'),
            'Role'      => 'นักเรียน',
            'Status'    => 'ปกติ',
        ]);

        Student::create([
            'StudentID'     => '06001',
            'UserID'        => $user2->UserID,
            'FirstName'     => 'นักเรียน',
            'LastName'      => 'สอง',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => 'ม.1/1',
            'Gender'        => 'หญิง',
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        $this->assertEquals('06002', Student::generateNextStudentId());
    }

    public function test_legacy_ids_do_not_interfere_with_06000_range()
    {
        // Existing legacy student with 7-digit ID (e.g. 6910101)
        $userOld = User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => '6910101',
            'CitizenID' => '3333333333333',
            'FirstName' => 'เก่า',
            'LastName'  => 'หนึ่ง',
            'Password'  => bcrypt('password'),
            'Role'      => 'นักเรียน',
            'Status'    => 'ปกติ',
        ]);
        Student::create([
            'StudentID'     => '6910101',
            'UserID'        => $userOld->UserID,
            'FirstName'     => 'เก่า',
            'LastName'      => 'หนึ่ง',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => 'ม.1/1',
            'Gender'        => 'ชาย',
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        // Existing legacy student with 10001
        User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => '10001',
            'CitizenID' => '4444444444444',
            'FirstName' => 'เก่า',
            'LastName'  => 'สอง',
            'Password'  => bcrypt('password'),
            'Role'      => 'นักเรียน',
            'Status'    => 'ปกติ',
        ]);

        // Teacher 20001, Discipline 30001, Admin 40001, Parent 50001
        User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => '20001',
            'CitizenID' => '5555555555555',
            'FirstName' => 'ครู',
            'LastName'  => 'ทดสอบ',
            'Password'  => bcrypt('password'),
            'Role'      => 'ครู',
            'Status'    => 'ปกติ',
        ]);

        $this->assertEquals('06000', Student::generateNextStudentId());
    }

    public function test_collision_skips_occupied_username_or_student_id()
    {
        // Pre-create User with 06000
        User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => '06000',
            'CitizenID' => '6666666666666',
            'FirstName' => 'คน',
            'LastName'  => 'จอง',
            'Password'  => bcrypt('password'),
            'Role'      => 'นักเรียน',
            'Status'    => 'ปกติ',
        ]);

        // Pre-create Student with 06001
        $u2 = User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => '06001',
            'CitizenID' => '7777777777777',
            'FirstName' => 'คน',
            'LastName'  => 'สอง',
            'Password'  => bcrypt('password'),
            'Role'      => 'นักเรียน',
            'Status'    => 'ปกติ',
        ]);
        Student::create([
            'StudentID'     => '06001',
            'UserID'        => $u2->UserID,
            'FirstName'     => 'คน',
            'LastName'      => 'สอง',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => 'ม.1/1',
            'Gender'        => 'ชาย',
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        $this->assertEquals('06002', Student::generateNextStudentId());
    }

    public function test_get_next_id_api_returns_auto_number()
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->getJson(route('admin.students.get-next-id'));

        $response->assertStatus(200);
        $response->assertJson(['StudentID' => '06000']);
    }

    public function test_admin_create_student_page_renders_06000()
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.students.create'));

        $response->assertStatus(200);
        $response->assertSee('value="06000"', false);
    }

    public function test_admin_stores_new_student_with_auto_id()
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'StudentID'  => '06000',
            'CitizenID'  => '1999999999999',
            'prefix'     => 'นาย',
            'FirstName'  => 'ทดสอบ',
            'LastName'   => 'ระบบ',
            'GradeLevel' => 'ม.4',
            'Classroom'  => '1',
            'Gender'     => 'ชาย',
        ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('students', [
            'StudentID' => '06000',
            'FirstName' => 'นายทดสอบ',
            'Classroom' => 'ม.4/1',
        ]);

        $this->assertDatabaseHas('users', [
            'Username'  => '06000',
            'CitizenID' => '1999999999999',
            'Role'      => 'นักเรียน',
        ]);

        // Next student ID should now be 06001
        $this->assertEquals('06001', Student::generateNextStudentId());
    }

    public function test_command_migrates_all_legacy_student_ids_to_auto_number()
    {
        $u1 = User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => '6910101',
            'CitizenID' => '1111111111111',
            'FirstName' => 'นักเรียน',
            'LastName'  => 'หนึ่ง',
            'Password'  => bcrypt('password'),
            'Role'      => 'นักเรียน',
            'Status'    => 'ปกติ',
        ]);
        Student::create([
            'StudentID'     => '6910101',
            'UserID'        => $u1->UserID,
            'FirstName'     => 'นักเรียน',
            'LastName'      => 'หนึ่ง',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => '1/1',
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        $u2 = User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => '6910102',
            'CitizenID' => '2222222222222',
            'FirstName' => 'นักเรียน',
            'LastName'  => 'สอง',
            'Password'  => bcrypt('password'),
            'Role'      => 'นักเรียน',
            'Status'    => 'ปกติ',
        ]);
        Student::create([
            'StudentID'     => '6910102',
            'UserID'        => $u2->UserID,
            'FirstName'     => 'นักเรียน',
            'LastName'      => 'สอง',
            'GradeLevel'    => 'ม.1',
            'Classroom'     => '1/1',
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        $this->artisan('students:migrate-ids')
            ->assertExitCode(0);

        $this->assertDatabaseHas('students', ['StudentID' => '06000']);
        $this->assertDatabaseHas('students', ['StudentID' => '06001']);
        $this->assertDatabaseMissing('students', ['StudentID' => '6910101']);
        $this->assertDatabaseMissing('students', ['StudentID' => '6910102']);

        $this->assertDatabaseHas('users', ['Username' => '06000']);
        $this->assertDatabaseHas('users', ['Username' => '06001']);
    }
}
