<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Bangladeshi mobile (01XXXXXXXXX, with or without +88) or an international number in +format.
 */
class PhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (self::normalize((string) $value) === null) {
            $fail('Please enter a valid phone number, e.g. 01XXXXXXXXX.');
        }
    }

    /** Returns 01XXXXXXXXX for Bangladeshi mobiles, +<digits> for international numbers, or null if invalid. */
    public static function normalize(string $value): ?string
    {
        $clean = preg_replace('/[\s\-().]/', '', $value);

        if (preg_match('/^(?:\+?88)?(01[3-9]\d{8})$/', $clean, $m)) {
            return $m[1];
        }
        if (preg_match('/^\+\d{8,15}$/', $clean)) {
            return $clean;
        }

        return null;
    }
}
