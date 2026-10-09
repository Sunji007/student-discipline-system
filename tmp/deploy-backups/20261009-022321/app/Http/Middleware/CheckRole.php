<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $activeRole = strtolower(session('active_role', auth()->user()->Role));
        if (in_array($activeRole, ['ผู้ดูแลระบบ', 'admin'])) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($activeRole === strtolower($role)) {
                return $next($request);
            }
        }

        return redirect()->route('home')->with('error', 'ไม่มีสิทธิ์เข้าใช้งานหน้านี้');
    }
}
