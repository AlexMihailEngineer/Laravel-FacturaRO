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
        $v = trim((string)$value);
        $v = strtoupper($v);
        
        if (str_starts_with($v, 'RO')) {
            $v = trim(substr($v, 2));
        }

        if (!preg_match('/^[0-9]+$/', $v)) {
            $fail('The :attribute must contain only digits (optionally prefixed by RO).');
            return;
        }

        $cui = $v;

        if (strlen($cui) < 2 || strlen($cui) > 10) {
            $fail('The :attribute must be a valid Romanian CUI (between 2 and 10 digits).');
            return;
        }

        $controlDigit = (int) substr($cui, -1);
        $baseCui = substr($cui, 0, -1);
        
        // Testing key: 753217532 (reversed: 2, 3, 5, 7, 1, 2, 3, 5, 7)
        $weights = [2, 3, 5, 7, 1, 2, 3, 5, 7];
        $baseRev = strrev($baseCui);
        
        $sum = 0;
        for ($i = 0; $i < strlen($baseRev); $i++) {
            $sum += (int)$baseRev[$i] * $weights[$i];
        }

        $calculatedDigit = ($sum * 10) % 11;
        if ($calculatedDigit === 10) {
            $calculatedDigit = 0;
        }

        if ($calculatedDigit !== $controlDigit) {
            $fail('The :attribute is not a valid Romanian CUI (failed checksum).');
        }
    }
}
