<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\ClientRepository;
use App\Tests\DatabaseTestCase;

class ClientRepositoryTest extends DatabaseTestCase
{
    private ClientRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new ClientRepository(self::$pdo);
    }

    public function testFindByPhoneReturnsMatches(): void
    {
        $this->repo->create(['name' => 'Alice', 'phone' => '+111']);
        $this->repo->create(['name' => 'Bob', 'phone' => '+222']);
        $this->repo->create(['name' => 'Carol', 'phone' => '+111']);

        $results = $this->repo->findByPhone('+111');
        $this->assertCount(2, $results);
        $this->assertSame('Alice', $results[0]['name']);
        $this->assertSame('Carol', $results[1]['name']);
    }

    public function testFindByPhoneReturnsEmptyWhenNoMatch(): void
    {
        $this->assertSame([], $this->repo->findByPhone('+999'));
    }
}
