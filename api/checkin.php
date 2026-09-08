<?php
/**
 * API: Check-in de Invitados con Escaneo de QR (o Código)
 * Invitación Mis XV Años - Angie Karolina
 * 
 * Permite al equipo de recepción / administración escanear el QR del pase VIP
 * o buscar por código de rifa / ID para registrar la asistencia real a la fiesta.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDBConnection();
    ensureTablesExist($pdo);

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?: [];

    // El código QR escaneado o texto enviado
    $rawCode = trim($data['qr_text'] ?? $_POST['qr_text'] ?? $_GET['code'] ?? '');
    // Acción: checkin (marcar asistencia), toggle (cambiar), status (solo consultar)
    $action = trim($data['action'] ?? $_POST['action'] ?? 'checkin');

    if (empty($rawCode)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No se recibió ningún código QR o texto para verificar.']);
        exit;
    }

    $invitado = null;

    // 1. Intentar extraer ID del formato: "XV-ANGIE | PASE #12 | TITULAR: ... | CODIGO: XVANGIE-0012-..."
    if (preg_match('/PASE\s*#(\d+)/i', $rawCode, $matches)) {
        $id = (int)$matches[1];
        $stmt = $pdo->prepare('SELECT id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at FROM invitados WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $invitado = $stmt->fetch();
    }

    // 2. Intentar buscar por código de pase "XVANGIE-0012-..." o ID directo
    if (!$invitado && preg_match('/XVANGIE-(\d+)/i', $rawCode, $matches)) {
        $id = (int)$matches[1];
        $stmt = $pdo->prepare('SELECT id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at FROM invitados WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $invitado = $stmt->fetch();
    }

    // 3. Intentar buscar por código de rifa (ej. "RIFA-4438") en titular
    if (!$invitado && preg_match('/RIFA-(\d+)/i', $rawCode, $matches)) {
        $codigoRifa = 'RIFA-' . $matches[1];
        $stmt = $pdo->prepare('SELECT id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at FROM invitados WHERE codigo_rifa = :c LIMIT 1');
        $stmt->execute([':c' => $codigoRifa]);
        $invitado = $stmt->fetch();

        // Si no es titular, puede ser acompañante:
        if (!$invitado) {
            $stmtA = $pdo->prepare('SELECT i.id, i.nombre_completo, i.asistira, i.codigo_rifa, i.asistio_evento, i.checkin_at, i.created_at FROM acompanantes a INNER JOIN invitados i ON a.invitado_id = i.id WHERE a.codigo_rifa = :c LIMIT 1');
            $stmtA->execute([':c' => $codigoRifa]);
            $invitado = $stmtA->fetch();
        }
    }

    // 4. Búsqueda por número de ID directo
    if (!$invitado && is_numeric($rawCode)) {
        $id = (int)$rawCode;
        $stmt = $pdo->prepare('SELECT id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at FROM invitados WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $invitado = $stmt->fetch();
    }

    // 5. Búsqueda por coincidencia exacta de nombre
    if (!$invitado) {
        $stmt = $pdo->prepare('SELECT id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at FROM invitados WHERE LOWER(TRIM(nombre_completo)) = LOWER(TRIM(:n)) LIMIT 1');
        $stmt->execute([':n' => $rawCode]);
        $invitado = $stmt->fetch();
    }

    if (!$invitado) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error'   => 'Pase no encontrado o código no válido.',
            'raw_code'=> $rawCode
        ]);
        exit;
    }

    // Obtener acompañantes asociados
    $stmtAcomp = $pdo->prepare('SELECT nombre_completo, codigo_rifa FROM acompanantes WHERE invitado_id = :id ORDER BY id ASC');
    $stmtAcomp->execute([':id' => $invitado['id']]);
    $acompanantes = $stmtAcomp->fetchAll();

    $totalPersonas = (int)$invitado['asistira'] ? (1 + count($acompanantes)) : 0;

    // Procesar acción
    $yaAsistio = (bool)$invitado['asistio_evento'];
    $nuevoEstado = $yaAsistio;

    if ($action === 'checkin') {
        // Registrar check-in si no estaba registrado
        $nuevoEstado = true;
        $stmtUpd = $pdo->prepare("UPDATE invitados SET asistio_evento = 1, checkin_at = COALESCE(checkin_at, CURRENT_TIMESTAMP) WHERE id = :id");
        $stmtUpd->execute([':id' => $invitado['id']]);
    } elseif ($action === 'toggle') {
        // Alternar
        $nuevoEstado = !$yaAsistio;
        $checkinTime = $nuevoEstado ? date('Y-m-d H:i:s') : null;
        $stmtUpd = $pdo->prepare("UPDATE invitados SET asistio_evento = :st, checkin_at = :ca WHERE id = :id");
        $stmtUpd->execute([
            ':st' => $nuevoEstado ? 1 : 0,
            ':ca' => $checkinTime,
            ':id' => $invitado['id']
        ]);
    }

    // Volver a consultar fecha de checkin actualizada
    $stmtRef = $pdo->prepare('SELECT asistio_evento, checkin_at FROM invitados WHERE id = :id');
    $stmtRef->execute([':id' => $invitado['id']]);
    $currentSt = $stmtRef->fetch();

    // Estadísticas actualizadas para el dashboard
    $stmtStats = $pdo->query('SELECT 
        (SELECT COUNT(*) FROM invitados WHERE asistio_evento = 1) AS total_titulares_presentes,
        (SELECT COUNT(*) FROM invitados WHERE asistira = 1) AS total_confirmados
    ');
    $stats = $stmtStats->fetch();

    echo json_encode([
        'success'           => true,
        'invitado'          => [
            'id'              => (int)$invitado['id'],
            'nombre_completo' => $invitado['nombre_completo'],
            'confirmado'      => (bool)$invitado['asistira'],
            'asistio_evento'  => (bool)$currentSt['asistio_evento'],
            'checkin_at'      => $currentSt['checkin_at'],
            'codigo_rifa'     => $invitado['codigo_rifa'],
            'acompanantes'    => $acompanantes,
            'total_personas'  => $totalPersonas
        ],
        'already_checked'   => $yaAsistio && ($action === 'checkin'),
        'total_presentes'   => (int)($stats['total_titulares_presentes'] ?? 0),
        'total_confirmados' => (int)($stats['total_confirmados'] ?? 0)
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al validar pase: ' . $e->getMessage()]);
}
