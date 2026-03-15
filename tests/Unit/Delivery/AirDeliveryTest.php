<?php

declare(strict_types=1);

namespace App\Tests\Unit\Delivery;

use App\Delivery\AirDelivery;
use PHPUnit\Framework\TestCase;

class AirDeliveryTest extends TestCase
{
    private AirDelivery $delivery;

    protected function setUp(): void
    {
        $this->delivery = new AirDelivery();
    }

    public function testCalculateCost(): void
    {
        $this->assertSame(50.0, $this->delivery->calculateCost(['delivery_type' => 'air']));
    }

    public function testEstimateDeliveryDays(): void
    {
        $this->assertSame(2, $this->delivery->estimateDeliveryDays(['delivery_type' => 'air']));
    }

    public function testIsAvailableForValidAddress(): void
    {
        $this->assertTrue($this->delivery->isAvailableForAddress('789 Airport Rd'));
    }

    public function testIsNotAvailableForEmptyAddress(): void
    {
        $this->assertFalse($this->delivery->isAvailableForAddress(''));
        $this->assertFalse($this->delivery->isAvailableForAddress('   '));
    }
}
