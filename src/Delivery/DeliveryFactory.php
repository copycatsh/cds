<?php

declare(strict_types=1);

namespace App\Delivery;

use App\Exception\ValidationException;

class DeliveryFactory
{
    public function create(string $type): DeliveryInterface
    {
        return match ($type) {
            'water' => new WaterDelivery(),
            'land' => new LandDelivery(),
            'air' => new AirDelivery(),
            default => throw new ValidationException("Invalid delivery type: {$type}"),
        };
    }
}
