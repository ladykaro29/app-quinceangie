<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Gestor Singleton de Conexión a Base de Datos
 * Compatible con MySQL (Producción/Staging) y SQLite (Desarrollo Local/CI)
 */
class Database
{
    private static ?PDO $instance = null;
    private static string $driver = 'sqlite';

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    public static function getDriver(): string
    {
        return self::$driver;
    }

    private static function connect(): void
    {
        // 1. Cargar variables de entorno si existen
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) continue;
                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, " \t\n\r\0\x0B\"'");
                    if (!array_key_exists($key, $_ENV)) {
                        $_ENV[$key] = $value;
                        putenv("$key=$value");
                    }
                }
            }
        }

        $dbDriver = strtolower(getenv('DB_CONNECTION') ?: getenv('DB_DRIVER') ?: 'auto');
        $sqlitePath = dirname(__DIR__, 2) . '/database/invitacion_xv.sqlite';

        // Intentar MySQL si está explícitamente configurado o si el driver pdo_mysql está presente
        if (in_array($dbDriver, ['mysql', 'mariadb']) || ($dbDriver === 'auto' && in_array('mysql', PDO::getAvailableDrivers()))) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $dbName = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'invitacion_xv';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: getenv('DB_PASSWORD') ?: '';

            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                self::$driver = 'mysql';
                DatabaseMigrationService::ensureMigrated(self::$instance);
                return;
            } catch (PDOException $e) {
                if ($dbDriver === 'mysql') {
                    throw new PDOException("Error de conexión MySQL estricta: " . $e->getMessage());
                }
                // Si estaba en 'auto', continuar con fallback local a SQLite
            }
        }

        // Conexión SQLite
        if (!file_exists(dirname($sqlitePath))) {
            mkdir(dirname($sqlitePath), 0777, true);
        }

        self::$instance = new PDO("sqlite:{$sqlitePath}", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        self::$instance->exec("PRAGMA foreign_keys = ON;");
        self::$driver = 'sqlite';

        // Auto-migración y auto-reparación de esquema multi-evento si es necesario
        DatabaseMigrationService::ensureMigrated(self::$instance);
    }
}
