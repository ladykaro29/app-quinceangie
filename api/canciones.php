<?php
/**
 * API: Sugerir canciones para la fiesta
 * Invitación XV Años - Angie Karolina
 * 
 * Endpoints:
 * GET  api/canciones.php  -> Lista las canciones sugeridas
 * POST api/canciones.php  -> Registra una nueva sugerencia { "cancion": "...", "nombre": "..." }
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDBConnection();
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al conectar con la base de datos']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ============================================================================
// 1. LISTAR CANCIONES (GET)
// ============================================================================
if ($method === 'GET') {
    $stmt = $pdo->query('SELECT id, cancion, nombre_invitado, created_at FROM canciones ORDER BY id DESC LIMIT 50');
    $canciones = $stmt->fetchAll();

    foreach ($canciones as &$c) {
        $c['cancion'] = htmlspecialchars($c['cancion'], ENT_QUOTES, 'UTF-8');
        $c['nombre_invitado'] = htmlspecialchars($c['nombre_invitado'] ?? 'Invitado', ENT_QUOTES, 'UTF-8');
    }

    echo json_encode(['success' => true, 'canciones' => $canciones]);
    exit;
}

// ============================================================================
// 2. SUGERIR CANCIÓN (POST)
// ============================================================================
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $cancion = trim($data['cancion'] ?? '');
    $nombre = trim($data['nombre_invitado'] ?? $data['nombre'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($cancion)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Por favor escribe el nombre de la canción y artista.']);
        exit;
    }

    if (mb_strlen($cancion) > 255) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El nombre de la canción es muy largo.']);
        exit;
    }

    // Evitar sugerir la misma canción exactamente si ya existe recientemente
    $stmtCheck = $pdo->prepare('SELECT id FROM canciones WHERE LOWER(TRIM(cancion)) = LOWER(:c) LIMIT 1');
    $stmtCheck->execute([':c' => $cancion]);
    if ($stmtCheck->fetch()) {
        echo json_encode([
            'success' => true,
            'message' => '¡Esa canción ya está en la lista de reproducción de la fiesta! 🎶'
        ]);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO canciones (cancion, nombre_invitado, ip, created_at) VALUES (:cancion, :nombre, :ip, :created_at)');
    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        ':cancion'    => $cancion,
        ':nombre'     => $nombre ?: null,
        ':ip'         => $ip,
        ':created_at' => $now
    ]);

    echo json_encode([
        'success' => true,
        'message' => '¡Canción sugerida con éxito! Se tendrá en cuenta para la fiesta. 🎵',
        'cancion' => [
            'id'              => (int)$pdo->lastInsertId(),
            'cancion'         => htmlspecialchars($cancion, ENT_QUOTES, 'UTF-8'),
            'nombre_invitado' => htmlspecialchars($nombre ?: 'Invitado', ENT_QUOTES, 'UTF-8'),
            'created_at'      => $now
        ]
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Método no permitido']);
