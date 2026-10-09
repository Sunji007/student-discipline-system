<?php

namespace Tests\Feature;

use App\Models\InformantReport;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InformantReportSuspectTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('suspectInputs')]
    public function test_supported_suspect_information_can_be_saved_and_displayed(?string $input, array $expectedIds): void
    {
        Storage::fake('public');
        $reporter = User::factory()->create(['Role' => 'นักเรียน']);
        foreach (['06001', '06002'] as $id) {
            $this->createStudent($id);
        }

        $this->actingAs($reporter)->post(route('student.informant-reports.store'), $this->reportData($input))
            ->assertRedirect(route('student.informant-reports.index'));

        $report = InformantReport::firstOrFail();
        $this->assertSame($input, $report->StudentID);
        $this->assertSame($expectedIds, $report->involved_students->pluck('StudentID')->sort()->values()->all());
        Storage::disk('public')->assertExists($report->evidence_paths);
        $this->get(route('student.informant-reports.index'))->assertOk();

        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $response = $this->actingAs($admin)->get(route('discipline.informant-reports.show', $report->ReportID))->assertOk();
        if (empty($expectedIds) && $input !== null) {
            $response->assertSee($input);
        }
        foreach ($expectedIds as $id) {
            $response->assertSee($id);
        }
    }

    public static function suspectInputs(): array
    {
        return [
            'physical description' => ['ตัวสูง ผิวสองสี เสื้อ ม.ปลาย', []],
            'long description' => [trim(str_repeat('ลักษณะเด่น ', 40)), []],
            'multiple existing IDs' => ['06001,06002', ['06001', '06002']],
            'spaces and semicolons' => ['06001; 06002', ['06001', '06002']],
            'single existing ID' => ['06001', ['06001']],
            'unknown ID kept as supplied information' => ['99999', []],
            'no suspect information' => [null, []],
        ];
    }

    public function test_student_still_cannot_include_own_id_in_a_report(): void
    {
        Storage::fake('public');
        $reporter = $this->createStudent('06000');
        $this->actingAs($reporter)->post(route('student.informant-reports.store'), $this->reportData('06001,06000'))
            ->assertSessionHasErrors('StudentID');
        $this->assertDatabaseCount('informant_reports', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_migration_preserves_legacy_reports_and_the_reporter_foreign_key(): void
    {
        // Rebuild the original single-student constraint, then upgrade a populated table.
        Schema::table('informant_reports', function ($table) {
            $table->string('StudentID', 10)->nullable()->change();
            $table->foreign('StudentID')->references('StudentID')->on('students')->onDelete('set null');
        });
        $student = $this->createStudent('06001');
        $report = InformantReport::create([
            'StudentID' => '06001', 'ReporterID' => $student->UserID, 'Description' => 'Legacy report',
            'EvidencePath' => 'legacy/evidence.png',
        ]);

        $migration = require database_path('migrations/2026_10_09_000001_allow_suspect_details_in_informant_reports.php');
        $migration->up();

        $this->assertSame('06001', $report->fresh()->StudentID);
        $this->assertSame('legacy/evidence.png', $report->fresh()->EvidencePath);
        $foreignColumns = array_column(Schema::getForeignKeys('informant_reports'), 'columns');
        $this->assertContains(['ReporterID'], $foreignColumns);
        $this->assertNotContains(['StudentID'], $foreignColumns);
        $report->update(['StudentID' => '06001,99999']);
        $this->assertSame('06001,99999', $report->fresh()->StudentID);
    }

    private function createStudent(string $id): User
    {
        $user = User::factory()->create(['Role' => 'นักเรียน']);
        Student::create(['StudentID' => $id, 'UserID' => $user->UserID, 'FirstName' => 'Test', 'LastName' => 'Student']);
        return $user;
    }

    private function reportData(?string $input): array
    {
        return [
            'Title' => 'Incident', 'Category' => 'Test', 'Description' => 'Incident details',
            'StudentID' => $input, 'AcknowledgeTruth' => '1',
            'evidence' => [UploadedFile::fake()->createWithContent('evidence.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='
            ))],
        ];
    }
}
