<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$routes = [
    1 => ['guest', '/login'],
    2 => ['admin', '/admin/dashboard'],
    3 => ['admin', '/admin/users'],
    4 => ['admin', '/admin/users/create'],
    5 => ['admin', '/admin/students'],
    6 => ['admin', '/admin/students/create'],
    7 => ['admin', '/admin/students/6910101/card'],
    8 => ['admin', '/admin/students/import'],
    9 => ['admin', '/admin/students/6910101/parents'],
    10 => ['admin', '/admin/teachers'],
    11 => ['admin', '/admin/permissions'],
    12 => ['admin', '/admin/dashboard'],
    13 => ['discipline1', '/discipline/dashboard'],
    14 => ['discipline1', '/discipline/risk-students'],
    15 => ['discipline1', '/discipline/behavior-rules'],
    16 => ['discipline1', '/discipline/behavior-rules/create'],
    17 => ['discipline1', '/discipline/behavior-records'],
    18 => ['discipline1', '/discipline/appeals'],
    19 => ['discipline1', '/discipline/informant-reports'],
    20 => ['discipline1', '/discipline/behavior-report'],
    21 => ['teacher1', '/teacher/dashboard'],
    22 => ['teacher1', '/teacher/attendance'],
    23 => ['teacher1', '/teacher/behavior-records/create'],
    24 => ['teacher1', '/prayer/scan'],
    25 => ['teacher1', '/teacher/messages'],
    26 => ['student1', '/student/dashboard'],
    27 => ['student1', '/student/appeals/create'],
    28 => ['student1', '/student/prayer-checkin'],
    29 => ['student1', '/student/informant-reports/create'],
    30 => ['parent1', '/parent/dashboard'],
    31 => ['parent1', '/parent/behavior-records'],
    32 => ['parent1', '/parent/messages'],
];

echo "=== CHECKING ROUTES VIA LARAVEL KERNEL ===\n";
foreach ($routes as $fig => [$role, $uri]) {
    $user = null;
    if ($role !== 'guest') {
        $user = \App\Models\User::where('Username', $role)->first();
        if (!$user) {
            echo "fig_{$fig}: USER NOT FOUND for role {$role}!\n";
            continue;
        }
        \Illuminate\Support\Facades\Auth::login($user);
        session(['active_role' => $user->Role]);
    } else {
        \Illuminate\Support\Facades\Auth::logout();
    }

    $request = \Illuminate\Http\Request::create($uri, 'GET');
    // Share session
    $request->setLaravelSession(session());
    
    $response = $app->handle($request);
    $status = $response->getStatusCode();
    
    $statusStr = ($status === 404) ? ">>> 404 NOT FOUND <<<" : "OK";
    echo sprintf("fig_%02d: [%d] (%s) %s -> %s\n", $fig, $status, $role, $uri, $statusStr);
}
