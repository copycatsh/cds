<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Exception\NotFoundException;
use App\Repository\ClientRepository;
use App\Tests\DatabaseTestCase;

class AbstractRepositoryTest extends DatabaseTestCase
{
    private ClientRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new ClientRepository(self::$pdo);
    }

    public function testFindAllEmpty(): void
    {
        $this->assertSame([], $this->repo->findAll());
    }

    public function testCreateAndFindById(): void
    {
        $id = $this->repo->create(['name' => 'Alice', 'phone' => '+1111', 'address' => '1 St']);
        $this->assertGreaterThan(0, $id);

        $row = $this->repo->findById($id);
        $this->assertSame('Alice', $row['name']);
        $this->assertSame('+1111', $row['phone']);
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $this->assertNull($this->repo->findById(99999));
    }

    public function testFindByIdOrFailThrows(): void
    {
        $this->expectException(NotFoundException::class);
        $this->repo->findByIdOrFail(99999);
    }

    public function testFindAllPopulated(): void
    {
        $this->repo->create(['name' => 'Alice']);
        $this->repo->create(['name' => 'Bob']);
        $all = $this->repo->findAll();
        $this->assertCount(2, $all);
    }

    public function testUpdate(): void
    {
        $id = $this->repo->create(['name' => 'Alice']);
        $this->repo->update($id, ['name' => 'Alice Updated']);
        $row = $this->repo->findById($id);
        $this->assertSame('Alice Updated', $row['name']);
    }

    public function testUpdateNonExistentThrows(): void
    {
        $this->expectException(NotFoundException::class);
        $this->repo->update(99999, ['name' => 'Ghost']);
    }

    public function testDelete(): void
    {
        $id = $this->repo->create(['name' => 'ToDelete']);
        $this->repo->delete($id);
        $this->assertNull($this->repo->findById($id));
    }

    public function testDeleteNonExistentThrows(): void
    {
        $this->expectException(NotFoundException::class);
        $this->repo->delete(99999);
    }
}
