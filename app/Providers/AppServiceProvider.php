<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ใช้ Bootstrap 5 สำหรับ Pagination
        Paginator::useBootstrap();

        if (!app()->runningInConsole()) {
            if (request()->server('HTTP_HOST') && (str_contains(request()->server('HTTP_HOST'), 'site.yru.ac.th') || str_contains(request()->server('HTTP_HOST'), 'student.yru.ac.th'))) {
                URL::forceScheme('https');
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                URL::forceScheme('https');
            } elseif (request()->isSecure()) {
                URL::forceScheme('https');
            }
        }

        // กำหนดชื่อผู้ส่งอีเมลให้เป็นภาษาไทยถูกต้อง ไม่เป็นตัวอักษรต่างดาว
        config(['mail.from.name' => 'โรงเรียนศิริราษฎร์สามัคคี']);

        // ปรับแต่งอีเมลรีเซ็ตรหัสผ่านเป็นภาษาไทยทั้งหมด
        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function ($notifiable, $token) {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('คำขอตั้งรหัสผ่านใหม่ — โรงเรียนศิริราษฎร์สามัคคี')
                ->greeting('สวัสดีครับ/ค่ะ')
                ->line('คุณได้รับอีเมลนี้เนื่องจากมีคำขอตั้งรหัสผ่านใหม่สำหรับบัญชีของคุณในระบบสารสนเทศงานวินัย โรงเรียนศิริราษฎร์สามัคคี')
                ->action('คลิกที่นี่เพื่อตั้งรหัสผ่านใหม่ (Reset Password)', $resetUrl)
                ->line('ลิงก์สำหรับตั้งรหัสผ่านใหม่นี้จะหมดอายุภายใน 60 นาที')
                ->line('หากคุณไม่ได้เป็นผู้ส่งคำขอนี้ สามารถละเว้นอีเมลนี้ได้ โดยบัญชีของคุณจะยังคงปลอดภัยตามเดิม')
                ->salutation("ขอแสดงความนับถือ\nโรงเรียนศิริราษฎร์สามัคคี");
        });
    }
}
