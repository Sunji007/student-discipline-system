<?php

namespace Tests\Feature;

use App\Models\InformantReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InformantReportEvidenceTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('missingEvidence')]
    public function test_student_cannot_submit_without_an_image(array $extra, string $error): void
    {
        $student = User::factory()->create(['Role' => 'นักเรียน']);

        $this->actingAs($student)->postJson(route('student.informant-reports.store'), $this->reportData() + $extra)
            ->assertUnprocessable()->assertJsonValidationErrors($error);

        $this->assertDatabaseCount('informant_reports', 0);
    }

    public static function missingEvidence(): array
    {
        return [
            'missing field' => [[], 'evidence'],
            'empty list' => [['evidence' => []], 'evidence'],
            'empty item' => [['evidence' => [null]], 'evidence.0'],
            'text instead of file list' => [['evidence' => 'not an image'], 'evidence'],
        ];
    }

    public function test_student_cannot_submit_a_document_or_disguised_image(): void
    {
        $student = User::factory()->create(['Role' => 'นักเรียน']);

        foreach (['document.pdf', 'fake.png'] as $name) {
            $file = UploadedFile::fake()->createWithContent($name, 'This is text, not an image.');
            // Use a real test upload so MIME detection inspects the contents;
            // Laravel's fake upload otherwise reports MIME based on the filename.
            $upload = new UploadedFile($file->getPathname(), $name, null, null, true);
            $this->actingAs($student)->postJson(route('student.informant-reports.store'), $this->reportData() + [
                'evidence' => [$upload],
            ])->assertUnprocessable()->assertJsonValidationErrors('evidence.0');
        }

        $this->assertDatabaseCount('informant_reports', 0);
    }

    #[DataProvider('imageCounts')]
    public function test_student_can_submit_with_images_and_they_are_saved(int $count): void
    {
        Storage::fake('public');
        $student = User::factory()->create(['Role' => 'นักเรียน']);

        $this->actingAs($student)->post(route('student.informant-reports.store'), $this->reportData() + [
            'evidence' => array_map(fn ($number) => $this->image("image-$number.png"), range(1, $count)),
        ])->assertRedirect(route('student.informant-reports.index'));

        $this->assertDatabaseCount('informant_reports', 1);
        $paths = InformantReport::first()->EvidencePaths;
        $this->assertCount($count, $paths);
        Storage::disk('public')->assertExists($paths);
    }

    public static function imageCounts(): array
    {
        return ['single image' => [1], 'multiple images' => [2]];
    }

    public function test_student_cannot_submit_images_over_the_size_limit(): void
    {
        $student = User::factory()->create(['Role' => 'นักเรียน']);
        $this->actingAs($student)->postJson(route('student.informant-reports.store'), $this->reportData() + [
            'evidence' => [UploadedFile::fake()->create('large.png', 20481, 'image/png')],
        ])->assertUnprocessable()->assertJsonValidationErrors('evidence.0');
        $this->assertDatabaseCount('informant_reports', 0);
    }

    public function test_failed_image_storage_does_not_create_a_report_without_evidence(): void
    {
        $student = User::factory()->create(['Role' => 'นักเรียน']);
        $disk = \Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('putFileAs')->once()->andReturn(false);
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);

        $this->actingAs($student)->postJson(route('student.informant-reports.store'), $this->reportData() + [
            'evidence' => [$this->image('evidence.png')],
        ])->assertUnprocessable()->assertJsonValidationErrors('evidence');

        $this->assertDatabaseCount('informant_reports', 0);
    }

    public function test_student_form_marks_image_as_required(): void
    {
        $student = User::factory()->create(['Role' => 'นักเรียน']);
        $response = $this->actingAs($student)->get(route('student.informant-reports.create'))->assertOk();
        $document = new \DOMDocument();
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        $input = (new \DOMXPath($document))->query('//input[@id="evidence"]')->item(0);

        $this->assertInstanceOf(\DOMElement::class, $input);
        $this->assertTrue($input->hasAttribute('required'));
    }

    private function image(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='
        ));
    }

    private function reportData(): array
    {
        return [
            'Title' => 'Test report', 'Category' => 'Test', 'Description' => 'Test incident details',
            'AcknowledgeTruth' => '1', 'IsAnonymous' => '1',
        ];
    }
}
