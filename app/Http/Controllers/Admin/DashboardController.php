<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // รวม 5 COUNT เป็น query เดียว + cache 60 วินาที
        $stats = Cache::remember('admin_dashboard_stats', 60, function () {
            $counts = DB::selectOne('
                SELECT
                    (SELECT COUNT(*) FROM users)            AS total_users,
                    (SELECT COUNT(*) FROM students)         AS total_students,
                    (SELECT COUNT(*) FROM teachers)         AS total_teachers,
                    (SELECT COUNT(*) FROM discipline_staff) AS total_discipline,
                    (SELECT COUNT(*) FROM parents)          AS total_parents
            ');
            return (array) $counts;
        });

        $recentUsers = User::select('UserID','Username','FullName','Role','Status','created_at')
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }
}