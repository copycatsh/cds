<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class OrderRepository extends AbstractRepository
{
    private const array TRACKING_PREFIXES = [
        'water' => 'WAT',
        'land' => 'LND',
        'air' => 'AIR',
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'orders');
    }

    /** @return list<string> */
    protected function allowedColumns(): array
    {
        return ['client_id', 'delivery_type', 'status', 'tracking_code', 'delivery_address', 'cost'];
    }

    /** @return list<array<string, mixed>> */
    public function findByClientId(int $clientId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, c.name AS client_name
             FROM orders o
             JOIN clients c ON o.client_id = c.id
             WHERE o.client_id = :client_id
             ORDER BY o.created_at DESC'
        );
        $stmt->execute(['client_id' => $clientId]);

        return $stmt->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function findByStatus(string $status): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, c.name AS client_name
             FROM orders o
             JOIN clients c ON o.client_id = c.id
             WHERE o.status = :status
             ORDER BY o.created_at DESC'
        );
        $stmt->execute(['status' => $status]);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findByTrackingCode(string $trackingCode): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, c.name AS client_name
             FROM orders o
             JOIN clients c ON o.client_id = c.id
             WHERE o.tracking_code = :tracking_code'
        );
        $stmt->execute(['tracking_code' => $trackingCode]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Override findAll to include client name via JOIN.
     * @return list<array<string, mixed>>
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT o.*, c.name AS client_name
             FROM orders o
             JOIN clients c ON o.client_id = c.id
             ORDER BY o.created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public static function generateTrackingCode(string $deliveryType): string
    {
        $prefix = self::TRACKING_PREFIXES[$deliveryType]
            ?? throw new \InvalidArgumentException("Unknown delivery type: {$deliveryType}");

        return $prefix . '-' . bin2hex(random_bytes(3));
    }
}
