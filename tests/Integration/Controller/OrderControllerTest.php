<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\OrderController;
use App\Delivery\DeliveryFactory;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Http\Request;
use App\Repository\ClientRepository;
use App\Repository\OrderRepository;
use App\Tests\DatabaseTestCase;

class OrderControllerTest extends DatabaseTestCase
{
    private OrderController $controller;
    private ClientRepository $clientRepo;
    private OrderRepository $orderRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientRepo = new ClientRepository(self::$pdo);
        $this->orderRepo = new OrderRepository(self::$pdo);
        $this->controller = new OrderController(
            $this->orderRepo,
            $this->clientRepo,
            new DeliveryFactory(),
        );
    }

    private function createClient(): int
    {
        return $this->clientRepo->create([
            'name' => 'Test Client',
            'address' => '123 Main St',
        ]);
    }

    public function testCreateOrder(): void
    {
        $clientId = $this->createClient();
        $request = new Request('POST', '/orders', [
            'client_id' => $clientId,
            'delivery_type' => 'air',
            'delivery_address' => '456 Oak Ave',
        ]);

        ob_start();
        $this->controller->create($request)->send();
        $output = ob_get_clean();
        $data = json_decode($output, true);

        $this->assertSame($clientId, $data['client_id']);
        $this->assertSame('air', $data['delivery_type']);
        $this->assertSame('pending', $data['status']);
        $this->assertStringStartsWith('AIR-', $data['tracking_code']);
        $this->assertSame('50.00', $data['cost']);
    }

    public function testCreateOrderMissingClientIdThrows(): void
    {
        $this->expectException(ValidationException::class);
        $request = new Request('POST', '/orders', [
            'delivery_type' => 'water',
            'delivery_address' => '1 St',
        ]);
        $this->controller->create($request);
    }

    public function testCreateOrderInvalidClientIdThrows(): void
    {
        $this->expectException(NotFoundException::class);
        $request = new Request('POST', '/orders', [
            'client_id' => 99999,
            'delivery_type' => 'water',
            'delivery_address' => '1 St',
        ]);
        $this->controller->create($request);
    }

    public function testCreateOrderMissingDeliveryTypeThrows(): void
    {
        $this->expectException(ValidationException::class);
        $clientId = $this->createClient();
        $request = new Request('POST', '/orders', [
            'client_id' => $clientId,
            'delivery_address' => '1 St',
        ]);
        $this->controller->create($request);
    }

    public function testCreateOrderInvalidDeliveryTypeThrows(): void
    {
        $this->expectException(ValidationException::class);
        $clientId = $this->createClient();
        $request = new Request('POST', '/orders', [
            'client_id' => $clientId,
            'delivery_type' => 'teleport',
            'delivery_address' => '1 St',
        ]);
        $this->controller->create($request);
    }

    public function testCreateOrderMissingDeliveryAddressThrows(): void
    {
        $this->expectException(ValidationException::class);
        $clientId = $this->createClient();
        $request = new Request('POST', '/orders', [
            'client_id' => $clientId,
            'delivery_type' => 'water',
        ]);
        $this->controller->create($request);
    }

    public function testShowOrder(): void
    {
        $clientId = $this->createClient();
        $orderId = $this->orderRepo->create([
            'client_id' => $clientId,
            'delivery_type' => 'land',
            'tracking_code' => 'LND-aabbcc',
            'delivery_address' => '789 Pine Rd',
            'cost' => 15.00,
        ]);

        $request = new Request('GET', "/orders/{$orderId}", []);
        $request->setRouteParams(['id' => (string) $orderId]);

        ob_start();
        $this->controller->show($request)->send();
        $output = ob_get_clean();
        $data = json_decode($output, true);

        $this->assertSame('land', $data['delivery_type']);
        $this->assertSame('LND-aabbcc', $data['tracking_code']);
    }

    public function testShowNonExistentThrows(): void
    {
        $this->expectException(NotFoundException::class);
        $request = new Request('GET', '/orders/99999', []);
        $request->setRouteParams(['id' => '99999']);
        $this->controller->show($request);
    }

    public function testIndexReturnsOrders(): void
    {
        $clientId = $this->createClient();
        $this->orderRepo->create([
            'client_id' => $clientId,
            'delivery_type' => 'water',
            'tracking_code' => 'WAT-112233',
            'delivery_address' => '1 St',
            'cost' => 5.00,
        ]);

        $request = new Request('GET', '/orders', []);
        ob_start();
        $this->controller->index($request)->send();
        $output = ob_get_clean();
        $data = json_decode($output, true);

        $this->assertCount(1, $data);
        $this->assertSame('Test Client', $data[0]['client_name']);
    }
}
