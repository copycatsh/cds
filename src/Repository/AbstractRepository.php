<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\NotFoundException;
use PDO;

abstract class AbstractRepository
{
    public function __construct(
        protected readonly PDO $pdo,
        protected readonly string $table,
    ) {
    }

    /** @return list<string> */
    abstract protected function allowedColumns(): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function filterColumns(array $data): array
    {
        $allowed = $this->allowedColumns();

        return array_filter(
            $data,
            fn (string $key) => in_array($key, $allowed, true),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            sprintf('SELECT * FROM %s WHERE id = :id', $this->table)
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /** @return array<string, mixed> */
    public function findByIdOrFail(int $id): array
    {
        $row = $this->findById($id);
        if ($row === null) {
            throw new NotFoundException(
                sprintf('%s with id %d not found', ucfirst(rtrim($this->table, 's')), $id)
            );
        }

        return $row;
    }

    /** @return list<array<string, mixed>> */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            sprintf('SELECT * FROM %s ORDER BY id', $this->table)
        );

        return $stmt->fetchAll();
    }

    /**
     * @param array<string, mixed> $data
     * @return int The inserted row ID
     */
    public function create(array $data): int
    {
        $data = $this->filterColumns($data);
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn (string $k) => ':' . $k, array_keys($data)));

        $stmt = $this->pdo->prepare(
            sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table, $columns, $placeholders)
        );
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        $this->findByIdOrFail($id);

        $data = $this->filterColumns($data);
        $sets = implode(', ', array_map(fn (string $k) => $k . ' = :' . $k, array_keys($data)));

        $stmt = $this->pdo->prepare(
            sprintf('UPDATE %s SET %s WHERE id = :id', $this->table, $sets)
        );

        return $stmt->execute([...$data, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $this->findByIdOrFail($id);

        $stmt = $this->pdo->prepare(
            sprintf('DELETE FROM %s WHERE id = :id', $this->table)
        );

        return $stmt->execute(['id' => $id]);
    }
}
