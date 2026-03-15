<?php

declare(strict_types=1);

namespace App\Delivery;

interface DeliveryInterface
{
    /** @param array<string, mixed> $order */
    public function calculateCost(array $order): float;

    /** @param array<string, mixed> $order */
    public function estimateDeliveryDays(array $order): int;

    public function isAvailableForAddress(string $address): bool;
}
