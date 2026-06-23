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
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::resource('permissions', App\Http\Controllers\Admin\RolePermissionController::class)->only(['index', 'store']);
    
    // Students CRUD
    Route::get('students/{student}/card', [App\Http\Controllers\Admin\StudentController::class, 'card'])->name('students.card');
    Route::resource('students', App\Http\Controllers\Admin\StudentController::class);

    // Parent / Guardian management (nested under student)
    Route::prefix('students/{student}/parents')->name('students.parents.')->group(function () {
        Route::get('/',        [App\Http\Controllers\Admin\ParentGuardianController::class, 'index'])  ->name('index');
        Route::get('/create',  [App\Http\Controllers\Admin\ParentGuardianController::class, 'create']) ->name('create');
        Route::post('/',       [App\Http\Controllers\Admin\ParentGuardianController::class, 'store'])  ->name('store');
        Route::get('/{parent}/edit',   [App\Http\Controllers\Admin\ParentGuardianController::class, 'edit'])    ->name('edit');
        Route::put('/{parent}',        [App\Http\Controllers\Admin\ParentGuardianController::class, 'update'])  ->name('update');
        Route::delete('/{parent}',     [App\Http\Controllers\Admin\ParentGuardianController::class, 'destroy']) ->name('destroy');
    });

    // Teachers CRUD
    Route::resource('teachers', App\Http\Controllers\Admin\TeacherController::class);

    // Messages
    Route::resource('messages', App\Http\Controllers\Admin\MessageController::class)->only(['index', 'show', 'create', 'store']);
});

// ==========================================
// Prayer Routes
// ==========================================
Route::middleware(['auth'])->prefix('prayer')->name('prayer.')->group(function () {
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
    Route::get('/dashboard', [App\Http\Controllers\Discipline\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/risk-students', [App\Http\Controllers\Discipline\DashboardController::class, 'riskStudents'])->name('risk-students');
    Route::get('/behavior-report', [App\Http\Controllers\Discipline\BehaviorReportController::class, 'index'])->name('behavior-report');
    Route::get('/behavior-report/export', [App\Http\Controllers\Discipline\BehaviorReportController::class, 'export'])->name('behavior-report.export');
    
    Route::resource('behavior-rules', App\Http\Controllers\Discipline\BehaviorRuleController::class);
    Route::resource('behavior-records', App\Http\Controllers\Discipline\BehaviorRecordController::class);
    Route::patch('behavior-records/{record}/approve', [App\Http\Controllers\Discipline\BehaviorRecordController::class, 'approve'])->name('behavior-records.approve');
    
    Route::resource('appeals', App\Http\Controllers\Discipline\AppealController::class);
    Route::patch('appeals/{appeal}/resolve', [App\Http\Controllers\Discipline\AppealController::class, 'resolve'])->name('appeals.resolve');
    
    Route::resource('informant-reports', App\Http\Controllers\Discipline\InformantReportController::class);
    Route::patch('informant-reports/{informantReport}/accept', [App\Http\Controllers\Discipline\InformantReportController::class, 'accept'])->name('informant-reports.accept');
    Route::patch('informant-reports/{informantReport}/close', [App\Http\Controllers\Discipline\InformantReportController::class, 'close'])->name('informant-reports.close');
    Route::resource('messages', App\Http\Controllers\Discipline\MessageController::class);
});

// ==========================================
// Teacher Routes
// ==========================================
Route::middleware(['auth', 'role:ครู,teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/classroom', [App\Http\Controllers\Teacher\ClassroomController::class, 'index'])->name('classroom.index');
    
    Route::resource('attendance', App\Http\Controllers\Teacher\AttendanceController::class)->only(['index', 'store']);
    Route::resource('behavior-records', App\Http\Controllers\Teacher\BehaviorRecordController::class)->only(['create', 'store', 'index', 'show']);
    Route::resource('messages', App\Http\Controllers\Teacher\MessageController::class);
});

// ==========================================
// Student Routes
// ==========================================
Route::middleware(['auth', 'role:นักเรียน,student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/behavior-records', [App\Http\Controllers\Student\BehaviorRecordController::class, 'index'])->name('behavior-records.index');
    Route::resource('appeals', App\Http\Controllers\Student\AppealController::class);
    Route::resource('informant-reports', App\Http\Controllers\Student\InformantReportController::class)->only(['index', 'create', 'store']);
    Route::resource('messages', App\Http\Controllers\Student\MessageController::class);

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
    })->name('prayer-checkin');
});

// ==========================================
// Parent Routes
// ==========================================
Route::middleware(['auth', 'role:ผู้ปกครอง,parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ParentGuardian\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/behavior-records', [App\Http\Controllers\ParentGuardian\BehaviorRecordController::class, 'index'])->name('behavior-records.index');
    Route::get('/attendance', [App\Http\Controllers\ParentGuardian\AttendanceController::class, 'index'])->name('attendance.index');
    Route::resource('messages', App\Http\Controllers\ParentGuardian\MessageController::class);
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
