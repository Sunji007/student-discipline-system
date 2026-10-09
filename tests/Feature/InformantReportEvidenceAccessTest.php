<?php

namespace Tests\Feature;

use App\Models\InformantReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class InformantReportEvidenceAccessTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('storedFormats')]
    public function test_each_stored_evidence_file_can_be_opened_without_a_storage_link(string $format): void
    {
        Storage::fake('public');
        $paths = ['informant_reports/evidence/one.png', 'informant_reports/evidence/two.png'];
        foreach ($paths as $index => $path) {
            Storage::disk('public')->put($path, 'evidence-' . $index);
        }
        $stored = match ($format) {
            'single' => $paths[0], 'json' => json_encode($paths), 'pipe' => implode('|', $paths),
        };
        $report = InformantReport::create(['Description' => 'Report details', 'EvidencePath' => $stored]);
        \App\Models\RolePermission::updateOrCreate(['Role' => 'ฝ่ายปกครอง', 'ModuleName' => 'informant-reports'], [
            'PermissionID' => (string) \Illuminate\Support\Str::uuid(), 'CanAccess' => true,
        ]);
        User::flushPermissionsCache();
        \Illuminate\Support\Facades\Cache::forget('role_permissions_' . md5('ฝ่ายปกครอง'));
        $this->actingAs(User::factory()->create(['Role' => 'ฝ่ายปกครอง']));

        foreach ($format === 'single' ? [0] : [0, 1] as $index) {
            $url = route('discipline.informant-reports.evidence', [$report->ReportID, $index]);
            $response = $this->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
            $this->assertSame('evidence-' . $index, file_get_contents($response->baseResponse->getFile()->getPathname()));
            $this->get(route('discipline.informant-reports.show', $report->ReportID))->assertSee($url);
        }
    }

    public static function storedFormats(): array
    {
        return ['single' => ['single'], 'json' => ['json'], 'pipe' => ['pipe']];
    }

    public function test_legacy_uploads_and_pdf_evidence_remain_accessible(): void
    {
        Storage::fake('public');
        $this->app->usePublicPath(Storage::disk('public')->path('legacy-public'));
        $file = public_path('uploads/informant-evidence/legacy.pdf');
        mkdir(dirname($file), 0777, true);
        file_put_contents($file, '%PDF-1.4 legacy');
        $report = InformantReport::create(['Description' => 'Legacy report', 'EvidencePath' => 'uploads/informant-evidence/legacy.pdf']);
        $this->actingAs(User::factory()->create(['Role' => 'ผู้ดูแลระบบ']));
        $response = $this->get(route('discipline.informant-reports.evidence', [$report->ReportID, 0]))->assertOk();
        $this->assertSame(realpath($file), $response->baseResponse->getFile()->getRealPath());
    }

    public function test_missing_files_or_indexes_return_not_found(): void
    {
        Storage::fake('public');
        $report = InformantReport::create(['Description' => 'Missing evidence', 'EvidencePath' => 'informant_reports/evidence/missing.jpg']);
        $this->actingAs(User::factory()->create(['Role' => 'ผู้ดูแลระบบ']));
        foreach ([0, 1] as $index) {
            $this->get(route('discipline.informant-reports.evidence', [$report->ReportID, $index]))->assertNotFound();
        }
    }

    public function test_evidence_requires_authorized_staff(): void
    {
        $report = InformantReport::create(['Description' => 'Private report', 'EvidencePath' => 'informant_reports/evidence/private.jpg']);
        $url = route('discipline.informant-reports.evidence', [$report->ReportID, 0]);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['Role' => 'นักเรียน']))->get($url)->assertRedirect(route('home'));
        \App\Models\RolePermission::where('Role', 'ฝ่ายปกครอง')->where('ModuleName', 'informant-reports')->update(['CanAccess' => false]);
        User::flushPermissionsCache();
        \Illuminate\Support\Facades\Cache::forget('role_permissions_' . md5('ฝ่ายปกครอง'));
        $this->actingAs(User::factory()->create(['Role' => 'ฝ่ายปกครอง']))->get($url)->assertForbidden();
    }

    public function test_a_saved_path_cannot_read_outside_the_evidence_storage_root(): void
    {
        Storage::fake('public');
        $root = Storage::disk('public')->path('');
        $outside = dirname(rtrim($root, '/\\')) . '/public-sibling';
        if (!is_dir($outside)) {
            mkdir($outside, 0777, true);
        }
        file_put_contents($outside . '/secret.png', 'outside storage');
        try {
            $report = InformantReport::create(['Description' => 'Invalid path', 'EvidencePath' => '../public-sibling/secret.png']);
            $this->actingAs(User::factory()->create(['Role' => 'ผู้ดูแลระบบ']))
                ->get(route('discipline.informant-reports.evidence', [$report->ReportID, 0]))->assertNotFound();
        } finally {
            unlink($outside . '/secret.png');
            rmdir($outside);
        }
    }
}
