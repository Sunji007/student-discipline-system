<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::first();
echo "First Student ID: " . ($student ? $student->StudentID : 'none') . "\n";

$studentsWithParents = \App\Models\Student::has('parents')->first();
echo "Student with parents: " . ($studentsWithParents ? $studentsWithParents->StudentID : 'none') . "\n";

$allSemesters = \App\Models\Semester::all();
echo "Total Semesters: " . $allSemesters->count() . "\n";
foreach ($allSemesters as $sem) {
    echo "Semester: ID={$sem->id}, Name={$sem->name}, Year={$sem->academic_year}, Active={$sem->is_active}\n";
}
