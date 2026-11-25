<?php
/**
 * API: Check-in de Invitados con Escaneo de QR y Prevención de Uso Cruzado
 * Plataforma: XV Experience — Multi-Celebración Segura
 * 
 * Permite al equipo de recepción / administración escanear el QR del pase VIP
 * o buscar por código de rifa / ID para registrar la asistencia real a la fiesta.
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/autoload.php';

use App\Core\CurrentEventContext;
use App\Middleware\TenantResolverMiddleware;
use App\Services\AuthService;

// ==============================================================================
// 1. AUTENTICACIÓN Y AUTORIZACIÓN DEL OPERADOR DE CHECK-IN
// ==============================================================================

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: [];

$isAuth = false;

// Verificación por sesión administrativa u operador autenticado
if (!empty($_SESSION['admin_auth']) || AuthService::check()) {
    $isAuth = true;
}

// Verificación por encabezado Authorization Bearer
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!$isAuth && !empty($authHeader)) {
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = trim($matches[1]);
        if ($token === ADMIN_PASS || $token === 'angie2026' || $token === 'sophia2026') {
            $isAuth = true;
        }
    }
}

// Verificación por clave de operador enviada en la petición
$operatorKey = $data['operator_key'] ?? $_POST['operator_key'] ?? $_GET['operator_key'] ?? '';
if (!$isAuth && !empty($operatorKey)) {
    if ($operatorKey === ADMIN_PASS || $operatorKey === 'angie2026' || $operatorKey === 'sophia2026') {
        $isAuth = true;
    }
}

if (!$isAuth) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error'   => 'Acceso no autorizado. Se requiere autenticación de operador de recepción para realizar check-in.'
    ]);
    exit;
}

// ==============================================================================
// 2. RESOLUCIÓN DE LA CELEBRACIÓN ACTIVA (CurrentEventContext)
// ==============================================================================

$requestedEvent = $data['evento'] ?? $_POST['evento'] ?? $_GET['evento'] ?? null;
if (!$requestedEvent) {
    $requestedEventId = $data['evento_id'] ?? $_POST['evento_id'] ?? $_GET['evento_id'] ?? null;
    if ($requestedEventId) {
        $pdoTemp = getDBConnection();
        $stmtSlug = $pdoTemp->prepare('SELECT slug FROM eventos WHERE id = :id LIMIT 1');
        $stmtSlug->execute([':id' => (int)$requestedEventId]);
        $requestedEvent = $stmtSlug->fetchColumn() ?: null;
    }
}

if (!CurrentEventContext::isLoaded()) {
    if (!TenantResolverMiddleware::resolve($requestedEvent)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error'   => 'Celebración no encontrada o evento inválido para el check-in.'
        ]);
        exit;
    }
}

$eventoId = CurrentEventContext::id();
$eventoSlug = CurrentEventContext::slug();
$eventoTitulo = CurrentEventContext::get()['nombre_celebracion'] ?? 'Celebración';

if (!$eventoId || $eventoId <= 0) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error'   => 'No se pudo identificar la celebración activa.'
    ]);
    exit;
}

// ==============================================================================
// 3. EXTRACCIÓN Y DETECCIÓN DE PREFIJOS DE PASE
// ==============================================================================

$rawCode = trim($data['qr_text'] ?? $_POST['qr_text'] ?? $_GET['code'] ?? '');
$action = trim($data['action'] ?? $_POST['action'] ?? 'checkin');

if (empty($rawCode)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No se recibió ningún código QR o texto para verificar.']);
    exit;
}

try {
    $pdo = getDBConnection();
    ensureTablesExist($pdo);

    $candidateGuest = null;
    $foreignEventDetected = null;

    // Detectar si el texto contiene prefijos explícitos de otra celebración
    if (preg_match('/XV-?ANGIE/i', $rawCode) && $eventoId !== 1) {
        $foreignEventDetected = 'Angie Karolina (ID 1)';
    } elseif (preg_match('/XV-?SOPHIA/i', $rawCode) && $eventoId !== 2) {
        $foreignEventDetected = 'Sophia Valentina (ID 2)';
    }

    if ($foreignEventDetected) {
        http_response_code(403);
        echo json_encode([
            'success'       => false,
            'error'         => "Pase no válido para esta celebración. Este pase pertenece a la celebración de {$foreignEventDetected}.",
            'cross_event'   => true,
            'evento_actual' => $eventoId,
            'evento_nombre' => $eventoTitulo
        ]);
        exit;
    }

    // 1. Intentar extraer ID del formato: "XV-XXX | PASE #12 | TITULAR: ... | CODIGO: ..."
    if (preg_match('/PASE\s*#(\d+)/i', $rawCode, $matches)) {
        $id = (int)$matches[1];
        $stmt = $pdo->prepare('SELECT id, evento_id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at FROM invitados WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $candidateGuest = $stmt->fetch();
    }

    // 2. Búsqueda por prefijo dinámico o conocido (XVANGIE-0012, XVSOPHIA-0012, etc.)
    if (!$candidateGuest && preg_match('/(?:XV-?[A-Z0-9]+)-(\d+)/i', $rawCode, $matches)) {
        $id = (int)$matches[1];
        $stmt = $pdo->prepare('SELECT id, evento_id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at FROM invitados WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $candidateGuest = $stmt->fetch();
    }

    // 3. Búsqueda por código de rifa (ej. "RIFA-4438")
    if (!$candidateGuest && preg_match('/RIFA-(\d+)/i', $rawCode, $matches)) {
        $codigoRifa = 'RIFA-' . $matches[1];
        $stmt = $pdo->prepare('SELECT id, evento_id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at FROM invitados WHERE codigo_rifa = :c LIMIT 1');
        $stmt->execute([':c' => $codigoRifa]);
        $candidateGuest = $stmt->fetch();

        // Si no es titular, buscar en acompañantes
        if (!$candidateGuest) {
            $stmtA = $pdo->prepare('SELECT i.id, i.evento_id, i.nombre_completo, i.asistira, i.codigo_rifa, i.asistio_evento, i.checkin_at, i.created_at FROM acompanantes a INNER JOIN invitados i ON a.invitado_id = i.id WHERE a.codigo_rifa = :c LIMIT 1');
            $stmtA->execute([':c' => $codigoRifa]);
            $candidateGuest = $stmtA->fetch();
        }
    }

    // 4. Búsqueda por ID numérico directo
    if (!$candidateGuest && is_numeric($rawCode)) {
        $id = (int)$rawCode;
        $stmt = $pdo->prepare('SELECT id, evento_id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at FROM invitados WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $candidateGuest = $stmt->fetch();
    }

    // 5. Búsqueda por coincidencia exacta de nombre
    if (!$candidateGuest) {
        $stmt = $pdo->prepare('SELECT id, evento_id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at FROM invitados WHERE LOWER(TRIM(nombre_completo)) = LOWER(TRIM(:n)) LIMIT 1');
        $stmt->execute([':n' => $rawCode]);
        $candidateGuest = $stmt->fetch();
    }

    if (!$candidateGuest) {
        http_response_code(404);
        echo json_encode([
            'success'  => false,
            'error'    => 'Pase no encontrado o código no registrado.',
            'raw_code' => $rawCode
        ]);
        exit;
    }

    // ==============================================================================
    // 4. VALIDACIÓN ESTRICTA DE PERTENENCIA AL EVENTO ACTIVO (Anti-Uso Cruzado)
    // ==============================================================================
    $guestEventId = (int)($candidateGuest['evento_id'] ?? 1);
    if ($guestEventId !== $eventoId) {
        http_response_code(403);
        echo json_encode([
            'success'       => false,
            'error'         => "Pase no válido para esta celebración. El pase pertenece a la celebración ID {$guestEventId} y el evento activo es {$eventoTitulo} (ID {$eventoId}).",
            'cross_event'   => true,
            'pase_evento'   => $guestEventId,
            'evento_actual' => $eventoId
        ]);
        exit;
    }

    // Obtener acompañantes asociados del mismo evento
    $stmtAcomp = $pdo->prepare('SELECT nombre_completo, codigo_rifa FROM acompanantes WHERE invitado_id = :id ORDER BY id ASC');
    $stmtAcomp->execute([':id' => $candidateGuest['id']]);
    $acompanantes = $stmtAcomp->fetchAll(PDO::FETCH_ASSOC);

    $totalPersonas = (int)$candidateGuest['asistira'] ? (1 + count($acompanantes)) : 0;
    $yaAsistio = (bool)$candidateGuest['asistio_evento'];
    $nuevoEstado = $yaAsistio;

    // ==============================================================================
    // 5. CONTROL DE REGISTRO DUPLICADO Y ACCIONES
    // ==============================================================================
    if ($action === 'checkin') {
        if ($yaAsistio) {
            // El invitado ya había ingresado previamente; no sobrescribir timestamp
            $stmtStats = $pdo->prepare('SELECT 
                (SELECT COUNT(*) FROM invitados WHERE evento_id = :ev1 AND asistio_evento = 1) AS total_titulares_presentes,
                (SELECT COUNT(*) FROM invitados WHERE evento_id = :ev2 AND asistira = 1) AS total_confirmados
            ');
            $stmtStats->execute([':ev1' => $eventoId, ':ev2' => $eventoId]);
            $stats = $stmtStats->fetch();

            echo json_encode([
                'success'           => true,
                'already_checked'   => true,
                'message'           => "¡Atención! Este pase ya fue utilizado previamente a las {$candidateGuest['checkin_at']}.",
                'invitado'          => [
                    'id'              => (int)$candidateGuest['id'],
                    'nombre_completo' => $candidateGuest['nombre_completo'],
                    'confirmado'      => (bool)$candidateGuest['asistira'],
                    'asistio_evento'  => true,
                    'checkin_at'      => $candidateGuest['checkin_at'],
                    'codigo_rifa'     => $candidateGuest['codigo_rifa'],
                    'acompanantes'    => $acompanantes,
                    'total_personas'  => $totalPersonas
                ],
                'total_presentes'   => (int)($stats['total_titulares_presentes'] ?? 0),
                'total_confirmados' => (int)($stats['total_confirmados'] ?? 0)
            ]);
            exit;
        }

        // Marcar primer ingreso
        $nuevoEstado = true;
        $now = date('Y-m-d H:i:s');
        $stmtUpd = $pdo->prepare("UPDATE invitados SET asistio_evento = 1, checkin_at = :now WHERE id = :id AND evento_id = :ev");
        $stmtUpd->execute([
            ':now' => $now,
            ':id'  => $candidateGuest['id'],
            ':ev'  => $eventoId
        ]);
        $candidateGuest['checkin_at'] = $now;

    } elseif ($action === 'toggle') {
        $nuevoEstado = !$yaAsistio;
        $checkinTime = $nuevoEstado ? date('Y-m-d H:i:s') : null;
        $stmtUpd = $pdo->prepare("UPDATE invitados SET asistio_evento = :st, checkin_at = :ca WHERE id = :id AND evento_id = :ev");
        $stmtUpd->execute([
            ':st' => $nuevoEstado ? 1 : 0,
            ':ca' => $checkinTime,
            ':id' => $candidateGuest['id'],
            ':ev' => $eventoId
        ]);
        $candidateGuest['checkin_at'] = $checkinTime;
    }

    // Estadísticas del evento activo
    $stmtStats = $pdo->prepare('SELECT 
        (SELECT COUNT(*) FROM invitados WHERE evento_id = :ev1 AND asistio_evento = 1) AS total_titulares_presentes,
        (SELECT COUNT(*) FROM invitados WHERE evento_id = :ev2 AND asistira = 1) AS total_confirmados
    ');
    $stmtStats->execute([':ev1' => $eventoId, ':ev2' => $eventoId]);
    $stats = $stmtStats->fetch();

    echo json_encode([
        'success'           => true,
        'already_checked'   => false,
        'message'           => '¡Ingreso verificado exitosamente! Bienvenido(a) a la celebración.',
        'invitado'          => [
            'id'              => (int)$candidateGuest['id'],
            'evento_id'       => $eventoId,
            'nombre_completo' => $candidateGuest['nombre_completo'],
            'confirmado'      => (bool)$candidateGuest['asistira'],
            'asistio_evento'  => (bool)$nuevoEstado,
            'checkin_at'      => $candidateGuest['checkin_at'],
            'codigo_rifa'     => $candidateGuest['codigo_rifa'],
            'acompanantes'    => $acompanantes,
            'total_personas'  => $totalPersonas
        ],
        'total_presentes'   => (int)($stats['total_titulares_presentes'] ?? 0),
        'total_confirmados' => (int)($stats['total_confirmados'] ?? 0)
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al validar pase: ' . $e->getMessage()]);
}
