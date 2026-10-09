<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UserController;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdditionalBugReviewTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('suspectDescriptions')]
    public function test_report_accepts_suspect_information_allowed_by_the_form(string $suspect): void
    {
        Storage::fake('public');
        $reporter = User::factory()->create(['Role' => 'นักเรียน']);
        foreach (['06001', '06002'] as $id) {
            $user = User::factory()->create(['Role' => 'นักเรียน']);
            Student::create(['StudentID' => $id, 'UserID' => $user->UserID, 'FirstName' => 'Test', 'LastName' => 'Student']);
        }
        $image = UploadedFile::fake()->createWithContent('evidence.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='
        ));

        $response = $this->actingAs($reporter)->post(route('student.informant-reports.store'), [
            'Title' => 'Incident', 'Category' => 'Test', 'Description' => 'Incident details',
            'StudentID' => $suspect, 'AcknowledgeTruth' => '1', 'evidence' => [$image],
        ]);
        $this->assertSame(302, $response->getStatusCode(), 'The form supports this input, but submission returns HTTP ' . $response->getStatusCode());
        $this->assertDatabaseCount('informant_reports', 1);
    }

    public static function suspectDescriptions(): array
    {
        return [
            'physical description' => ['ตัวสูง ผิวสองสี เสื้อ ม.ปลาย'],
            'multiple existing student IDs' => ['06001,06002'],
        ];
    }

    public function test_importing_student_details_does_not_unsuspend_the_account(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $user = User::factory()->create(['Role' => 'นักเรียน', 'Status' => 'ระงับการใช้งาน']);
        Student::create(['StudentID' => '06000', 'UserID' => $user->UserID, 'FirstName' => 'Before', 'LastName' => 'Student']);

        $this->actingAs($admin)->postJson(route('admin.students.import.store'), [
            'mode' => 'update_only', 'students' => [[
                'student_id' => '06000', 'first_name' => 'Updated', 'last_name' => 'Student',
                'grade' => 'ม.1', 'room' => '1/1',
            ]],
        ])->assertOk();
        $this->assertSame('ระงับการใช้งาน', $user->fresh()->Status);
    }

    public function test_clearing_optional_phone_actually_removes_the_phone(): void
    {
        $user = User::factory()->create(['Role' => 'ผู้ดูแลระบบ', 'Phone' => '0812345678', 'CitizenID' => '1234567899012']);
        $data = [
            'FirstName' => 'Test', 'LastName' => 'Admin', 'FirstName_EN' => 'Test', 'LastName_EN' => 'Admin',
            'CitizenID' => $user->CitizenID, 'Role' => 'ผู้ดูแลระบบ', 'Status' => 'ปกติ',
            'Phone' => null, 'Email' => $user->Email,
        ];
        $request = \Mockery::mock(Request::class)->makePartial();
        $request->initialize([], $data);
        $request->setMethod('PUT');
        $request->shouldReceive('validate')->once()->andReturnUsing(function ($rules, $messages) use ($request) {
            unset($rules['Email']);
            return Validator::make($request->all(), $rules, $messages)->validate() + ['Email' => $request->input('Email')];
        });

        (new UserController)->update($request, $user);
        $this->assertNull($user->fresh()->getRawOriginal('Phone'));
    }
}
