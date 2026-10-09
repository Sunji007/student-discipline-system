<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ThaiMobilePhone implements ValidationRule
{
    public const MESSAGE = 'กรุณากรอกเบอร์มือถือ 10 หลัก ขึ้นต้นด้วย 06, 08 หรือ 09 เช่น 081-234-5678';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/^(?:0[689][0-9]{8}|0[689][0-9]-[0-9]{3}-[0-9]{4})$/D', $value)) {
            $fail(self::MESSAGE);
        }
    }

    public static function storageVariants(string $phone): array
    {
        $digits = str_replace('-', '', $phone);

        return [$digits, substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6)];
    }
}
