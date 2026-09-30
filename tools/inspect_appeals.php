<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appeal;
use App\Models\Student;
use App\Models\BehaviorRecord;
use Illuminate\Support\Facades\DB;

echo "=== APPEALS INSPECTION ===\n";
$totalAppeals = Appeal::count();
echo "Total Appeals: {$totalAppeals}\n";

$appeals = Appeal::with(['behaviorRecord.rule', 'student'])->get();
foreach ($appeals as $idx => $a) {
    $rule = $a->behaviorRecord?->rule;
    $ruleType = $rule?->RuleType ?? 'N/A';
    $ruleMod = $rule?->ScoreModifier ?? 'N/A';
    $ruleName = $rule?->RuleName ?? 'N/A';
    $restored = var_export($a->RestoredPoints, true);
    echo "#" . ($idx + 1) . " AppealID: {$a->AppealID}\n";
    echo "   StudentID: {$a->StudentID} | Status: {$a->Status}\n";
    echo "   RestoredPoints: {$restored}\n";
    echo "   RecordID: {$a->RecordID} | Rule: [{$ruleType}] {$ruleName} (Modifier: {$ruleMod})\n";
    echo "   Record Status: " . ($a->behaviorRecord?->Status ?? 'N/A') . "\n";
    echo "   Record semester_id: " . ($a->behaviorRecord?->semester_id ?? 'NULL') . "\n";
    echo "   ----------------------------------------\n";
}

echo "\n=== MIGRATIONS TABLE CHECK ===\n";
$migrations = DB::table('migrations')->orderBy('id', 'asc')->get();
echo "Total migrations recorded: " . $migrations->count() . "\n";
$lastMigrations = $migrations->slice(-5);
foreach ($lastMigrations as $m) {
    echo "   {$m->migration} (batch: {$m->batch})\n";
}

echo "\n=== STUDENTS SCORE CHECK ===\n";
$totalStudents = Student::count();
echo "Total Students: {$totalStudents}\n";
$activeSemester = DB::table('semesters')->where('is_active', true)->first();
$activeSemId = $activeSemester ? $activeSemester->semester_id : null;
echo "Active Semester: " . ($activeSemester ? "{$activeSemester->term}/{$activeSemester->academic_year} (ID: {$activeSemId})" : 'None') . "\n";

$mismatchCount = 0;
$noRecordMismatch = 0;
$withRecordMismatch = 0;

foreach (Student::all() as $st) {
    $calcScore = $st->getBehaviorScoreForSemester($activeSemId);
    if ((float)$st->BehaviorScore !== (float)$calcScore) {
        $mismatchCount++;
        $hasRecords = DB::table('behavior_records')->where('StudentID', $st->StudentID)->exists();
        if ($hasRecords) {
            $withRecordMismatch++;
        } else {
            $noRecordMismatch++;
        }
    }
}
echo "Mismatch count: {$mismatchCount} / {$totalStudents}\n";
echo "   - Mismatch with NO behavior records: {$noRecordMismatch}\n";
echo "   - Mismatch WITH behavior records: {$withRecordMismatch}\n";

$sampleStudent = Student::find('6920101');
if ($sampleStudent) {
    echo "\nSample Student 6920101:\n";
    echo "DB Score: {$sampleStudent->BehaviorScore}\n";
    echo "Calculated Score: " . $sampleStudent->getBehaviorScoreForSemester($activeSemId) . "\n";
    foreach (BehaviorRecord::where('StudentID', '6920101')->get() as $r) {
        $appeal = $r->appeal;
        echo "  - Record: {$r->RecordID} | Status: {$r->Status} | Sem: {$r->semester_id} | Rule: {$r->rule?->RuleName} ({$r->rule?->ScoreModifier})\n";
    }
}

echo "\n=== TESTING DASHBOARD CONTROLLER QUERY ===\n";
$controller = new \App\Http\Controllers\Discipline\DashboardController();
$ref = new \ReflectionClass($controller);
$m = $ref->getMethod('getSelectedSemesterId');
$m->setAccessible(true);
$semId = $m->invoke($controller) ?? 1;

$netModifiers = \Illuminate\Support\Facades\DB::table('behavior_records')
    ->join('behavior_rules', 'behavior_records.RuleID', '=', 'behavior_rules.RuleID')
    ->leftJoin('appeals', 'behavior_records.RecordID', '=', 'appeals.RecordID')
    ->where('behavior_records.semester_id', $semId)
    ->whereIn('behavior_records.Status', ['อนุมัติ', 'อนุมัติแล้ว', 'อยู่ในระหว่างยื่นอุทธรณ์'])
    ->selectRaw('behavior_records.StudentID as StudentID')
    ->selectRaw("SUM(
        CASE 
            WHEN behavior_rules.RuleType = 'ตัดคะแนน' THEN 
                -ABS(behavior_rules.ScoreModifier) + (CASE WHEN appeals.Status = 'คืนคะแนน' THEN COALESCE(appeals.RestoredPoints, ABS(behavior_rules.ScoreModifier)) ELSE 0 END)
            ELSE 
                ABS(behavior_rules.ScoreModifier)
        END
    ) as net_modifier")
    ->groupBy('behavior_records.StudentID')
    ->pluck('net_modifier', 'StudentID');

echo "SUCCESS! Query executed with zero error. Found rows: " . count($netModifiers) . "\n";


