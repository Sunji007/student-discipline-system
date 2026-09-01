<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleSelectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showRoleSelection()
    {
        $user = auth()->user();
        $roles = $user->getAvailableRoles();

        if (count($roles) <= 1) {
            session(['active_role' => $user->Role]);
            return redirect()->route('home');
        }

        return view('auth.select-role', compact('user', 'roles'));
    }

    public function selectRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $user = auth()->user();
        $availableRoles = $user->getAvailableRoles();

        if (!in_array($request->role, $availableRoles)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'บทบาทที่เลือกไม่ถูกต้อง'], 422);
            }
            return back()->with('error', 'บทบาทที่เลือกไม่ถูกต้อง');
        }

        session(['active_role' => $request->role]);

        $role = strtolower($request->role);
        $redirectUrl = match($role) {
            'ผู้ดูแลระบบ', 'admin' => route('admin.dashboard'),
            'ฝ่ายปกครอง', 'discipline' => route('discipline.dashboard'),
            'ครู', 'teacher' => route('teacher.dashboard'),
            'นักเรียน', 'student' => route('student.dashboard'),
            'ผู้ปกครอง', 'parent' => route('parent.dashboard'),
            default => route('home')
        };

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['redirect' => $redirectUrl]);
        }

        return redirect()->route('home');
    }

    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $user = auth()->user();
        $availableRoles = $user->getAvailableRoles();

        if (!in_array($request->role, $availableRoles)) {
            return back()->with('error', 'บทบาทที่เลือกไม่ถูกต้อง');
        }

        session(['active_role' => $request->role]);

        $role = strtolower($request->role);
        return match($role) {
            'ผู้ดูแลระบบ', 'admin' => redirect()->route('admin.dashboard'),
            'ฝ่ายปกครอง', 'discipline' => redirect()->route('discipline.dashboard'),
            'ครู', 'teacher' => redirect()->route('teacher.dashboard'),
            'นักเรียน', 'student' => redirect()->route('student.dashboard'),
            'ผู้ปกครอง', 'parent' => redirect()->route('parent.dashboard'),
            default => redirect()->route('home')
        };
    }
}
