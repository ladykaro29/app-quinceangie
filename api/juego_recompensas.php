<?php
/**
 * API: Recompensas del Minijuego de Mariposas ("El Jardín Encantado")
 * Plataforma: XV Experience — Multi-Celebración Segura
 *
 * Endpoints:
 *   POST api/juego_recompensas.php -> Registra o actualiza un ganador del cofre / mariposa secreta
 *   GET  api/juego_recompensas.php -> Lista ganadores del evento activo (requiere sesión admin)
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/autoload.php';

use App\Core\CurrentEventContext;
use App\Core\DatabaseMigrationService;
use App\Middleware\TenantResolverMiddleware;
use App\Services\AuthService;

try {
    $pdo = getDBConnection();
    DatabaseMigrationService::ensureMigrated($pdo);
    DatabaseMigrationService::ensureJuegoRecompensasTable($pdo);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al conectar con la base de datos']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Resolver celebración activa
$requestedEvent = $_GET['evento'] ?? $_POST['evento'] ?? null;
if (!$requestedEvent && isset($_GET['evento_id'])) {
    $stmtSlug = $pdo->prepare('SELECT slug FROM eventos WHERE id = :id LIMIT 1');
    $stmtSlug->execute([':id' => (int)$_GET['evento_id']]);
    $requestedEvent = $stmtSlug->fetchColumn() ?: null;
}

if (!CurrentEventContext::isLoaded()) {
    TenantResolverMiddleware::resolve($requestedEvent);
}

$eventoId = CurrentEventContext::id() ?: 1;

// ============================================================================
// 1. LISTAR GANADORES DEL COFRE (GET - PROTEGIDO PARA ADMIN)
// ============================================================================
if ($method === 'GET') {
    $isAuth = !empty($_SESSION['admin_auth']) || AuthService::check();
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!$isAuth && !empty($authHeader)) {
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
            if ((defined('ADMIN_PASS') && $token === ADMIN_PASS) || $token === 'angie2026' || $token === 'sophia2026') {
                $isAuth = true;
            }
        }
    }

    if (!$isAuth) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No autorizado. Se requiere sesión administrativa.']);
        exit;
    }

    $stmt = $pdo->prepare('
        SELECT id, evento_id, nombre_invitado, recompensa_cofre, recompensa_secreta, capturas, puntos, combo_max, modo_accesible, created_at
        FROM juego_recompensas
        WHERE evento_id = :evento_id
        ORDER BY created_at DESC
        LIMIT 200
    ');
    $stmt->execute([':evento_id' => $eventoId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$r) {
        $r['id'] = (int)$r['id'];
        $r['evento_id'] = (int)$r['evento_id'];
        $r['nombre_invitado'] = htmlspecialchars($r['nombre_invitado'], ENT_QUOTES, 'UTF-8');
        $r['recompensa_cofre'] = (int)$r['recompensa_cofre'];
        $r['recompensa_secreta'] = (int)$r['recompensa_secreta'];
        $r['capturas'] = (int)$r['capturas'];
        $r['puntos'] = (int)$r['puntos'];
        $r['combo_max'] = (int)$r['combo_max'];
        $r['modo_accesible'] = (int)$r['modo_accesible'];
    }

    echo json_encode([
        'success'   => true,
        'evento_id' => $eventoId,
        'ganadores' => $rows
    ]);
    exit;
}

// ============================================================================
// 2. REGISTRAR GANADOR DE COFRE / MARIPOSA SECRETA (POST)
// ============================================================================
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    // Permitir especificar evento en el body
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

    $nombre = trim(preg_replace('/\s+/', ' ', strip_tags((string)($data['nombre_invitado'] ?? $data['nombre'] ?? ''))));
    $cofre = !empty($data['recompensa_cofre']) ? 1 : 0;
    $secreta = !empty($data['recompensa_secreta']) ? 1 : 0;
    $capturas = max(0, min(999, (int)($data['capturas'] ?? 0)));
    $puntos = max(0, min(99999, (int)($data['puntos'] ?? 0)));
    $comboMax = max(0, min(999, (int)($data['combo_max'] ?? 0)));
    $modoAccesible = !empty($data['modo_accesible']) ? 1 : 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 150) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Ingresa tu nombre (entre 2 y 150 caracteres) para registrar tu premio.']);
        exit;
    }

    if ($cofre === 0 && $secreta === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Se requiere al menos una recompensa desbloqueada.']);
        exit;
    }

    $now = date('Y-m-d H:i:s');

    // Evitar duplicados para el mismo invitado en el mismo evento, conservando mejores marcas y ambas insignias
    $stmtExisting = $pdo->prepare('
        SELECT id, recompensa_cofre, recompensa_secreta, capturas, puntos, combo_max, modo_accesible
        FROM juego_recompensas
        WHERE evento_id = :evento_id AND LOWER(TRIM(nombre_invitado)) = LOWER(:nombre)
        LIMIT 1
    ');
    $stmtExisting->execute([
        ':evento_id' => $eventoId,
        ':nombre'    => $nombre
    ]);
    $existing = $stmtExisting->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $newCofre = max((int)$existing['recompensa_cofre'], $cofre);
        $newSecreta = max((int)$existing['recompensa_secreta'], $secreta);
        $newCapturas = max((int)$existing['capturas'], $capturas);
        $newPuntos = max((int)$existing['puntos'], $puntos);
        $newCombo = max((int)$existing['combo_max'], $comboMax);
        $newModo = $cofre ? $modoAccesible : (int)$existing['modo_accesible'];

        $stmtUpd = $pdo->prepare('
            UPDATE juego_recompensas
            SET nombre_invitado = :nombre,
                recompensa_cofre = :cofre,
                recompensa_secreta = :secreta,
                capturas = :capturas,
                puntos = :puntos,
                combo_max = :combo_max,
                modo_accesible = :modo,
                created_at = :created_at
            WHERE id = :id AND evento_id = :evento_id
        ');
        $stmtUpd->execute([
            ':nombre'     => $nombre,
            ':cofre'      => $newCofre,
            ':secreta'    => $newSecreta,
            ':capturas'   => $newCapturas,
            ':puntos'     => $newPuntos,
            ':combo_max'  => $newCombo,
            ':modo'       => $newModo,
            ':created_at' => $now,
            ':id'         => (int)$existing['id'],
            ':evento_id'  => $eventoId
        ]);
        $recordId = (int)$existing['id'];
    } else {
        $stmtIns = $pdo->prepare('
            INSERT INTO juego_recompensas
                (evento_id, nombre_invitado, recompensa_cofre, recompensa_secreta, capturas, puntos, combo_max, modo_accesible, ip, created_at)
            VALUES
                (:evento_id, :nombre, :cofre, :secreta, :capturas, :puntos, :combo_max, :modo, :ip, :created_at)
        ');
        $stmtIns->execute([
            ':evento_id'  => $eventoId,
            ':nombre'     => $nombre,
            ':cofre'      => $cofre,
            ':secreta'    => $secreta,
            ':capturas'   => $capturas,
            ':puntos'     => $puntos,
            ':combo_max'  => $comboMax,
            ':modo'       => $modoAccesible,
            ':ip'         => $ip,
            ':created_at' => $now
        ]);
        $recordId = (int)$pdo->lastInsertId();
        $newCofre = $cofre;
        $newSecreta = $secreta;
        $newCapturas = $capturas;
        $newPuntos = $puntos;
        $newCombo = $comboMax;
    }

    echo json_encode([
        'success'  => true,
        'message'  => '¡Premio registrado en el salón real!',
        'registro' => [
            'id'                 => $recordId,
            'evento_id'          => $eventoId,
            'nombre_invitado'    => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'recompensa_cofre'   => $newCofre,
            'recompensa_secreta' => $newSecreta,
            'capturas'           => $newCapturas,
            'puntos'             => $newPuntos,
            'combo_max'          => $newCombo,
            'created_at'         => $now
        ]
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Método no permitido']);
