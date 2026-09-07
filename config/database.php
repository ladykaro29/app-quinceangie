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

    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
    ensureRaffleSchema($pdo);
    return $pdo;
}

/**
 * Asegura automáticamente que existan las columnas de código de rifa
 */
function ensureRaffleSchema(PDO $pdo): void {
    static $migrated = false;
    if ($migrated) return;

    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM invitados LIKE 'codigo_rifa'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE invitados ADD COLUMN codigo_rifa VARCHAR(20) NULL UNIQUE AFTER asistira");
        }
    } catch (\Exception $e) {
        // Ignorar si no se puede alterar o ya existe
    }

    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM acompanantes LIKE 'codigo_rifa'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE acompanantes ADD COLUMN codigo_rifa VARCHAR(20) NULL UNIQUE AFTER nombre_completo");
        }
    } catch (\Exception $e) {
        // Ignorar si no se puede alterar o ya existe
    }

    $migrated = true;
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
