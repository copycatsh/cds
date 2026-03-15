<?php

declare(strict_types=1);

namespace App\Tests;

use PDO;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected static ?PDO $pdo = null;

    public static function setUpBeforeClass(): void
    {
        if (self::$pdo !== null) {
            return;
        }

        $host = getenv('DB_HOST') ?: 'postgres';
        $port = getenv('DB_PORT') ?: '5432';
        $dbName = getenv('DB_NAME') ?: 'cds_test';
        $user = getenv('DB_USER') ?: 'cds';
        $password = getenv('DB_PASSWORD') ?: 'cds_secret';

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbName);
        self::$pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $migrations = glob(__DIR__ . '/../migrations/*.sql');
        sort($migrations);
        foreach ($migrations as $file) {
            $sql = file_get_contents($file);
            if ($sql !== false) {
                // Wrap in a try-catch so re-running migrations on existing schema doesn't fail
                try {
                    self::$pdo->exec($sql);
                } catch (\PDOException $e) {
                    // Ignore "already exists" errors
                    if (!str_contains($e->getMessage(), 'already exists')) {
                        throw $e;
                    }
                }
            }
        }
    }

    protected function setUp(): void
    {
        self::$pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        self::$pdo->rollBack();
    }
}
