<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    // หลัง login สำเร็จ → redirect ตาม Role
    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // บอก Laravel ว่าใช้ Username แทน email
    public function username(): string
    {
        return 'Username';
    }

    // ตรวจสอบ credentials + สถานะบัญชี
    protected function credentials(Request $request): array
    {
        return [
            'Username' => $request->Username,
            'password' => $request->Password,
            'Status'   => 'ปกติ',
        ];
    }

    private function sendLoginError(Request $request, array $errors)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['errors' => $errors], 422);
        }
        return back()->withErrors($errors)->withInput($request->except('Password'));
    }

    public function login(Request $request)
    {
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        // 1. ตรวจสอบฟิลด์ Username (ว่างหรือไม่)
        if (!$request->filled('Username')) {
            return $this->sendLoginError($request, ['Username' => 'กรุณากรอกรหัสประจำตัว/รหัสนักเรียน']);
        }

        // 2. ตรวจสอบว่ามีผู้ใช้งานนี้ในระบบหรือไม่
        $user = \App\Models\User::where('Username', $request->Username)->first();
        if (!$user) {
            $this->incrementLoginAttempts($request);
            return $this->sendLoginError($request, ['Username' => 'ไม่พบรหัสประจำตัว/รหัสนักเรียนนี้ในระบบ']);
        }

        // Parent fallback: if logging in with student's ID but parent's password
        if ($user->Role === 'นักเรียน' && $user->student && $request->filled('Password')) {
            if (!\Hash::check($request->Password, $user->Password)) {
                // If student password check fails, check if it matches a parent of this student
                $studentId = $user->student->StudentID;
                $parentUsers = \App\Models\User::where('Role', 'ผู้ปกครอง')
                    ->whereHas('parentGuardian', function($q) use ($studentId) {
                        $q->where('StudentID', $studentId);
                    })
                    ->get();

                foreach ($parentUsers as $parentUser) {
                    if (\Hash::check($request->Password, $parentUser->Password)) {
                        $user = $parentUser; // Swap user to parent!
                        break;
                    }
                }
            }
        }

        // 3. ตรวจสอบสถานะบัญชี
        if ($user->Status !== 'ปกติ') {
            $this->incrementLoginAttempts($request);
            return $this->sendLoginError($request, ['Username' => 'บัญชีผู้ใช้งานนี้ถูกระงับการใช้งาน']);
        }

        // 4. ตรวจสอบฟิลด์ Password (ว่างหรือไม่)
        if (!$request->filled('Password')) {
            return $this->sendLoginError($request, ['Password' => 'กรุณากรอกรหัสผ่าน']);
        }

        // 5. ตรวจสอบความถูกต้องของรหัสผ่าน
        if (!\Hash::check($request->Password, $user->Password)) {
            $this->incrementLoginAttempts($request);
            return $this->sendLoginError($request, ['Password' => 'รหัสผ่านไม่ถูกต้อง']);
        }

        // เคลียร์ความพยายามเข้าสู่ระบบ
        $this->clearLoginAttempts($request);

        // 6. เข้าระบบ
        \Auth::login($user, $request->filled('remember'));

        if ($request->hasSession()) {
            $request->session()->put('auth.password_confirmed_at', time());
            $request->session()->forget('active_role');

            // If parent logged in using a child's student ID, default session to that child
            if ($user->Role === 'ผู้ปกครอง' && $request->Username !== $user->Username) {
                $typedUsername = $request->Username;
                $hasStudent = $user->parentStudents()->where('StudentID', $typedUsername)->exists();
                if ($hasStudent) {
                    $request->session()->put('selected_student_id', $typedUsername);
                }
            }
        }

        $availableRoles = $user->getAvailableRoles();
        if (count($availableRoles) > 1) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['redirect' => route('select-role')]);
            }
            return redirect()->route('select-role');
        }

        session(['active_role' => $availableRoles[0] ?? $user->Role]);

        if ($request->ajax() || $request->wantsJson()) {
            $role = strtolower($availableRoles[0] ?? $user->Role);
            $redirectUrl = match($role) {
                'ผู้ดูแลระบบ', 'admin' => route('admin.dashboard'),
                'ฝ่ายปกครอง', 'discipline' => route('discipline.dashboard'),
                'ครู', 'teacher' => route('teacher.dashboard'),
                'นักเรียน', 'student' => route('student.dashboard'),
                'ผู้ปกครอง', 'parent' => route('parent.dashboard'),
                default => route('home')
            };
            return response()->json(['redirect' => $redirectUrl]);
        }

        return $this->sendLoginResponse($request);
    }
}