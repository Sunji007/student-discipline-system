<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$parentUser = \App\Models\User::where('Username', 'parent1')->first();
echo "parent1 UserID: " . ($parentUser ? $parentUser->UserID : 'none') . "\n";
if ($parentUser) {
    echo "parentStudents count: " . $parentUser->parentStudents->count() . "\n";
    $pg = \App\Models\ParentGuardian::where('UserID', $parentUser->UserID)->first();
    echo "ParentGuardian row by UserID: " . ($pg ? "ID={$pg->ParentID}, StudentID={$pg->StudentID}, Name={$pg->FullName}" : "NONE") . "\n";
}

$firstStudent = \App\Models\Student::first();
echo "First Student: " . $firstStudent->StudentID . " (" . $firstStudent->FullName . ")\n";

// Let's link parent1 to the first student if not linked!
if ($parentUser && $firstStudent) {
    $pg = \App\Models\ParentGuardian::where('UserID', $parentUser->UserID)->first();
    if (!$pg) {
        $pg = new \App\Models\ParentGuardian();
        $pg->ParentID = 'PAR-TEST-01';
        $pg->UserID = $parentUser->UserID;
        $pg->StudentID = $firstStudent->StudentID;
        $pg->FirstName = 'ผู้ปกครอง';
        $pg->LastName = 'ทดสอบ';
        $pg->Relationship = 'บิดา';
        $pg->Phone = '0812345678';
        $pg->save();
        echo "Created and linked ParentGuardian row for parent1 to student {$firstStudent->StudentID}!\n";
    } else {
        $pg->StudentID = $firstStudent->StudentID;
        $pg->save();
        echo "Updated ParentGuardian StudentID to {$firstStudent->StudentID}!\n";
    }
    
    // Also update Student's ParentID
    $firstStudent->ParentID = $pg->ParentID;
    $firstStudent->save();
    echo "Updated Student ParentID to {$pg->ParentID}!\n";
}
