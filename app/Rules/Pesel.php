<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Pesel implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pesel = preg_replace('/\D+/', '', (string) $value);

        if ($pesel === '') {
            return;
        }

        if (strlen($pesel) !== 11) {
            $fail('Numer PESEL jest niepoprawny!');
            return;
        }

        $sum = 0;
        $weights = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];

        foreach ($weights as $index => $weight) {
            $sum += ((int) $pesel[$index]) * $weight;
        }

        $controlDigit = (10 - ($sum % 10)) % 10;

        if ($controlDigit !== (int) $pesel[10]) {
            $fail('Numer PESEL jest niepoprawny!');
        }
    }
}
