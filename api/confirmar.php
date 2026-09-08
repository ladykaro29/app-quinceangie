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

        // 1. Validar que no haya nombres repetidos en la misma lista de acompañantes
        $normAcomp = [];
        foreach ($acompanantes as $acomp) {
            $normKey = mb_strtolower(preg_replace('/\s+/', ' ', trim($acomp)));
            if (isset($normAcomp[$normKey])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error'   => "El acompañante \"$acomp\" está repetido en la lista."
                ]);
                exit;
            }
            $normAcomp[$normKey] = true;

            // Tampoco puede llamarse exactamente igual al titular
            $normTitular = mb_strtolower(preg_replace('/\s+/', ' ', trim($nombreCompleto)));
            if ($normKey === $normTitular) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error'   => "El acompañante \"$acomp\" no puede tener el mismo nombre del titular."
                ]);
                exit;
            }
        }

        if (count($acompanantes) > $maxAcompanantes) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Máximo $maxAcompanantes acompañantes permitidos."]);
            exit;
        }
    }

    // Conectar a la BD
    $pdo = getDBConnection();

    // 2. VALIDAR SI EL INVITADO TITULAR YA ESTÁ REGISTRADO EN LA BASE DE DATOS
    // Normalizar para comparación sin importar mayúsculas/minúsculas ni espacios múltiples
    $nombreLimpio = trim(preg_replace('/\s+/', ' ', $nombreCompleto));

    $stmtCheck = $pdo->prepare('SELECT id, nombre_completo, asistira, codigo_rifa FROM invitados WHERE LOWER(TRIM(nombre_completo)) = LOWER(:nombre) LIMIT 1');
    $stmtCheck->execute([':nombre' => $nombreLimpio]);
    $existente = $stmtCheck->fetch();

    if ($existente) {
        // El invitado ya se había registrado previamente
        $idPrevio = (int)$existente['id'];
        $asistiraPrevio = (bool)$existente['asistira'];
        $codigoPrevio = $existente['codigo_rifa'];

        // Cargar sus acompañantes y boletos ya registrados para devolver su Pase VIP existente
        $stmtAcompsExistentes = $pdo->prepare('SELECT nombre_completo, codigo_rifa FROM acompanantes WHERE invitado_id = :id ORDER BY id ASC');
        $stmtAcompsExistentes->execute([':id' => $idPrevio]);
        $acompsExistentes = $stmtAcompsExistentes->fetchAll();

        $boletosPrevios = [];
        $nombresAcompsPrevios = [];
        if ($asistiraPrevio && $codigoPrevio) {
            $boletosPrevios[] = [
                'nombre'     => $existente['nombre_completo'],
                'codigo'     => $codigoPrevio,
                'es_titular' => true
            ];
        }
        foreach ($acompsExistentes as $ac) {
            $nombresAcompsPrevios[] = $ac['nombre_completo'];
            if ($asistiraPrevio && !empty($ac['codigo_rifa'])) {
                $boletosPrevios[] = [
                    'nombre'     => $ac['nombre_completo'],
                    'codigo'     => $ac['codigo_rifa'],
                    'es_titular' => false
                ];
            }
        }

        $qrCodePrevio = 'XVANGIE-' . str_pad($idPrevio, 4, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(md5($existente['nombre_completo'] . $idPrevio . 'angie2026'), 0, 6));

        // Retornar aviso amigable con su pase ya generado (o error explicativo si no asiste)
        http_response_code(200);
        echo json_encode([
            'success'           => true,
            'ya_registrado'     => true,
            'id'                => $idPrevio,
            'nombre'            => $existente['nombre_completo'],
            'asistira'          => $asistiraPrevio,
            'acompanantes'      => $nombresAcompsPrevios,
            'total_pases'       => $asistiraPrevio ? (1 + count($nombresAcompsPrevios)) : 0,
            'qr_code'           => $qrCodePrevio,
            'boletos_rifa'      => $boletosPrevios,
            'total_confirmados' => 0,
            'message'           => '✨ ' . $existente['nombre_completo'] . ', ya tenías una confirmación registrada previamente. Aquí tienes tu Pase VIP y tus números de rifa asignados.'
        ]);
        exit;
    }

    // 3. VALIDAR SI EL NOMBRE YA FUE REGISTRADO COMO ACOMPAÑANTE DE OTRO INVITADO
    $stmtCheckAcomp = $pdo->prepare('SELECT a.nombre_completo, i.nombre_completo AS titular FROM acompanantes a INNER JOIN invitados i ON a.invitado_id = i.id WHERE LOWER(TRIM(a.nombre_completo)) = LOWER(:nombre) LIMIT 1');
    $stmtCheckAcomp->execute([':nombre' => $nombreLimpio]);
    $comoAcomp = $stmtCheckAcomp->fetch();

    if ($comoAcomp) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => "El invitado \"{$comoAcomp['nombre_completo']}\" ya está confirmado como acompañante de {$comoAcomp['titular']}."
        ]);
        exit;
    }

    $pdo->beginTransaction();

    try {
        // Generar código de rifa para el titular si asistirá
        $codigoRifaTitular = $asistira ? generarCodigoRifaUnico($pdo) : null;

        // Insertar invitado principal
        $stmt = $pdo->prepare('INSERT INTO invitados (nombre_completo, asistira, codigo_rifa) VALUES (:nombre, :asistira, :codigo_rifa)');
        $stmt->execute([
            ':nombre'      => $nombreCompleto,
            ':asistira'    => $asistira ? 1 : 0,
            ':codigo_rifa' => $codigoRifaTitular,
        ]);
        $invitadoId = $pdo->lastInsertId();

        // Boletos de rifa generados
        $boletosRifa = [];
        if ($asistira && $codigoRifaTitular) {
            $boletosRifa[] = [
                'nombre'     => $nombreCompleto,
                'codigo'     => $codigoRifaTitular,
                'es_titular' => true,
            ];
        }

        // Insertar acompañantes si los hay con su propio código de rifa único
        if ($asistira && !empty($acompanantes)) {
            $stmtAcomp = $pdo->prepare('INSERT INTO acompanantes (invitado_id, nombre_completo, codigo_rifa) VALUES (:invitado_id, :nombre, :codigo_rifa)');
            foreach ($acompanantes as $nombreAcomp) {
                $codigoRifaAcomp = generarCodigoRifaUnico($pdo);
                $stmtAcomp->execute([
                    ':invitado_id' => $invitadoId,
                    ':nombre'      => $nombreAcomp,
                    ':codigo_rifa' => $codigoRifaAcomp,
                ]);

                $boletosRifa[] = [
                    'nombre'     => $nombreAcomp,
                    'codigo'     => $codigoRifaAcomp,
                    'es_titular' => false,
                ];
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
            'boletos_rifa'      => $boletosRifa,
            'total_confirmados' => $totalConfirmados,
            'message'           => $asistira
                ? '¡Gracias por confirmar tu asistencia! Aquí tienes tu Pase VIP de entrada y tus boletos para la Rifa de Regalos. 🎉'
                : 'Lamentamos que no puedas asistir. ¡Te llevaremos en el corazón! 💛'
        ]);

    } catch (\Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (\PDOException $e) {
    http_response_code(500);
    $detail = $e->getMessage();
    echo json_encode([
        'success' => false,
        'error'   => 'Error de base de datos: ' . $detail
    ]);
    error_log('Error en confirmar.php (PDO): ' . $detail);

} catch (\Exception $e) {
    http_response_code(500);
    $detail = $e->getMessage();
    echo json_encode([
        'success' => false,
        'error'   => 'Error al procesar: ' . $detail
    ]);
    error_log('Error en confirmar.php: ' . $detail);
}
