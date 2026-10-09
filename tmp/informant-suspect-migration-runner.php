<?php

// Temporary deployment template. The token, migration hash and expiry are filled in memory.
ini_set('display_errors', '0');
$expectedTokenHash = '__TOKEN_HASH__';
$expectedMigrationHash = '__MIGRATION_HASH__';
$expiresAt = '__EXPIRES_AT__';
$token = $_SERVER['HTTP_X_CODEX_MIGRATION_TOKEN'] ?? '';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
    || !ctype_xdigit($expectedTokenHash) || strlen($expectedTokenHash) !== 64
    || !ctype_digit($expiresAt) || time() > (int) $expiresAt
    || !hash_equals($expectedTokenHash, hash('sha256', $token))) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/json');
$phase = 'bootstrap';
try {
    // Same application location as the website's index.php.
    $appRoot = dirname(__DIR__, 2) . '/private/student-discipline-system';
    require $appRoot . '/vendor/autoload.php';
    $app = require $appRoot . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $phase = 'preflight';
    $relativeMigration = 'database/migrations/2026_10_09_000001_allow_suspect_details_in_informant_reports.php';
    if (!hash_equals($expectedMigrationHash, hash_file('sha256', $appRoot . '/' . $relativeMigration))) {
        throw new RuntimeException('Migration content does not match the tested version.');
    }
    $engine = \Illuminate\Support\Facades\DB::scalar(
        "SELECT ENGINE FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'informant_reports'"
    );
    if (strtoupper((string) $engine) !== 'INNODB') {
        throw new RuntimeException('Transactional storage is required for rollback-only verification.');
    }

    $beforeCount = \Illuminate\Support\Facades\DB::table('informant_reports')->count();
    $beforeType = \Illuminate\Support\Facades\Schema::getColumnType('informant_reports', 'StudentID');
    $beforeForeigns = \Illuminate\Support\Facades\Schema::getForeignKeys('informant_reports');

    $phase = 'migration';
    $exitCode = \Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => $relativeMigration, '--force' => true]);
    if ($exitCode !== 0) {
        throw new RuntimeException('The targeted migration did not complete.');
    }

    $phase = 'schema_verification';
    $afterType = \Illuminate\Support\Facades\Schema::getColumnType('informant_reports', 'StudentID');
    if ($afterType !== 'text') {
        throw new RuntimeException('Suspect information is not stored as text.');
    }
    $afterForeigns = \Illuminate\Support\Facades\Schema::getForeignKeys('informant_reports');
    foreach ($afterForeigns as $foreign) {
        if ($foreign['columns'] === ['StudentID']) {
            throw new RuntimeException('The single-student constraint remains.');
        }
    }
    foreach ($beforeForeigns as $foreign) {
        if ($foreign['columns'] !== ['StudentID'] && !in_array($foreign, $afterForeigns, true)) {
            throw new RuntimeException('An unrelated foreign key changed.');
        }
    }
    if (\Illuminate\Support\Facades\DB::table('informant_reports')->count() < $beforeCount) {
        throw new RuntimeException('Existing report count decreased.');
    }

    $phase = 'rollback_only_write_verification';
    $probeIds = [];
    $probeValues = ['ตัวสูง ผิวสองสี เสื้อ ม.ปลาย', '06001,06002', str_repeat('ลักษณะเด่น ', 40)];
    \Illuminate\Support\Facades\DB::beginTransaction();
    try {
        foreach ($probeValues as $value) {
            $id = (string) \Illuminate\Support\Str::uuid();
            $probeIds[] = $id;
            \Illuminate\Support\Facades\DB::table('informant_reports')->insert([
                'ReportID' => $id, 'Description' => 'Temporary migration verification', 'StudentID' => $value,
                'Status' => 'เรื่องใหม่', 'ReportDate' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            if (\Illuminate\Support\Facades\DB::table('informant_reports')->where('ReportID', $id)->value('StudentID') !== $value) {
                throw new RuntimeException('Suspect information was truncated or changed.');
            }
        }
    } finally {
        \Illuminate\Support\Facades\DB::rollBack();
    }
    if (\Illuminate\Support\Facades\DB::table('informant_reports')->whereIn('ReportID', $probeIds)->exists()) {
        throw new RuntimeException('Verification rows were not rolled back.');
    }

    echo json_encode([
        'ok' => true, 'column_type_before' => $beforeType, 'column_type_after' => $afterType,
        'existing_reports_preserved' => true, 'unrelated_foreign_keys_preserved' => true,
        'write_probes_passed' => count($probeValues), 'probe_rows_rolled_back' => true,
    ]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'phase' => $phase, 'error_type' => get_class($exception)]);
}
