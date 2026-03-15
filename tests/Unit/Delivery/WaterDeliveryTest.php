<?php

declare(strict_types=1);

namespace App\Tests\Unit\Delivery;

use App\Delivery\WaterDelivery;
use PHPUnit\Framework\TestCase;

class WaterDeliveryTest extends TestCase
{
    private WaterDelivery $delivery;

    protected function setUp(): void
    {
        $this->delivery = new WaterDelivery();
    }

    public function testCalculateCost(): void
    {
        $this->assertSame(5.0, $this->delivery->calculateCost(['delivery_type' => 'water']));
    }

    public function testEstimateDeliveryDays(): void
    {
        $this->assertSame(14, $this->delivery->estimateDeliveryDays(['delivery_type' => 'water']));
    }

    public function testIsAvailableForValidAddress(): void
    {
        $this->assertTrue($this->delivery->isAvailableForAddress('123 Port Street'));
    }

    public function testIsNotAvailableForEmptyAddress(): void
    {
        $this->assertFalse($this->delivery->isAvailableForAddress(''));
        $this->assertFalse($this->delivery->isAvailableForAddress('   '));
    }
}
