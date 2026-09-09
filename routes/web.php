<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes(['register' => false]);

Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
});

Route::get('/clear-cache', function() {
    try {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE informant_reports MODIFY ReportID VARCHAR(50) NOT NULL");
        $reports = \Illuminate\Support\Facades\DB::table('informant_reports')->orderBy('created_at', 'asc')->get();
        $index = 1;
        foreach ($reports as $r) {
            $newId = 'INF-' . str_pad($index, 3, '0', STR_PAD_LEFT);
            \Illuminate\Support\Facades\DB::table('informant_reports')
                ->where('ReportID', $r->ReportID)
                ->update(['ReportID' => $newId, 'IsAnonymous' => 1]);
            $index++;
        }
    } catch (\Throwable $e) {}
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return 'ระบบทำการล้างแคช (Cache Cleared) อัปเดตรหัสเรื่อง INF-001 เรียบร้อยแล้ว!';
});

Route::get('/debug-parents-list', function() {
    $students = \App\Models\Student::with('user', 'parent')->get();
    $out = "=== DEBUG PARENTS AND STUDENTS ===\n\n";
    foreach ($students as $s) {
        $out .= "----------------------------------------\n";
        $out .= "Student ID: {$s->StudentID} | Name: {$s->FullName} | Class: {$s->Classroom} | UserID: {$s->UserID} | ParentID: {$s->ParentID}\n";
        $pByStudId = \App\Models\ParentGuardian::where('StudentID', $s->StudentID)->first();
        $out .= "Parent (where StudentID={$s->StudentID}): " . ($pByStudId ? "Name: {$pByStudId->FullName} | ParentID: {$pByStudId->ParentID} | UserID: {$pByStudId->UserID}" : "NONE") . "\n";
        $pByParentId = \App\Models\ParentGuardian::where('ParentID', $s->ParentID)->first();
        $out .= "Parent (where ParentID={$s->ParentID}): " . ($pByParentId ? "Name: {$pByParentId->FullName} | ParentID: {$pByParentId->ParentID} | UserID: {$pByParentId->UserID}" : "NONE") . "\n";
    }
    $out .= "\nALL PARENTS IN DATABASE:\n";
    $parents = \App\Models\ParentGuardian::with('user')->get();
    foreach ($parents as $p) {
        $out .= "ParentID: {$p->ParentID} | UserID: {$p->UserID} | Name: {$p->FullName} | StudentID field: {$p->StudentID}\n";
    }
    return response($out, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
});

Route::get('/seed-classroom-31', function () {
    $count = \Database\Seeders\Classroom31Seeder::seed40Students();
    return response()->json([
        'status' => 'success',
        'message' => "จำลองเพิ่มนักเรียนห้อง ม.3/1 จำนวน {$count} คน เรียบร้อยแล้ว",
        'total_31_students' => \App\Models\Student::where('Classroom', 'like', '%3/1%')->orWhere('Classroom', '3/1')->count(),
    ], 200, ['Content-Type' => 'application/json; charset=utf-8']);
});



Route::get('/fix-storage', function() {
    $target = storage_path('app/public');
    $link = public_path('storage');

    $results = [];
    $results[] = "Target path: " . $target . " (Exists: " . (file_exists($target) ? 'Yes' : 'No') . ")";
    $results[] = "Link path: " . $link . " (Exists: " . (file_exists($link) ? 'Yes' : 'No') . ")";

    if (file_exists($link)) {
        if (is_link($link)) {
            $results[] = "Link path is a symlink pointing to: " . readlink($link);
            unlink($link);
            $results[] = "Existing symlink deleted.";
        } else if (is_dir($link)) {
            $results[] = "Link path is a real directory! Renaming it to storage_old...";
            rename($link, $link . '_old_' . time());
        } else {
            $results[] = "Link path is a file! Deleting it...";
            unlink($link);
        }
    }

    if (symlink($target, $link)) {
        $results[] = "Successfully created symbolic link from $link to $target!";
    } else {
        $results[] = "Failed to create symbolic link using php symlink(). Attempting Artisan storage:link...";
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            $results[] = "Artisan storage:link command finished.";
        } catch (\Exception $e) {
            $results[] = "Artisan storage:link failed: " . $e->getMessage();
        }
    }

    return implode("<br>\n", $results);
});

Route::match(['GET', 'POST'], '/semesters/switch', [App\Http\Controllers\SemesterController::class, 'switchSemester'])->name('semesters.switch');


// ==========================================
// Admin Routes
// ==========================================
Route::middleware(['auth', 'role:ผู้ดูแลระบบ,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard');
    Route::post('users/check-citizen-id', [App\Http\Controllers\Admin\UserController::class, 'checkCitizenID'])->name('users.check-citizen-id')->middleware('permission:users');
    Route::post('parents/check-citizen-id', [App\Http\Controllers\Admin\ParentGuardianController::class, 'checkCitizenID'])->name('parents.check-citizen-id')->middleware('permission:users');
    Route::post('users/check-phone', [App\Http\Controllers\Admin\UserController::class, 'checkPhone'])->name('users.check-phone')->middleware('permission:users');
    Route::post('users/check-email', [App\Http\Controllers\Admin\UserController::class, 'checkEmail'])->name('users.check-email')->middleware('permission:users');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class)->middleware('permission:users');
    Route::resource('permissions', App\Http\Controllers\Admin\RolePermissionController::class)->only(['index'])->middleware('permission:permissions');
    
    // Students CRUD
    Route::get('students/import', [App\Http\Controllers\Admin\StudentImportController::class, 'show'])->name('students.import')->middleware('permission:users');
    Route::get('students/import/template', [App\Http\Controllers\Admin\StudentImportController::class, 'template'])->name('students.import.template')->middleware('permission:users');
    Route::post('students/import', [App\Http\Controllers\Admin\StudentImportController::class, 'store'])->name('students.import.store')->middleware('permission:users');
    Route::get('students/get-next-id', [App\Http\Controllers\Admin\StudentController::class, 'getNextId'])->name('students.get-next-id')->middleware('permission:users');
    Route::get('students/{student}/card', [App\Http\Controllers\Admin\StudentController::class, 'card'])->name('students.card')->middleware('permission:users');
    Route::resource('students', App\Http\Controllers\Admin\StudentController::class)->middleware('permission:users');

    // Parent / Guardian management (nested under student)
    Route::prefix('students/{student}/parents')->name('students.parents.')->middleware('permission:users')->group(function () {
        Route::get('/',        [App\Http\Controllers\Admin\ParentGuardianController::class, 'index'])  ->name('index');
        Route::get('/create',  [App\Http\Controllers\Admin\ParentGuardianController::class, 'create']) ->name('create');
        Route::post('/',       [App\Http\Controllers\Admin\ParentGuardianController::class, 'store'])  ->name('store');
        Route::get('/{parent}/edit',   [App\Http\Controllers\Admin\ParentGuardianController::class, 'edit'])    ->name('edit');
        Route::put('/{parent}',        [App\Http\Controllers\Admin\ParentGuardianController::class, 'update'])  ->name('update');
        Route::delete('/{parent}',     [App\Http\Controllers\Admin\ParentGuardianController::class, 'destroy']) ->name('destroy');
    });

    // Teachers CRUD
    Route::get('teachers/get-next-id', [App\Http\Controllers\Admin\TeacherController::class, 'getNextId'])->name('teachers.get-next-id')->middleware('permission:users');
    Route::resource('teachers', App\Http\Controllers\Admin\TeacherController::class)->middleware('permission:users');

    // Promotions (Automatic background promotion based on behavior scores)
    Route::get('promotions', function () {
        return redirect()->route('admin.students.index')
            ->with('info', 'ระบบเลื่อนชั้นปีการศึกษาทำงานแบบอัตโนมัติในเบื้องหลังตามคะแนนพฤติกรรมแล้ว (เกณฑ์ผ่าน >= 50 คะแนน)');
    })->name('promotions.index')->middleware('permission:users');

    // Semesters Management
    Route::post('semesters', [App\Http\Controllers\Admin\SemesterController::class, 'store'])->name('semesters.store')->middleware('permission:users');
    Route::post('semesters/{semester}/set-active', [App\Http\Controllers\Admin\SemesterController::class, 'setActive'])->name('semesters.set-active')->middleware('permission:users');
    Route::delete('semesters/{semester}', [App\Http\Controllers\Admin\SemesterController::class, 'destroy'])->name('semesters.destroy')->middleware('permission:users');

    // Messages
    Route::resource('messages', App\Http\Controllers\Admin\MessageController::class)->only(['index', 'show', 'create', 'store'])->middleware('permission:messages');
});

// ==========================================
// Prayer Routes
// ==========================================
Route::middleware(['auth', 'permission:attendance'])->prefix('prayer')->name('prayer.')->group(function () {
    Route::get('/scan', [App\Http\Controllers\Prayer\PrayerController::class, 'scan'])->name('scan');
    Route::post('/scan/store', [App\Http\Controllers\Prayer\PrayerController::class, 'store'])->name('scan.store');
    Route::get('/calendar', [App\Http\Controllers\Prayer\PrayerController::class, 'calendar'])->name('calendar');
    Route::get('/dashboard', [App\Http\Controllers\Prayer\PrayerController::class, 'dashboard'])->name('dashboard');
    Route::get('/export', [App\Http\Controllers\Prayer\PrayerController::class, 'export'])->name('export');
    Route::post('/corrections/toggle', [App\Http\Controllers\Prayer\PrayerController::class, 'toggleCorrection'])->name('corrections.toggle');
    Route::get('/export-select', function () {
        $role = strtolower(auth()->user()->Role);
        if (in_array($role, ['นักเรียน', 'student', 'ผู้ปกครอง', 'parent'])) {
            abort(403, 'ไม่มีสิทธิ์ส่งออกรายงาน');
        }
        return view('prayer.export-select');
    })->name('export-select');
});

// ==========================================
// Discipline Routes
// ==========================================
Route::middleware(['auth', 'role:ฝ่ายปกครอง,discipline'])->prefix('discipline')->name('discipline.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Discipline\DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard');
    Route::get('/risk-students', [App\Http\Controllers\Discipline\DashboardController::class, 'riskStudents'])->name('risk-students')->middleware('permission:risk-students');
    Route::get('/behavior-report', [App\Http\Controllers\Discipline\BehaviorReportController::class, 'index'])->name('behavior-report')->middleware('permission:behavior-records');
    Route::get('/behavior-report/export', [App\Http\Controllers\Discipline\BehaviorReportController::class, 'export'])->name('behavior-report.export')->middleware('permission:behavior-records');
    
    Route::get('behavior-rules', [App\Http\Controllers\Discipline\BehaviorRuleController::class, 'index'])->name('behavior-rules.index')->middleware('permission:behavior-rules');
    Route::get('behavior-rules/create', [App\Http\Controllers\Discipline\BehaviorRuleController::class, 'create'])->name('behavior-rules.create')->middleware('permission:behavior-rules');
    Route::post('behavior-rules', [App\Http\Controllers\Discipline\BehaviorRuleController::class, 'store'])->name('behavior-rules.store')->middleware('permission:behavior-rules');
    Route::get('behavior-rules/{id}/edit', [App\Http\Controllers\Discipline\BehaviorRuleController::class, 'edit'])->name('behavior-rules.edit')->middleware('permission:behavior-rules');
    Route::put('behavior-rules/{id}', [App\Http\Controllers\Discipline\BehaviorRuleController::class, 'update'])->name('behavior-rules.update')->middleware('permission:behavior-rules');
    Route::patch('behavior-rules/{id}', [App\Http\Controllers\Discipline\BehaviorRuleController::class, 'update'])->middleware('permission:behavior-rules');
    Route::delete('behavior-rules/{id}', [App\Http\Controllers\Discipline\BehaviorRuleController::class, 'destroy'])->name('behavior-rules.destroy')->middleware('permission:behavior-rules');
    Route::get('behavior-rules/{id}', [App\Http\Controllers\Discipline\BehaviorRuleController::class, 'show'])->name('behavior-rules.show')->middleware('permission:behavior-rules');
    Route::resource('behavior-records', App\Http\Controllers\Discipline\BehaviorRecordController::class)->middleware('permission:behavior-records');
    Route::patch('behavior-records/{record}/approve', [App\Http\Controllers\Discipline\BehaviorRecordController::class, 'approve'])->name('behavior-records.approve')->middleware('permission:behavior-records');
    Route::patch('behavior-records/{record}/reject', [App\Http\Controllers\Discipline\BehaviorRecordController::class, 'reject'])->name('behavior-records.reject')->middleware('permission:behavior-records');
    
    Route::resource('appeals', App\Http\Controllers\Discipline\AppealController::class)->middleware('permission:appeals');
    Route::patch('appeals/{appeal}/resolve', [App\Http\Controllers\Discipline\AppealController::class, 'resolve'])->name('appeals.resolve')->middleware('permission:appeals');
    
    Route::post('informant-reports/bulk-archive', [App\Http\Controllers\Discipline\InformantReportController::class, 'bulkArchive'])->name('informant-reports.bulk-archive')->middleware('permission:informant-reports');
    Route::patch('informant-reports/{id}/archive', [App\Http\Controllers\Discipline\InformantReportController::class, 'archive'])->name('informant-reports.archive')->middleware('permission:informant-reports');
    Route::patch('informant-reports/{id}/unarchive', [App\Http\Controllers\Discipline\InformantReportController::class, 'unarchive'])->name('informant-reports.unarchive')->middleware('permission:informant-reports');
    Route::resource('informant-reports', App\Http\Controllers\Discipline\InformantReportController::class)->middleware('permission:informant-reports');
    Route::patch('informant-reports/{informantReport}/accept', [App\Http\Controllers\Discipline\InformantReportController::class, 'accept'])->name('informant-reports.accept')->middleware('permission:informant-reports');
    Route::patch('informant-reports/{informantReport}/close', [App\Http\Controllers\Discipline\InformantReportController::class, 'close'])->name('informant-reports.close')->middleware('permission:informant-reports');
    Route::resource('messages', App\Http\Controllers\Discipline\MessageController::class)->middleware('permission:messages');
});

// ==========================================
// Teacher Routes
// ==========================================
Route::middleware(['auth', 'role:ครู,teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard');
    Route::get('/classroom', [App\Http\Controllers\Teacher\ClassroomController::class, 'index'])->name('classroom.index')->middleware('permission:attendance');
    
    Route::resource('attendance', App\Http\Controllers\Teacher\AttendanceController::class)->only(['index', 'store'])->middleware('permission:attendance');
    Route::resource('behavior-records', App\Http\Controllers\Teacher\BehaviorRecordController::class)->only(['create', 'store', 'index', 'show'])->middleware('permission:behavior-records');
    Route::resource('messages', App\Http\Controllers\Teacher\MessageController::class)->middleware('permission:messages');
});

// ==========================================
// Student Routes
// ==========================================
Route::middleware(['auth', 'role:นักเรียน,student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard');
    
    Route::get('/attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index')->middleware('permission:attendance');
    Route::get('/behavior-records', [App\Http\Controllers\Student\BehaviorRecordController::class, 'index'])->name('behavior-records.index')->middleware('permission:behavior-records');
    Route::post('appeals/{appeal}/cancel', [App\Http\Controllers\Student\AppealController::class, 'cancel'])->name('appeals.cancel')->middleware('permission:appeals');
    Route::resource('appeals', App\Http\Controllers\Student\AppealController::class)->middleware('permission:appeals');
    Route::resource('informant-reports', App\Http\Controllers\Student\InformantReportController::class)->only(['index', 'create', 'store'])->middleware('permission:informant-reports');
    Route::resource('messages', App\Http\Controllers\Student\MessageController::class)->middleware('permission:messages');

    // เช็คละหมาด — หน้าแสดง QR/Barcode ให้ครูสแกน
    Route::get('/prayer-checkin', function () {
        $user    = auth()->user();
        $student = $user->student;
        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'ไม่พบข้อมูลนักเรียน');
        }
        $today       = \Carbon\Carbon::today()->toDateString();
        $prayerToday = \App\Models\PrayerRecord::where('StudentID', $student->StudentID)
            ->where('RecordDate', $today)
            ->get();
        return view('student.prayer-checkin', compact('student', 'prayerToday', 'today'));
    })->name('prayer-checkin')->middleware('permission:attendance');
});

// ==========================================
// Parent Routes
// ==========================================
Route::middleware(['auth', 'role:ผู้ปกครอง,parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ParentGuardian\DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard');
    Route::post('/switch-student', [App\Http\Controllers\ParentGuardian\DashboardController::class, 'switchStudent'])->name('switch-student');
    Route::get('/behavior-records', [App\Http\Controllers\ParentGuardian\BehaviorRecordController::class, 'index'])->name('behavior-records.index')->middleware('permission:behavior-records');
    Route::get('/attendance', [App\Http\Controllers\ParentGuardian\AttendanceController::class, 'index'])->name('attendance.index')->middleware('permission:attendance');
    Route::resource('messages', App\Http\Controllers\ParentGuardian\MessageController::class)->middleware('permission:messages');
});

Route::get('/select-role', [App\Http\Controllers\Auth\RoleSelectionController::class, 'showRoleSelection'])->name('select-role');
Route::post('/select-role', [App\Http\Controllers\Auth\RoleSelectionController::class, 'selectRole'])->name('select-role.store');
Route::post('/switch-role', [App\Http\Controllers\Auth\RoleSelectionController::class, 'switchRole'])->name('switch-role');

// Redirect /home to correct dashboard
Route::get('/home', function () {
    if (!Auth::check()) return redirect('/login');
    
    $activeRole = session('active_role');
    if (!$activeRole) {
        $roles = Auth::user()->getAvailableRoles();
        if (count($roles) > 1) {
            return redirect()->route('select-role');
        }
        $activeRole = Auth::user()->Role;
        session(['active_role' => $activeRole]);
    }

    $role = strtolower($activeRole);
    return match($role) {
        'ผู้ดูแลระบบ', 'admin' => redirect()->route('admin.dashboard'),
        'ฝ่ายปกครอง', 'discipline' => redirect()->route('discipline.dashboard'),
        'ครู', 'teacher' => redirect()->route('teacher.dashboard'),
        'นักเรียน', 'student' => redirect()->route('student.dashboard'),
        'ผู้ปกครอง', 'parent' => redirect()->route('parent.dashboard'),
        default => redirect('/login')
    };
})->name('home');

