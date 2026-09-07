<?php
/**
 * API: Confirmar Asistencia (POST)
 * Invitación XV Años - Angie Karolina Avendaño Rivera
 * 
 * Recibe JSON:
 * {
 *   "nombre_completo": "Nombre del invitado",
 *   "asistira": true|false,
 *   "acompanantes": ["Nombre 1", "Nombre 2"]
 * }
 * 
 * Responde JSON:
 * { "success": true, "total_confirmados": N }
 * o
 * { "success": false, "error": "mensaje" }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido. Use POST.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    // Leer y decodificar el JSON del cuerpo
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'JSON inválido.']);
        exit;
    }

    // Validar nombre_completo
    $nombreCompleto = trim($data['nombre_completo'] ?? '');
    if ($nombreCompleto === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El nombre completo es obligatorio.']);
        exit;
    }

    // Validar que solo contenga letras, espacios, tildes y caracteres latinos
    if (!preg_match('/^[\p{L}\s\.\-\']+$/u', $nombreCompleto)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El nombre solo puede contener letras, espacios y tildes.']);
        exit;
    }

    if (mb_strlen($nombreCompleto) > 150) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El nombre no puede exceder 150 caracteres.']);
        exit;
    }

    // Validar asistencia
    $asistira = isset($data['asistira']) ? (bool)$data['asistira'] : true;

    // Validar acompañantes
    $acompanantes = [];
    if ($asistira && isset($data['acompanantes']) && is_array($data['acompanantes'])) {
        $maxAcompanantes = defined('MAX_ACOMPANANTES') ? MAX_ACOMPANANTES : 5;

        foreach ($data['acompanantes'] as $nombre) {
            $nombre = trim($nombre);
            if ($nombre === '') continue;

            if (!preg_match('/^[\p{L}\s\.\-\']+$/u', $nombre)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "El nombre del acompañante \"$nombre\" contiene caracteres no válidos."]);
                exit;
            }

            if (mb_strlen($nombre) > 150) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El nombre del acompañante no puede exceder 150 caracteres.']);
                exit;
            }

            $acompanantes[] = $nombre;
        }

        if (count($acompanantes) > $maxAcompanantes) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Máximo $maxAcompanantes acompañantes permitidos."]);
            exit;
        }
    }

    // Conectar a la BD e insertar con transacción
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    try {
        // Insertar invitado principal
        $stmt = $pdo->prepare('INSERT INTO invitados (nombre_completo, asistira) VALUES (:nombre, :asistira)');
        $stmt->execute([
            ':nombre'   => $nombreCompleto,
            ':asistira' => $asistira ? 1 : 0,
        ]);
        $invitadoId = $pdo->lastInsertId();

        // Insertar acompañantes si los hay
        if ($asistira && !empty($acompanantes)) {
            $stmtAcomp = $pdo->prepare('INSERT INTO acompanantes (invitado_id, nombre_completo) VALUES (:invitado_id, :nombre)');
            foreach ($acompanantes as $nombreAcomp) {
                $stmtAcomp->execute([
                    ':invitado_id' => $invitadoId,
                    ':nombre'      => $nombreAcomp,
                ]);
            }
        }

        $pdo->commit();

        // Contar total de confirmados que asistirán (invitados + acompañantes)
        $stmtCount = $pdo->query(
            'SELECT 
                (SELECT COUNT(*) FROM invitados WHERE asistira = 1) +
                (SELECT COUNT(*) FROM acompanantes a INNER JOIN invitados i ON a.invitado_id = i.id WHERE i.asistira = 1)
             AS total'
        );
        $totalConfirmados = (int)$stmtCount->fetchColumn();

        http_response_code(200);
        $qrCode = 'XVANGIE-' . str_pad($invitadoId, 4, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(md5($nombreCompleto . $invitadoId . 'angie2026'), 0, 6));
        echo json_encode([
            'success'           => true,
            'id'                => (int)$invitadoId,
            'nombre'            => $nombreCompleto,
            'asistira'          => $asistira,
            'acompanantes'      => $acompanantes,
            'total_pases'       => $asistira ? (1 + count($acompanantes)) : 0,
            'qr_code'           => $qrCode,
            'total_confirmados' => $totalConfirmados,
            'message'           => $asistira
                ? '¡Gracias por confirmar tu asistencia! Aquí tienes tu Pase VIP de entrada con código QR. 🎉'
                : 'Lamentamos que no puedas asistir. ¡Te llevaremos en el corazón! 💛'
        ]);

    } catch (\Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error al guardar la confirmación. Por favor intenta de nuevo.'
    ]);
    // En producción, loguear el error real: error_log($e->getMessage());
    error_log('Error en confirmar.php: ' . $e->getMessage());

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Ocurrió un error inesperado. Por favor intenta de nuevo.'
    ]);
    error_log('Error en confirmar.php: ' . $e->getMessage());
}
