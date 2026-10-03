<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Total Students: " . \App\Models\Student::count() . PHP_EOL;
foreach (\App\Models\Student::orderBy('StudentID')->take(15)->get() as $s) {
    echo $s->StudentID . " | " . $s->FullName . " | " . $s->GradeLevel . " | " . $s->Classroom . PHP_EOL;
}
