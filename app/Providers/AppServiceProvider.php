<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ใช้ Bootstrap 5 สำหรับ Pagination
        Paginator::useBootstrap();

        if (!app()->runningInConsole()) {
            if (request()->server('HTTP_HOST') && (str_contains(request()->server('HTTP_HOST'), 'site.yru.ac.th') || str_contains(request()->server('HTTP_HOST'), 'student.yru.ac.th'))) {
                URL::forceScheme('https');
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                URL::forceScheme('https');
            } elseif (request()->isSecure()) {
                URL::forceScheme('https');
            }
        }
    }
}
