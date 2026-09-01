<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'logout',
            'semesters/switch',
            'parent/switch-student',
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            return back()->withInput()->withErrors([
                'evidence' => 'ขนาดไฟล์ที่อัปโหลดรวมกันใหญ่เกินขีดจำกัดของระบบ (โปรดเลือกไฟล์ที่มีขนาดรวมไม่เกิน 15MB)'
            ]);
        });
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->back()->with('error', 'เซสชันหมดอายุ โปรดลองใหม่อีกครั้ง');
        });
    })
    ->registered(function ($app) {
        if (file_exists(base_path('../../public_html'))) {
            $app->usePublicPath(realpath(base_path('../../public_html')));
        }
    })->create();
