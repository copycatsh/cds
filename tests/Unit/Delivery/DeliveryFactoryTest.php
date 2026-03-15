<?php

declare(strict_types=1);

namespace App\Tests\Unit\Delivery;

use App\Delivery\AirDelivery;
use App\Delivery\DeliveryFactory;
use App\Delivery\LandDelivery;
use App\Delivery\WaterDelivery;
use App\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class DeliveryFactoryTest extends TestCase
{
    private DeliveryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new DeliveryFactory();
    }

    public function testCreateWater(): void
    {
        $this->assertInstanceOf(WaterDelivery::class, $this->factory->create('water'));
    }

    public function testCreateLand(): void
    {
        $this->assertInstanceOf(LandDelivery::class, $this->factory->create('land'));
    }

    public function testCreateAir(): void
    {
        $this->assertInstanceOf(AirDelivery::class, $this->factory->create('air'));
    }

    public function testCreateInvalidThrows(): void
    {
        $this->expectException(ValidationException::class);
        $this->factory->create('teleport');
    }
}
