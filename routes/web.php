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

// ==========================================
// Admin Routes
// ==========================================
Route::middleware(['auth', 'role:ผู้ดูแลระบบ,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class)->middleware('permission:users');
    Route::resource('permissions', App\Http\Controllers\Admin\RolePermissionController::class)->only(['index', 'store'])->middleware('permission:permissions');
    
    // Students CRUD
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
    Route::resource('teachers', App\Http\Controllers\Admin\TeacherController::class)->middleware('permission:users');

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
    
    Route::resource('behavior-rules', App\Http\Controllers\Discipline\BehaviorRuleController::class)->middleware('permission:behavior-rules');
    Route::resource('behavior-records', App\Http\Controllers\Discipline\BehaviorRecordController::class)->middleware('permission:behavior-records');
    Route::patch('behavior-records/{record}/approve', [App\Http\Controllers\Discipline\BehaviorRecordController::class, 'approve'])->name('behavior-records.approve')->middleware('permission:behavior-records');
    
    Route::resource('appeals', App\Http\Controllers\Discipline\AppealController::class)->middleware('permission:appeals');
    Route::patch('appeals/{appeal}/resolve', [App\Http\Controllers\Discipline\AppealController::class, 'resolve'])->name('appeals.resolve')->middleware('permission:appeals');
    
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
    Route::get('/behavior-records', [App\Http\Controllers\ParentGuardian\BehaviorRecordController::class, 'index'])->name('behavior-records.index')->middleware('permission:behavior-records');
    Route::get('/attendance', [App\Http\Controllers\ParentGuardian\AttendanceController::class, 'index'])->name('attendance.index')->middleware('permission:attendance');
    Route::resource('messages', App\Http\Controllers\ParentGuardian\MessageController::class)->middleware('permission:messages');
});

// Redirect /home to correct dashboard
Route::get('/home', function () {
    if (!Auth::check()) return redirect('/login');
    
    $role = strtolower(Auth::user()->Role);
    return match($role) {
        'ผู้ดูแลระบบ', 'admin' => redirect()->route('admin.dashboard'),
        'ฝ่ายปกครอง', 'discipline' => redirect()->route('discipline.dashboard'),
        'ครู', 'teacher' => redirect()->route('teacher.dashboard'),
        'นักเรียน', 'student' => redirect()->route('student.dashboard'),
        'ผู้ปกครอง', 'parent' => redirect()->route('parent.dashboard'),
        default => redirect('/login')
    };
})->name('home');
