<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Show the form to reset password via phone number.
     */
    public function showPhoneResetForm()
    {
        return view('auth.passwords.phone');
    }

    /**
     * Reset the password by verifying username and phone number.
     */
    public function updatePasswordByPhone(Request $request)
    {
        $request->validate([
            'Username' => 'required|string',
            'Phone' => 'required|string',
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols(), 'confirmed'],
        ], [
            'Username.required' => 'กรุณากรอกรหัสประจำตัว',
            'Phone.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'password.required' => 'กรุณากรอกรหัสผ่านใหม่',
            'password.min' => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน',
            'password.letters' => 'รหัสผ่านต้องมีตัวอักษรภาษาอังกฤษอย่างน้อย 1 ตัว',
            'password.mixed' => 'รหัสผ่านต้องมีทั้งตัวพิมพ์เล็กและตัวพิมพ์ใหญ่ (A-Z และ a-z)',
            'password.numbers' => 'รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว (0-9)',
            'password.symbols' => 'รหัสผ่านต้องมีอักขระพิเศษอย่างน้อย 1 ตัว (เช่น @, #, $, !)',
        ]);

        // Find user by Username
        $user = User::where('Username', $request->Username)->first();

        if ($user) {
            $dbPhoneClean = preg_replace('/\D/', '', $user->Phone ?? '');
            $reqPhoneClean = preg_replace('/\D/', '', $request->Phone ?? '');
            if ($dbPhoneClean === '' || $dbPhoneClean !== $reqPhoneClean) {
                $user = null;
            }
        }

        if (!$user) {
            return back()
                ->withErrors(['Username' => 'รหัสประจำตัวหรือเบอร์โทรศัพท์ไม่ถูกต้อง'])
                ->withInput($request->only('Username', 'Phone'));
        }

        // Update password
        $user->Password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')
            ->with('success', 'เปลี่ยนรหัสผ่านใหม่เรียบร้อยแล้ว กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่ของคุณ');
    }
}

