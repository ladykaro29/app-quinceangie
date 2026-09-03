<?php
/**
 * Configuración de conexión a la base de datos
 * Invitación XV Años - Angie Karolina Avendaño Rivera
 * 
 * Usa variables de entorno para las credenciales.
 * En desarrollo local (XAMPP/Laragon), puedes definirlas en un archivo .env
 * o directamente en la configuración de tu servidor.
 */

// Cargar .env si existe (para desarrollo local)
$envFile = __DIR__ . '/../.env';
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

// Configuración de la base de datos
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'invitacion_xv';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbCharset = 'utf8mb4';

// Credenciales del panel de administración
define('ADMIN_USER', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASS', getenv('ADMIN_PASS') ?: 'angie2026');

// Máximo de acompañantes por invitado
define('MAX_ACOMPANANTES', (int)(getenv('MAX_ACOMPANANTES') ?: 5));

/**
 * Obtiene una conexión PDO a la base de datos
 * 
 * @return PDO
 * @throws PDOException
 */
function getDBConnection(): PDO {
    global $dbHost, $dbPort, $dbName, $dbUser, $dbPass, $dbCharset;

    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $dbUser, $dbPass, $options);
}
