<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueUserPhone implements ValidationRule
{
    public function __construct(private ?string $ignoreUserId = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $query = User::whereIn('Phone', ThaiMobilePhone::storageVariants($value));
        if ($this->ignoreUserId !== null) {
            $query->where('UserID', '!=', $this->ignoreUserId);
        }

        if ($query->exists()) {
            $fail('เบอร์โทรศัพท์นี้ถูกใช้งานในระบบแล้ว');
        }
    }
}
