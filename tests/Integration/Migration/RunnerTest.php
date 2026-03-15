<?php

declare(strict_types=1);

namespace App\Tests\Integration\Migration;

use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    private \PDO $pdo;
    private string $testMigrationsDir;

    protected function setUp(): void
    {
        $host = getenv('DB_HOST') ?: 'postgres';
        $port = getenv('DB_PORT') ?: '5432';
        $user = getenv('DB_USER') ?: 'cds';
        $password = getenv('DB_PASSWORD') ?: 'cds_secret';

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=cds_test', $host, $port);
        $this->pdo = new \PDO($dsn, $user, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        $this->pdo->exec('DROP TABLE IF EXISTS schema_migrations CASCADE');
        $this->pdo->exec('DROP TABLE IF EXISTS runner_test_table CASCADE');

        $this->testMigrationsDir = sys_get_temp_dir() . '/cds_test_migrations_' . uniqid();
        mkdir($this->testMigrationsDir);
    }

    protected function tearDown(): void
    {
        $this->pdo->exec('DROP TABLE IF EXISTS runner_test_table CASCADE');
        $this->pdo->exec('DROP TABLE IF EXISTS schema_migrations CASCADE');

        array_map('unlink', glob($this->testMigrationsDir . '/*.sql'));
        rmdir($this->testMigrationsDir);
    }

    public function testRunnerCreatesTrackingTable(): void
    {
        $this->runMigrations();

        $result = $this->pdo->query(
            "SELECT EXISTS (SELECT FROM pg_tables WHERE tablename = 'schema_migrations')"
        )->fetchColumn();

        $this->assertTrue((bool) $result);
    }

    public function testRunnerAppliesMigration(): void
    {
        file_put_contents(
            $this->testMigrationsDir . '/001_test.sql',
            'CREATE TABLE runner_test_table (id SERIAL PRIMARY KEY, name TEXT);'
        );

        $output = $this->runMigrations();

        $this->assertStringContainsString('001_test.sql', $output);
        $this->assertStringContainsString('OK', $output);

        $result = $this->pdo->query(
            "SELECT EXISTS (SELECT FROM pg_tables WHERE tablename = 'runner_test_table')"
        )->fetchColumn();
        $this->assertTrue((bool) $result);
    }

    public function testRunnerSkipsApplied(): void
    {
        file_put_contents(
            $this->testMigrationsDir . '/001_test.sql',
            'CREATE TABLE runner_test_table (id SERIAL PRIMARY KEY);'
        );

        $this->runMigrations();
        $output = $this->runMigrations();

        $this->assertStringContainsString('No pending migrations', $output);
    }

    private function runMigrations(): string
    {
        $host = getenv('DB_HOST') ?: 'postgres';
        $port = getenv('DB_PORT') ?: '5432';
        $user = getenv('DB_USER') ?: 'cds';
        $password = getenv('DB_PASSWORD') ?: 'cds_secret';

        $runnerPath = realpath(__DIR__ . '/../../../migrations/runner.php');

        $cmd = sprintf(
            'DB_HOST=%s DB_PORT=%s DB_NAME=cds_test DB_USER=%s DB_PASSWORD=%s php %s %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($password),
            escapeshellarg($runnerPath),
            escapeshellarg($this->testMigrationsDir)
        );

        return shell_exec($cmd) ?? '';
    }
}
