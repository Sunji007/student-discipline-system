<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('Username', 'student01')->first();
Auth::login($user);

try {
    // Simulate the AJAX POST request
    $request = Illuminate\Http\Request::create('/prayer/scan/store', 'POST', [
        'student_id' => '10001',
        'period' => 'บ่าย',
        'status' => 'ละหมาด'
    ]);

    // Set headers as JSON request
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('Content-Type', 'application/json');

    $response = app()->handle($request);
    $output = "Status code: " . $response->getStatusCode() . "\n";
    $output .= "Response body: " . $response->getContent() . "\n";
    file_put_contents('storage/error.txt', $output);
    echo "Saved output to storage/error.txt\n";
} catch (\Throwable $e) {
    $err = "Exception: " . $e->getMessage() . "\n";
    $err .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    $err .= "Stack trace:\n" . $e->getTraceAsString() . "\n";
    file_put_contents('storage/error.txt', $err);
    echo "Saved exception to storage/error.txt\n";
}
