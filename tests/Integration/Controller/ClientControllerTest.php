<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ClientController;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Http\Request;
use App\Repository\ClientRepository;
use App\Tests\DatabaseTestCase;

class ClientControllerTest extends DatabaseTestCase
{
    private ClientController $controller;
    private ClientRepository $clientRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientRepo = new ClientRepository(self::$pdo);
        $this->controller = new ClientController($this->clientRepo);
    }

    public function testIndexReturnsEmptyArray(): void
    {
        $request = new Request('GET', '/clients', []);
        $response = $this->controller->index($request);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertSame([], json_decode($output, true));
    }

    public function testCreateClient(): void
    {
        $request = new Request('POST', '/clients', [
            'name' => 'Jane Doe',
            'phone' => '+555',
            'address' => '99 Elm St',
        ]);
        $response = $this->controller->create($request);

        ob_start();
        $response->send();
        $output = ob_get_clean();
        $data = json_decode($output, true);

        $this->assertSame('Jane Doe', $data['name']);
        $this->assertSame('+555', $data['phone']);
        $this->assertArrayHasKey('id', $data);
    }

    public function testCreateClientMissingNameThrows(): void
    {
        $this->expectException(ValidationException::class);
        $request = new Request('POST', '/clients', []);
        $this->controller->create($request);
    }

    public function testShowClient(): void
    {
        $id = $this->clientRepo->create(['name' => 'Bob']);
        $request = new Request('GET', "/clients/{$id}", []);
        $request->setRouteParams(['id' => (string) $id]);
        $response = $this->controller->show($request);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertSame('Bob', json_decode($output, true)['name']);
    }

    public function testShowNonExistentThrows(): void
    {
        $this->expectException(NotFoundException::class);
        $request = new Request('GET', '/clients/99999', []);
        $request->setRouteParams(['id' => '99999']);
        $this->controller->show($request);
    }

    public function testDeleteClient(): void
    {
        $id = $this->clientRepo->create(['name' => 'ToDelete']);
        $request = new Request('DELETE', "/clients/{$id}", []);
        $request->setRouteParams(['id' => (string) $id]);

        ob_start();
        $this->controller->delete($request)->send();
        $output = ob_get_clean();

        $this->assertTrue(json_decode($output, true)['deleted']);
        $this->assertNull($this->clientRepo->findById($id));
    }
}
