<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Get the response for a successful password reset.
     */
    protected function sendResetResponse(\Illuminate\Http\Request $request, $response)
    {
        // Logout the user so they have to login with their new password
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'เปลี่ยนรหัสผ่านใหม่เรียบร้อยแล้ว กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่ของคุณ');
    }

    /**
     * Set the user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function setUserPassword($user, $password)
    {
        $user->Password = \Illuminate\Support\Facades\Hash::make($password);
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(\Illuminate\Http\Request $request)
    {
        return [
            'email' => trim($request->input('email', '')),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation'),
            'token' => $request->input('token'),
        ];
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ];
    }

    /**
     * Get the password reset validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [
            'password.required'  => 'กรุณากรอกรหัสผ่านใหม่',
            'password.confirmed' => 'รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน',
            'password.min'       => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'password.letters'   => 'รหัสผ่านต้องมีตัวอักษรภาษาอังกฤษ',
            'password.mixed'     => 'รหัสผ่านต้องมีทั้งตัวพิมพ์เล็กและตัวพิมพ์ใหญ่ (a-z และ A-Z)',
            'password.numbers'   => 'รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว (0-9)',
            'password.symbols'   => 'รหัสผ่านต้องมีอักขระพิเศษอย่างน้อย 1 ตัว (เช่น @, #, $, !)',
        ];
    }
}
