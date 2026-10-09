<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invalidPhones')]
    public function test_phone_check_rejects_invalid_numbers(string $phone): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);

        $this->actingAs($admin)->postJson(route('admin.users.check-phone'), ['Phone' => $phone])
            ->assertUnprocessable()->assertJsonValidationErrors('Phone');
    }

    public static function invalidPhones(): array
    {
        return [
            'all zeroes' => ['000-000-0000'],
            'all ones' => ['111-111-1111'],
            'all nines' => ['9999999999'],
            'sequential wrong prefix' => ['012-345-6789'],
            'missing leading zero' => ['8812345678'],
            'wrong prefix' => ['031-234-5678'],
            'too short' => ['081234567'],
            'too long' => ['08123456789'],
            'embedded letters' => ['abc0812345678'],
            'trailing letter' => ['081-234-567x'],
            'mixed separators' => ['081--2345678'],
        ];
    }

    #[DataProvider('validPhones')]
    public function test_phone_check_accepts_supported_mobile_formats(string $phone): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);

        $this->actingAs($admin)->postJson(route('admin.users.check-phone'), ['Phone' => $phone])
            ->assertOk()->assertJsonPath('exists', false);
    }

    public static function validPhones(): array
    {
        return [
            '06 prefix' => ['0612345678'],
            '08 prefix' => ['0812345678'],
            '09 prefix' => ['0912345678'],
            'formatted' => ['081-234-5678'],
            'repeated suffix is not proof of a fake number' => ['099-999-9999'],
        ];
    }

    public function test_duplicate_phone_is_detected_in_both_input_formats(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        User::factory()->create(['Phone' => '081-234-5678']);

        foreach (['0812345678', '081-234-5678'] as $phone) {
            $this->actingAs($admin)->postJson(route('admin.users.check-phone'), ['Phone' => $phone])
                ->assertOk()->assertJsonPath('exists', true);
        }
    }

    public function test_duplicate_legacy_formatted_phone_is_detected_for_raw_input(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $user = User::factory()->create();
        DB::table('users')->where('UserID', $user->UserID)->update(['Phone' => '081-234-5678']);

        $this->actingAs($admin)->postJson(route('admin.users.check-phone'), ['Phone' => '0812345678'])
            ->assertOk()->assertJsonPath('exists', true);
    }

    public function test_editing_own_phone_does_not_report_a_duplicate(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ', 'Phone' => '0812345678']);

        $this->actingAs($admin)->postJson(route('admin.users.check-phone'), [
            'Phone' => '081-234-5678', 'UserID' => $admin->UserID,
        ])->assertOk()->assertJsonPath('exists', false);
    }

    public function test_user_create_and_update_validate_phone_without_javascript(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $user = User::factory()->create();

        // Other required fields are omitted to avoid the external email DNS check.
        // The Phone-specific error must still be present on both write endpoints.
        $this->actingAs($admin)->postJson(route('admin.users.store'), ['Phone' => '111-111-1111'])
            ->assertUnprocessable()->assertJsonValidationErrors('Phone');
        $this->actingAs($admin)->putJson(route('admin.users.update', $user->UserID), ['Phone' => '111-111-1111'])
            ->assertUnprocessable()->assertJsonValidationErrors('Phone');
    }

    public function test_import_rejects_an_invalid_phone_before_saving_any_rows(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);

        $this->actingAs($admin)->postJson(route('admin.students.import.store'), [
            'mode' => 'insert_only',
            'students' => [[
                'student_id' => '06000', 'first_name' => 'Test', 'last_name' => 'Student',
                'grade' => 'ม.1', 'room' => '1/1', 'phone' => '0000000000',
            ]],
        ])->assertUnprocessable()->assertJsonValidationErrors('students.0.phone');

        $this->assertDatabaseMissing('users', ['Username' => '06000']);
    }

    public function test_user_writes_reject_duplicate_legacy_phone_without_javascript(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $user = User::factory()->create();
        $owner = User::factory()->create();
        DB::table('users')->where('UserID', $owner->UserID)->update(['Phone' => '081-234-5678']);

        $this->actingAs($admin)->postJson(route('admin.users.store'), ['Phone' => '0812345678'])
            ->assertUnprocessable()->assertJsonValidationErrors('Phone');
        $this->actingAs($admin)->putJson(route('admin.users.update', $user->UserID), ['Phone' => '0812345678'])
            ->assertUnprocessable()->assertJsonValidationErrors('Phone');
    }

    public function test_import_rejects_phone_already_used_by_another_account(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        User::factory()->create(['Phone' => '0812345678']);

        $this->actingAs($admin)->postJson(route('admin.students.import.store'), [
            'mode' => 'insert_only', 'students' => [$this->importRow('06000', '081-234-5678')],
        ])->assertUnprocessable()->assertJsonValidationErrors('students.0.phone');

        $this->assertDatabaseMissing('users', ['Username' => '06000']);
    }

    public function test_duplicate_phones_within_import_roll_back_all_rows(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);

        $this->actingAs($admin)->postJson(route('admin.students.import.store'), [
            'mode' => 'insert_only', 'students' => [
                $this->importRow('06000', '0812345678'),
                $this->importRow('06001', '081-234-5678'),
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors('students.1.phone');

        $this->assertDatabaseMissing('users', ['Username' => '06000']);
        $this->assertDatabaseMissing('students', ['StudentID' => '06000']);
        $this->assertDatabaseMissing('users', ['Username' => '06001']);
    }

    public function test_import_allows_existing_student_to_keep_own_phone(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $user = User::factory()->create(['Role' => 'นักเรียน', 'Phone' => '0812345678']);
        Student::create(['StudentID' => '06000', 'UserID' => $user->UserID, 'FirstName' => 'Test', 'LastName' => 'Student']);

        $this->actingAs($admin)->postJson(route('admin.students.import.store'), [
            'mode' => 'update_only', 'students' => [$this->importRow('06000', '081-234-5678')],
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', ['UserID' => $user->UserID, 'Phone' => '0812345678']);
    }

    public function test_parent_form_validates_phone_on_the_server(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $user = User::factory()->create(['Role' => 'นักเรียน']);
        $student = Student::create(['StudentID' => '06000', 'UserID' => $user->UserID, 'FirstName' => 'Test', 'LastName' => 'Student']);

        $this->actingAs($admin)->postJson(route('admin.students.parents.store', $student->StudentID), ['Phone' => '000-000-0000'])
            ->assertUnprocessable()->assertJsonValidationErrors('Phone');
    }

    private function importRow(string $studentId, string $phone): array
    {
        return [
            'student_id' => $studentId, 'first_name' => 'Test', 'last_name' => 'Student',
            'grade' => 'ม.1', 'room' => '1/1', 'phone' => $phone,
        ];
    }
}
