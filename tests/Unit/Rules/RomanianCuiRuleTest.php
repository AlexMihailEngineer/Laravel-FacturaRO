<?php

use App\Rules\RomanianCuiRule;

test('it validates romanian cui correctly with and without RO prefix', function (string $value, bool $expected) {
    $rule = new RomanianCuiRule();
    $passed = true;

    $rule->validate('cui', $value, function ($message) use (&$passed) {
        $passed = false;
    });

    expect($passed)->toBe($expected);
})->with([
    'Google Romania (Numeric only)' => ['23047266', true],
    'Google Romania (With RO prefix)' => ['RO23047266', true],
    'Google Romania (Lowercase ro prefix)' => ['ro23047266', true],
    'eMAG (Dante International)' => ['14399840', true],
    'Orange Romania' => ['9010105', true],
]);

test('it fails for checksum near-miss IDs', function () {
    $rule = new RomanianCuiRule();
    $passed = true;

    // Google Romania CUI is 23047266. One digit off (near-miss) should fail.
    $rule->validate('cui', '23047267', function ($message) use (&$passed) {
        $passed = false;
    });

    expect($passed)->toBeFalse();
});
