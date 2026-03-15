<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class ClientRepository extends AbstractRepository
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'clients');
    }

    /** @return list<string> */
    protected function allowedColumns(): array
    {
        return ['name', 'phone', 'address'];
    }

    /** @return list<array<string, mixed>> */
    public function findByPhone(string $phone): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM clients WHERE phone = :phone ORDER BY id');
        $stmt->execute(['phone' => $phone]);

        return $stmt->fetchAll();
    }
}
