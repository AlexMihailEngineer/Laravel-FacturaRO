<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RomanianCuiRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $v = (string) $value;
        if (str_starts_with(strtoupper($v), 'RO')) {
            $v = substr($v, 2);
        }

        $cui = preg_replace('/[^0-9]/', '', $v);

        if (strlen($cui) < 2 || strlen($cui) > 10) {
            $fail('The :attribute must be a valid Romanian CUI (between 2 and 10 digits).');
            return;
        }

        $c_digit = (int) substr($cui, -1);
        $baseCui = substr($cui, 0, -1);
        
        $v_key = "753217532";
        $keyRev = strrev($v_key);
        $baseRev = strrev($baseCui);
        
        $sum = 0;
        for ($i = 0; $i < strlen($baseRev); $i++) {
            $sum += (int)$baseRev[$i] * (int)$keyRev[$i];
        }

        $calculatedDigit = ($sum * 10) % 11;
        if ($calculatedDigit === 10) {
            $calculatedDigit = 0;
        }

        if ($calculatedDigit !== $c_digit) {
            $fail('The :attribute is not a valid Romanian CUI (failed Modulo 11 check).');
        }
    }
}
