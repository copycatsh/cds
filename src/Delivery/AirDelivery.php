<?php

declare(strict_types=1);

namespace App\Delivery;

class AirDelivery implements DeliveryInterface
{
    private const float BASE_COST = 50.00;
    private const int BASE_DAYS = 2;

    /** @param array<string, mixed> $order */
    public function calculateCost(array $order): float
    {
        return self::BASE_COST;
    }

    /** @param array<string, mixed> $order */
    public function estimateDeliveryDays(array $order): int
    {
        return self::BASE_DAYS;
    }

    public function isAvailableForAddress(string $address): bool
    {
        if (trim($address) === '') {
            return false;
        }

        return true;
    }
}
