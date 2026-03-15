<?php

declare(strict_types=1);

namespace App\Tests\Unit\Delivery;

use App\Delivery\LandDelivery;
use PHPUnit\Framework\TestCase;

class LandDeliveryTest extends TestCase
{
    private LandDelivery $delivery;

    protected function setUp(): void
    {
        $this->delivery = new LandDelivery();
    }

    public function testCalculateCost(): void
    {
        $this->assertSame(15.0, $this->delivery->calculateCost(['delivery_type' => 'land']));
    }

    public function testEstimateDeliveryDays(): void
    {
        $this->assertSame(7, $this->delivery->estimateDeliveryDays(['delivery_type' => 'land']));
    }

    public function testIsAvailableForValidAddress(): void
    {
        $this->assertTrue($this->delivery->isAvailableForAddress('456 Highway Rd'));
    }

    public function testIsNotAvailableForEmptyAddress(): void
    {
        $this->assertFalse($this->delivery->isAvailableForAddress(''));
        $this->assertFalse($this->delivery->isAvailableForAddress('   '));
    }
}
