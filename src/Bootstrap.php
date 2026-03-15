<?php

declare(strict_types=1);

namespace App;

use App\Controller\ClientController;
use App\Controller\OrderController;
use App\Database\Connection;
use App\Delivery\DeliveryFactory;
use App\Repository\ClientRepository;
use App\Repository\OrderRepository;

class Bootstrap
{
    private ?Connection $connection = null;
    private ?ClientRepository $clientRepo = null;
    private ?OrderRepository $orderRepo = null;
    private ?ClientController $clientController = null;
    private ?OrderController $orderController = null;

    public function getConnection(): Connection
    {
        if ($this->connection === null) {
            $this->connection = new Connection(
                host: getenv('DB_HOST') ?: 'postgres',
                port: (int) (getenv('DB_PORT') ?: 5432),
                database: getenv('DB_NAME') ?: 'cds',
                user: getenv('DB_USER') ?: 'cds',
                password: getenv('DB_PASSWORD') ?: 'cds_secret',
            );
        }

        return $this->connection;
    }

    public function getClientRepository(): ClientRepository
    {
        if ($this->clientRepo === null) {
            $this->clientRepo = new ClientRepository($this->getConnection()->getPdo());
        }

        return $this->clientRepo;
    }

    public function getOrderRepository(): OrderRepository
    {
        if ($this->orderRepo === null) {
            $this->orderRepo = new OrderRepository($this->getConnection()->getPdo());
        }

        return $this->orderRepo;
    }

    public function getClientController(): ClientController
    {
        if ($this->clientController === null) {
            $this->clientController = new ClientController(
                $this->getClientRepository(),
            );
        }

        return $this->clientController;
    }

    public function getOrderController(): OrderController
    {
        if ($this->orderController === null) {
            $this->orderController = new OrderController(
                $this->getOrderRepository(),
                $this->getClientRepository(),
                new DeliveryFactory(),
            );
        }

        return $this->orderController;
    }
}
