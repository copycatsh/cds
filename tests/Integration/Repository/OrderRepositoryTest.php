<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\ClientRepository;
use App\Repository\OrderRepository;
use App\Tests\DatabaseTestCase;

class OrderRepositoryTest extends DatabaseTestCase
{
    private OrderRepository $orderRepo;
    private ClientRepository $clientRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderRepo = new OrderRepository(self::$pdo);
        $this->clientRepo = new ClientRepository(self::$pdo);
    }

    private function createClientAndOrder(string $deliveryType = 'water'): int
    {
        $clientId = $this->clientRepo->create(['name' => 'Test Client', 'address' => '1 St']);
        $trackingCode = OrderRepository::generateTrackingCode($deliveryType);

        return $this->orderRepo->create([
            'client_id' => $clientId,
            'delivery_type' => $deliveryType,
            'tracking_code' => $trackingCode,
            'delivery_address' => '456 Delivery St',
            'cost' => 5.00,
        ]);
    }

    public function testCreateOrderAndFindById(): void
    {
        $orderId = $this->createClientAndOrder();
        $order = $this->orderRepo->findById($orderId);

        $this->assertSame('water', $order['delivery_type']);
        $this->assertSame('pending', $order['status']);
        $this->assertSame('456 Delivery St', $order['delivery_address']);
        $this->assertStringStartsWith('WAT-', $order['tracking_code']);
    }

    public function testFindByClientId(): void
    {
        $clientId = $this->clientRepo->create(['name' => 'Client A']);
        $this->orderRepo->create([
            'client_id' => $clientId,
            'delivery_type' => 'air',
            'tracking_code' => OrderRepository::generateTrackingCode('air'),
            'delivery_address' => '1 St',
            'cost' => 50.00,
        ]);

        $orders = $this->orderRepo->findByClientId($clientId);
        $this->assertCount(1, $orders);
        $this->assertSame('Client A', $orders[0]['client_name']);
    }

    public function testFindByStatus(): void
    {
        $this->createClientAndOrder();
        $orders = $this->orderRepo->findByStatus('pending');
        $this->assertCount(1, $orders);
        $this->assertArrayHasKey('client_name', $orders[0]);
    }

    public function testFindByTrackingCode(): void
    {
        $orderId = $this->createClientAndOrder('land');
        $order = $this->orderRepo->findById($orderId);

        $found = $this->orderRepo->findByTrackingCode($order['tracking_code']);
        $this->assertNotNull($found);
        $this->assertSame($orderId, $found['id']);
        $this->assertArrayHasKey('client_name', $found);
    }

    public function testFindByTrackingCodeReturnsNull(): void
    {
        $this->assertNull($this->orderRepo->findByTrackingCode('XXX-000000'));
    }

    public function testFindAllIncludesClientName(): void
    {
        $this->createClientAndOrder();
        $orders = $this->orderRepo->findAll();
        $this->assertCount(1, $orders);
        $this->assertSame('Test Client', $orders[0]['client_name']);
    }

    public function testGenerateTrackingCodeFormat(): void
    {
        $water = OrderRepository::generateTrackingCode('water');
        $land = OrderRepository::generateTrackingCode('land');
        $air = OrderRepository::generateTrackingCode('air');

        $this->assertMatchesRegularExpression('/^WAT-[0-9a-f]{6}$/', $water);
        $this->assertMatchesRegularExpression('/^LND-[0-9a-f]{6}$/', $land);
        $this->assertMatchesRegularExpression('/^AIR-[0-9a-f]{6}$/', $air);
    }

    public function testGenerateTrackingCodeInvalidTypeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        OrderRepository::generateTrackingCode('teleport');
    }
}
