<?php

ini_set('display_errors', '0');
$expectedTokenHash = '__TOKEN_HASH__';
$expiresAt = '__EXPIRES_AT__';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
    || !ctype_xdigit($expectedTokenHash) || strlen($expectedTokenHash) !== 64
    || !ctype_digit($expiresAt) || time() > (int) $expiresAt
    || !hash_equals($expectedTokenHash, hash('sha256', $_SERVER['HTTP_X_CODEX_MIGRATION_TOKEN'] ?? ''))) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/json');
$phase = 'bootstrap';
try {
    $appRoot = dirname(__DIR__, 2) . '/private/student-discipline-system';
    require $appRoot . '/vendor/autoload.php';
    $app = require $appRoot . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $phase = 'cache_refresh';
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    // Keep verification sessions in memory; never log in or change a real account.
    config(['session.driver' => 'array']);

    $phase = 'existing_evidence_lookup';
    $filename = '8NOsb6O1mj9s3QoGG9SyzbNji1MGXZxPf1GtR3Bh.jpg';
    $report = \App\Models\InformantReport::where('EvidencePath', 'like', '%' . $filename . '%')->firstOrFail();
    $index = null;
    foreach ($report->evidence_paths as $key => $path) {
        if (basename($path) === $filename) {
            $index = $key;
            break;
        }
    }
    if ($index === null) {
        throw new RuntimeException('Evidence is not attached to this report.');
    }
    $staff = \App\Models\User::where('Role', 'ผู้ดูแลระบบ')->firstOrFail();
    \Illuminate\Support\Facades\Auth::guard('web')->setUser($staff);

    $phase = 'protected_route_verification';
    $url = route('discipline.informant-reports.evidence', [$report->ReportID, $index]);
    $request = \Illuminate\Http\Request::create($url, 'GET');
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    if ($response->getStatusCode() !== 200 || !$response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
        throw new RuntimeException('Evidence route did not return the original file.');
    }
    $expectedFile = storage_path('app/public/informant_reports/evidence/' . $filename);
    if (!hash_equals(hash_file('sha256', $expectedFile), hash_file('sha256', $response->getFile()->getPathname()))) {
        throw new RuntimeException('Evidence contents changed.');
    }
    $phase = 'view_link_verification';
    $html = $app->make(\App\Http\Controllers\Discipline\InformantReportController::class)->show($report)->render();
    if (!str_contains($html, htmlspecialchars($url, ENT_QUOTES, 'UTF-8'))) {
        throw new RuntimeException('The report view did not use the protected route.');
    }
    echo json_encode([
        'ok' => true, 'evidence_http_status' => 200, 'original_image_unchanged' => true,
        'new_view_link_verified' => true, 'evidence_url' => $url,
    ]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'phase' => $phase, 'error_type' => get_class($exception)]);
}
