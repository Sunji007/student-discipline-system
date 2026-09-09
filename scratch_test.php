<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$list = App\Models\Semester::all();
foreach ($list as $sem) {
    echo "ID: {$sem->semester_id}, Year: {$sem->academic_year}, Term: {$sem->term}, Active: {$sem->is_active}\n";
}

$studentCount = App\Models\Student::count();
echo "Total students: {$studentCount}\n";
