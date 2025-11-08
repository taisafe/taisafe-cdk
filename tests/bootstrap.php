<?php
declare(strict_types=1);

// Shared test bootstrap (T003) – initializes env config, autoloader and optional DB fixtures.
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('PROJECT_ROOT', dirname(__DIR__));

/**
 * @return array<string,mixed>
 */
function loadConfig(): array
{
    $configFile = PROJECT_ROOT . '/config/env.php';
    if (! file_exists($configFile)) {
        $configFile = PROJECT_ROOT . '/config/env.example.php';
    }
    return require $configFile;
}

$config = loadConfig();

spl_autoload_register(static function (string $class): void {
    $baseDir = PROJECT_ROOT . '/src/';
    $path = $baseDir . str_replace('\\', '/', $class) . '.php';
    if (is_file($path)) {
        require_once $path;
        return;
    }

    $prefix = 'Taisafe\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $segments = explode('\\', $relative);
        $file = array_pop($segments) . '.php';
        $dir = $segments ? implode('/', array_map('strtolower', $segments)) . '/' : '';
        $altPath = $baseDir . $dir . $file;
        if (is_file($altPath)) {
            require_once $altPath;
        }
    }
});

/**
 * Executes database/setup.sql if PDO connection is available.
 */
function bootstrapTestDatabase(array $config): void
{
    if (! isset($config['db'])) {
        return;
    }
    try {
        $db = $config['db'];
        $charset = $db['charset'] ?? 'utf8mb4';
        $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $db['host'], $db['port'], $charset);
        $pdo = new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $database = $db['database'];
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $database . '` CHARACTER SET ' . $charset);
        $pdo->exec('USE `' . $database . '`');
        $schema = @file_get_contents(PROJECT_ROOT . '/database/setup.sql');
        if ($schema !== false) {
            foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
                if ($statement !== '') {
                    $pdo->exec($statement);
                }
            }
        }
    } catch (\Throwable $e) {
        error_log("[tests/bootstrap] DB bootstrap skipped: {$e->getMessage()}");
    }
}

bootstrapTestDatabase($config);
