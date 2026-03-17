<?php

use App\Domain\Invoice\Entities\InvoiceLine;
use App\Domain\Invoice\ValueObjects\Money;

test('invoice line calculations with various VAT rates', function (string $amount, float $quantity, int $vat, string $expectedNet, string $expectedVat, string $expectedTotal) {
    $unitPrice = new Money($amount);
    $line = new InvoiceLine('Test Product', $quantity, $unitPrice, $vat);

    expect($line->getNetTotal()->getAmount())->toBe($expectedNet)
        ->and($line->getVatAmount()->getAmount())->toBe($expectedVat)
        ->and($line->getTotal()->getAmount())->toBe($expectedTotal);
})->with([
    '19% VAT standard' => ['100.00', 1.0, 19, '100.00', '19.00', '119.00'],
    '9% VAT food/medical' => ['100.00', 1.0, 9, '100.00', '9.00', '109.00'],
    '5% VAT books/housing' => ['100.00', 1.0, 5, '100.00', '5.00', '105.00'],
    '0% VAT exempt' => ['100.00', 1.0, 0, '100.00', '0.00', '100.00'],
    'Multiple quantities' => ['50.00', 2.5, 19, '125.00', '23.75', '148.75'],
]);

test('precision and rounding behavior', function (string $amount, float $quantity, int $vat, string $expectedVat) {
    $unitPrice = new Money($amount);
    $line = new InvoiceLine('Rounding Test', $quantity, $unitPrice, $vat);

    // Current implementation uses bcadd/bcmul which truncates rather than rounds if not handled.
    // This test will help verify if we need to implement a rounding strategy.
    expect($line->getVatAmount()->getAmount())->toBe($expectedVat);
})->with([
    'Rounding down (0.004 -> 0.00)' => ['0.02', 1.0, 19, '0.00'], // 0.02 * 0.19 = 0.0038
    'Rounding up (0.005 -> 0.01)' => ['0.03', 1.0, 19, '0.01'], // 0.03 * 0.19 = 0.0057
]);

test('midpoint and symmetry rounding', function (string $input, string $expected) {
    expect(Money::round($input, 2)->getAmount())->toBe($expected);
})->with([
    '0.005 rounds to 0.01' => ['0.005', '0.01'],
    '0.004 rounds to 0.00' => ['0.004', '0.00'],
    '-0.005 rounds to -0.01' => ['-0.005', '-0.01'],
    '-0.004 rounds to -0.00' => ['-0.004', '0.00'],
]);

test('extremely large numbers', function (string $amount, string $multiplier, string $expected) {
    $money = new Money($amount);
    expect($money->multiply($multiplier)->getAmount())->toBe($expected);
})->with([
    'Million turnover' => ['1000000.00', '1.19', '1190000.00'],
    'Billion turnover' => ['1000000000.00', '0.19', '190000000.00'],
    'Small percentage of large number' => ['999999999.99', '0.0001', '100000.00'],
]);

test('edge cases for quantities and values', function (float $quantity, string $amount, string $expectedNet) {
    $unitPrice = new Money($amount);
    $line = new InvoiceLine('Edge Case', $quantity, $unitPrice, 19);

    expect($line->getNetTotal()->getAmount())->toBe($expectedNet);
})->with([
    'Zero quantity' => [0.0, '100.00', '0.00'],
    'Negative quantity (storno/return)' => [-1.0, '100.00', '-100.00'],
    'Zero price' => [1.0, '0.00', '0.00'],
]);
