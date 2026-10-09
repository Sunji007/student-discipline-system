<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherIdLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_log_in_using_teacher_id_and_existing_password(): void
    {
        $teacher = $this->teacher();
        $this->postJson('/login', ['Username' => '0540201', 'Password' => 'TeacherTest123!'])
            ->assertOk()->assertJsonPath('redirect', route('teacher.dashboard'));
        $this->assertAuthenticatedAs($teacher);
        $this->assertSame('somchai1234', $teacher->fresh()->Username);
    }

    public function test_existing_teacher_username_still_works(): void
    {
        $teacher = $this->teacher();
        $this->postJson('/login', ['Username' => 'somchai1234', 'Password' => 'TeacherTest123!'])->assertOk();
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_wrong_password_for_teacher_id_is_rejected(): void
    {
        $this->teacher();
        $this->postJson('/login', ['Username' => '0540201', 'Password' => 'WrongPassword123!'])
            ->assertUnprocessable()->assertJsonValidationErrors('Password');
        $this->assertGuest();
    }

    public function test_suspended_teacher_cannot_log_in_using_teacher_id(): void
    {
        $this->teacher(['Status' => 'ระงับการใช้งาน']);
        $this->postJson('/login', ['Username' => '0540201', 'Password' => 'TeacherTest123!'])
            ->assertUnprocessable()->assertJsonValidationErrors('Username')
            ->assertJsonPath('errors.Username', 'บัญชีผู้ใช้งานนี้ถูกระงับการใช้งาน');
        $this->assertGuest();
    }

    public function test_existing_username_has_priority_over_a_conflicting_teacher_id(): void
    {
        $this->teacher();
        $existing = User::factory()->create(['Username' => '0540201', 'Password' => 'OtherAccount123!', 'Role' => 'ผู้ดูแลระบบ']);
        $this->postJson('/login', ['Username' => '0540201', 'Password' => 'TeacherTest123!'])->assertUnprocessable();
        $this->assertGuest();
        $this->postJson('/login', ['Username' => '0540201', 'Password' => 'OtherAccount123!'])->assertOk();
        $this->assertAuthenticatedAs($existing);
    }

    public function test_teacher_id_preserves_role_selection_for_users_with_multiple_roles(): void
    {
        $teacher = $this->teacher(['Role' => 'ฝ่ายปกครอง']);
        $this->postJson('/login', ['Username' => '0540201', 'Password' => 'TeacherTest123!'])
            ->assertOk()->assertJsonPath('redirect', route('select-role'));
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_login_form_explains_teacher_id_is_supported(): void
    {
        $this->get('/login')->assertOk()->assertSee('กรอก Username รหัสนักเรียน หรือรหัสครู');
    }

    private function teacher(array $attributes = []): User
    {
        $user = User::factory()->create($attributes + [
            'Username' => 'somchai1234', 'Role' => 'ครู', 'Password' => 'TeacherTest123!', 'Status' => 'ปกติ',
        ]);
        Teacher::create(['TeacherID' => '0540201', 'UserID' => $user->UserID]);
        return $user;
    }
}
