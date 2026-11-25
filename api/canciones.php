<?php
/**
 * API: Sugerir canciones para la fiesta
 * Plataforma: XV Experience — Multi-Celebración Segura
 * 
 * Endpoints:
 * GET  api/canciones.php  -> Lista las canciones sugeridas del evento activo
 * POST api/canciones.php  -> Registra una nueva sugerencia asociada a su evento_id
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
require_once __DIR__ . '/../app/autoload.php';

use App\Core\CurrentEventContext;
use App\Middleware\TenantResolverMiddleware;

try {
    $pdo = getDBConnection();
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al conectar con la base de datos']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Resolver celebración activa
$requestedEvent = $_GET['evento'] ?? $_POST['evento'] ?? null;
if (!$requestedEvent && isset($_GET['evento_id'])) {
    $requestedEventId = (int)$_GET['evento_id'];
    $stmtSlug = $pdo->prepare('SELECT slug FROM eventos WHERE id = :id LIMIT 1');
    $stmtSlug->execute([':id' => $requestedEventId]);
    $requestedEvent = $stmtSlug->fetchColumn() ?: null;
}

if (!CurrentEventContext::isLoaded()) {
    TenantResolverMiddleware::resolve($requestedEvent);
}

$eventoId = CurrentEventContext::id() ?: 1;

// ============================================================================
// 1. LISTAR CANCIONES (GET)
// ============================================================================
if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT id, cancion, nombre_invitado, created_at FROM canciones WHERE evento_id = :evento_id ORDER BY id DESC LIMIT 50');
    $stmt->execute([':evento_id' => $eventoId]);
    $canciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($canciones as &$c) {
        $c['cancion'] = htmlspecialchars($c['cancion'], ENT_QUOTES, 'UTF-8');
        $c['nombre_invitado'] = htmlspecialchars($c['nombre_invitado'] ?? 'Invitado', ENT_QUOTES, 'UTF-8');
    }

    echo json_encode([
        'success'   => true,
        'evento_id' => $eventoId,
        'canciones' => $canciones
    ]);
    exit;
}

// ============================================================================
// 2. SUGERIR CANCIÓN (POST)
// ============================================================================
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    // Verificar si en el body viene un evento específico
    if (!empty($data['evento']) || !empty($data['evento_id'])) {
        $postEvent = $data['evento'] ?? null;
        if (!$postEvent && !empty($data['evento_id'])) {
            $stmtSlug = $pdo->prepare('SELECT slug FROM eventos WHERE id = :id LIMIT 1');
            $stmtSlug->execute([':id' => (int)$data['evento_id']]);
            $postEvent = $stmtSlug->fetchColumn() ?: null;
        }
        if ($postEvent) {
            TenantResolverMiddleware::resolve($postEvent);
            $eventoId = CurrentEventContext::id() ?: $eventoId;
        }
    }

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

    // Evitar sugerir la misma canción exactamente si ya existe en la misma celebración
    $stmtCheck = $pdo->prepare('SELECT id FROM canciones WHERE evento_id = :evento_id AND LOWER(TRIM(cancion)) = LOWER(:c) LIMIT 1');
    $stmtCheck->execute([
        ':evento_id' => $eventoId,
        ':c'         => $cancion
    ]);
    if ($stmtCheck->fetch()) {
        echo json_encode([
            'success' => true,
            'message' => '¡Esa canción ya está en la lista de reproducción de la fiesta! 🎶'
        ]);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO canciones (evento_id, cancion, nombre_invitado, ip, created_at) VALUES (:evento_id, :cancion, :nombre, :ip, :created_at)');
    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        ':evento_id'   => $eventoId,
        ':cancion'     => $cancion,
        ':nombre'      => $nombre ?: null,
        ':ip'          => $ip,
        ':created_at'  => $now
    ]);

    echo json_encode([
        'success' => true,
        'message' => '¡Canción sugerida con éxito! Se tendrá en cuenta para la fiesta. 🎵',
        'cancion' => [
            'id'              => (int)$pdo->lastInsertId(),
            'evento_id'       => $eventoId,
            'cancion'         => htmlspecialchars($cancion, ENT_QUOTES, 'UTF-8'),
            'nombre_invitado' => htmlspecialchars($nombre ?: 'Invitado', ENT_QUOTES, 'UTF-8'),
            'created_at'      => $now
        ]
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Método no permitido']);
