<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: 'postgres';
$port = getenv('DB_PORT') ?: '5432';
$dbName = getenv('DB_NAME') ?: 'cds';
$user = getenv('DB_USER') ?: 'cds';
$password = getenv('DB_PASSWORD') ?: 'cds_secret';

$dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbName);
$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec('
    CREATE TABLE IF NOT EXISTS schema_migrations (
        filename VARCHAR(255) PRIMARY KEY,
        applied_at TIMESTAMPTZ NOT NULL DEFAULT now()
    )
');

$applied = $pdo->query('SELECT filename FROM schema_migrations ORDER BY filename')
    ->fetchAll(PDO::FETCH_COLUMN);

$dir = $argv[1] ?? __DIR__;
$files = glob($dir . '/*.sql');
sort($files);

$pending = array_filter($files, function (string $file) use ($applied): bool {
    return !in_array(basename($file), $applied, true);
});

if (empty($pending)) {
    echo "No pending migrations.\n";
    exit(0);
}

foreach ($pending as $file) {
    $name = basename($file);
    $sql = file_get_contents($file);

    if ($sql === false) {
        fprintf(STDERR, "Failed to read %s\n", $name);
        exit(1);
    }

    echo "Applying {$name}... ";

    $pdo->beginTransaction();
    try {
        $pdo->exec($sql);
        $stmt = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (:filename)');
        $stmt->execute(['filename' => $name]);
        $pdo->commit();
        echo "OK\n";
    } catch (Throwable $e) {
        $pdo->rollBack();
        fprintf(STDERR, "FAILED: %s\n", $e->getMessage());
        exit(1);
    }
}

echo sprintf("Applied %d migration(s).\n", count($pending));
