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

    // กำหนด field ที่ validate
    protected function validateLogin(Request $request): void
    {
        $request->validate([
            'Username' => ['required', 'string'],
            'Password' => ['required', 'string'],
        ]);
    }
}