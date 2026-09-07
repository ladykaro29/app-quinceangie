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

// Configuración de la base de datos (detecta variables estándar de Easypanel, Docker, cPanel y Laravel)
$dbHost = getenv('DB_HOST') ?: getenv('DATABASE_HOST') ?: getenv('MYSQL_HOST') ?: getenv('MARIADB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: getenv('DATABASE_PORT') ?: getenv('MYSQL_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: getenv('DATABASE_NAME') ?: getenv('MYSQL_DATABASE') ?: 'invitacion_xv';
$dbUser = getenv('DB_USER') ?: getenv('DATABASE_USER') ?: getenv('MYSQL_USER') ?: getenv('MYSQL_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASS') ?: getenv('DB_PASSWORD') ?: getenv('DATABASE_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';
$dbCharset = 'utf8mb4';

// Credenciales del panel de administración
define('ADMIN_USER', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASS', getenv('ADMIN_PASS') ?: 'angie2026');

// Máximo de acompañantes por invitado
define('MAX_ACOMPANANTES', (int)(getenv('MAX_ACOMPANANTES') ?: 5));

/**
 * Obtiene una conexión PDO a la base de datos
 * Con reintento inteligente de nombres de host comunes en Docker/Easypanel y auto-creación de tablas
 * 
 * @return PDO
 * @throws PDOException
 */
function getDBConnection(): PDO {
    global $dbHost, $dbPort, $dbName, $dbUser, $dbPass, $dbCharset;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Si DB_HOST no fue especificado manualmente en .env, probar candidatos típicos de red interna Docker
    $hostsToTry = [$dbHost];
    if (!getenv('DB_HOST') && !getenv('DATABASE_HOST') && !getenv('MYSQL_HOST')) {
        $hostsToTry = array_unique(array_filter([$dbHost, 'mysql', 'mariadb', 'database', 'db', '127.0.0.1']));
    }

    $lastException = null;
    $pdo = null;

    foreach ($hostsToTry as $candidateHost) {
        try {
            $dsn = "mysql:host={$candidateHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";
            $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
            $dbHost = $candidateHost;
            break;
        } catch (\PDOException $e) {
            $lastException = $e;

            // Si el servidor MySQL responde pero la base de datos aún no existe (código 1049), crearla automáticamente
            if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                try {
                    $dsnNoDb = "mysql:host={$candidateHost};port={$dbPort};charset={$dbCharset}";
                    $pdoRoot = new PDO($dsnNoDb, $dbUser, $dbPass, $options);
                    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo = new PDO("mysql:host={$candidateHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}", $dbUser, $dbPass, $options);
                    $dbHost = $candidateHost;
                    break;
                } catch (\Exception $ex) {
                    $lastException = $ex;
                }
            }
        }
    }

    if (!$pdo) {
        throw $lastException ?: new \PDOException("No se pudo conectar a la base de datos en [{$dbHost}:{$dbPort}].");
    }

    ensureTablesExist($pdo);
    return $pdo;
}

/**
 * Asegura automáticamente que existan las tablas requeridas y columnas de rifa
 */
function ensureTablesExist(PDO $pdo): void {
    static $migrated = false;
    if ($migrated) return;

    try {
        // Crear tabla invitados si no existe
        $pdo->exec("CREATE TABLE IF NOT EXISTS invitados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_completo VARCHAR(150) NOT NULL,
            asistira TINYINT(1) NOT NULL DEFAULT 1,
            codigo_rifa VARCHAR(20) NULL UNIQUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_asistira (asistira),
            INDEX idx_codigo_rifa (codigo_rifa),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Crear tabla acompanantes si no existe
        $pdo->exec("CREATE TABLE IF NOT EXISTS acompanantes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invitado_id INT NOT NULL,
            nombre_completo VARCHAR(150) NOT NULL,
            codigo_rifa VARCHAR(20) NULL UNIQUE,
            INDEX idx_acomp_codigo_rifa (codigo_rifa)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    } catch (\Exception $e) {
        // Ignorar si ya existen
    }

    // Migraciones seguras para columnas de rifa en tablas existentes
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM invitados LIKE 'codigo_rifa'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE invitados ADD COLUMN codigo_rifa VARCHAR(20) NULL UNIQUE AFTER asistira");
        }
    } catch (\Exception $e) {}

    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM acompanantes LIKE 'codigo_rifa'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE acompanantes ADD COLUMN codigo_rifa VARCHAR(20) NULL UNIQUE AFTER nombre_completo");
        }
    } catch (\Exception $e) {}

    $migrated = true;
}

/**
 * Alias de compatibilidad hacia atrás
 */
function ensureRaffleSchema(PDO $pdo): void {
    ensureTablesExist($pdo);
}

/**
 * Genera un código único aleatorio para la rifa de regalos (ej. RIFA-4821)
 */
function generarCodigoRifaUnico(PDO $pdo): string {
    for ($attempt = 0; $attempt < 100; $attempt++) {
        $num = rand(1000, 9999);
        $codigo = 'RIFA-' . $num;

        $stmt = $pdo->prepare('SELECT (
            (SELECT COUNT(*) FROM invitados WHERE codigo_rifa = :c1) +
            (SELECT COUNT(*) FROM acompanantes WHERE codigo_rifa = :c2)
        ) AS total');
        $stmt->execute([':c1' => $codigo, ':c2' => $codigo]);
        if ((int)$stmt->fetchColumn() === 0) {
            return $codigo;
        }
    }
    // Fallback con número mayor en caso de colisión improbable
    return 'RIFA-' . rand(10000, 99999);
}

/**
 * Genera códigos de rifa retroactivamente para registros previos sin código
 */
function populateMissingRaffleCodes(PDO $pdo): void {
    try {
        // Invitados que asistirán sin código de rifa
        $stmt = $pdo->query("SELECT id FROM invitados WHERE asistira = 1 AND (codigo_rifa IS NULL OR codigo_rifa = '')");
        $sinCodigo = $stmt->fetchAll();
        foreach ($sinCodigo as $inv) {
            $codigo = generarCodigoRifaUnico($pdo);
            $upd = $pdo->prepare("UPDATE invitados SET codigo_rifa = :cod WHERE id = :id");
            $upd->execute([':cod' => $codigo, ':id' => $inv['id']]);
        }

        // Acompañantes de invitados confirmados sin código de rifa
        $stmtAcomp = $pdo->query("SELECT a.id FROM acompanantes a INNER JOIN invitados i ON a.invitado_id = i.id WHERE i.asistira = 1 AND (a.codigo_rifa IS NULL OR a.codigo_rifa = '')");
        $acompSinCodigo = $stmtAcomp->fetchAll();
        foreach ($acompSinCodigo as $ac) {
            $codigo = generarCodigoRifaUnico($pdo);
            $upd = $pdo->prepare("UPDATE acompanantes SET codigo_rifa = :cod WHERE id = :id");
            $upd->execute([':cod' => $codigo, ':id' => $ac['id']]);
        }
    } catch (\Exception $e) {
        // Silencioso en caso de error
    }
}
