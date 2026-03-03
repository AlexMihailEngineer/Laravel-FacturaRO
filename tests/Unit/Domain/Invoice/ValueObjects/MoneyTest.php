<?php

namespace Tests\Unit\Domain\Invoice\ValueObjects;

use App\Domain\Invoice\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_creates_money_with_fixed_precision()
    {
        $money = new Money(100.5555, 2);
        $this->assertEquals('100.55', $money->getAmount());
    }

    public function test_it_adds_money_correctly()
    {
        $m1 = new Money('10.50');
        $m2 = new Money('5.25');
        $sum = $m1->add($m2);

        $this->assertEquals('15.75', $sum->getAmount());
    }

    public function test_it_multiplies_money_correctly()
    {
        $m1 = new Money('10.00');
        $product = $m1->multiply(1.19); // 19% VAT

        $this->assertEquals('11.90', $product->getAmount());
    }
}
