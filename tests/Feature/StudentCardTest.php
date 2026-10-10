<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCardTest extends TestCase
{
    use RefreshDatabase;

    private function createStudent(string $id): Student
    {
        $user = User::factory()->create(['Role' => 'นักเรียน']);

        return Student::create([
            'StudentID' => $id,
            'UserID' => $user->UserID,
            'FirstName' => 'นักเรียน',
            'LastName' => $id,
            'GradeLevel' => 'ม.1',
            'Classroom' => '1',
        ]);
    }

    public function test_student_can_show_their_card_with_a_leading_zero_id(): void
    {
        $student = $this->createStudent('06000');

        $this->actingAs($student->user)->get(route('student.card'))
            ->assertOk()
            ->assertViewIs('students.card')
            ->assertViewHas('student', fn ($cardStudent) => $cardStudent->StudentID === '06000')
            ->assertViewHas('backUrl', route('student.dashboard'))
            ->assertSee('บาร์โค้ดรหัสนักเรียน 06000')
            ->assertSee('คิวอาร์โค้ดรหัสนักเรียน 06000')
            ->assertSee("'06000'", false)
            ->assertSee(asset('js/vendor/JsBarcode.all.min.js'), false)
            ->assertSee(asset('js/vendor/qrious.min.js'), false);
    }

    public function test_student_cannot_select_another_students_card(): void
    {
        $student = $this->createStudent('06000');
        $otherStudent = $this->createStudent('06001');

        $this->actingAs($student->user)
            ->get(route('student.card', ['student_id' => $otherStudent->StudentID]))
            ->assertOk()
            ->assertViewHas('student', fn ($cardStudent) => $cardStudent->StudentID === $student->StudentID)
            ->assertDontSee($otherStudent->FullName);
    }

    public function test_guest_must_sign_in_to_show_a_card(): void
    {
        $this->get(route('student.card'))->assertRedirect(route('login'));
    }

    public function test_teacher_cannot_open_the_student_card_route(): void
    {
        $teacher = User::factory()->create(['Role' => 'ครู']);

        $this->actingAs($teacher)->get(route('student.card'))->assertRedirect(route('home'));
    }

    public function test_missing_student_profile_returns_not_found(): void
    {
        $user = User::factory()->create(['Role' => 'นักเรียน']);

        $this->actingAs($user)->get(route('student.card'))->assertNotFound();
    }

    public function test_admin_can_still_print_a_student_card(): void
    {
        $student = $this->createStudent('06000');
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);

        $this->actingAs($admin)->get(route('admin.students.card', $student->StudentID))
            ->assertOk()
            ->assertViewIs('students.card')
            ->assertViewHas('backUrl', route('admin.students.index'))
            ->assertSee('บาร์โค้ดรหัสนักเรียน 06000')
            ->assertSee('คิวอาร์โค้ดรหัสนักเรียน 06000');
    }
}
