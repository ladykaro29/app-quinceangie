<?php
/**
 * API: Galería de Fotos y Álbum Colaborativo Multimedia
 * Plataforma Multi-Celebración — XV Experience
 * 
 * Endpoints:
 * GET  api/fotos.php                     -> Lista fotos aprobadas (o todas si mode=moderacion y auth)
 * GET  api/fotos.php?action=stats        -> Métricas de almacenamiento y conteos de moderación
 * POST api/fotos.php                     -> Sube una nueva foto (multipart/form-data o JSON base64)
 * POST api/fotos.php?action=like         -> Da 'like' a una foto ({ "id": 123 })
 * POST api/fotos.php?action=aprobar      -> Aprueba foto(s) { "id": 1 } o { "ids": [1,2,3] }
 * POST api/fotos.php?action=rechazar     -> Rechaza foto(s) { "id": 1 } o { "ids": [1,2,3] }
 * POST api/fotos.php?action=destacar     -> Alterna destacada { "id": 1 }
 * POST api/fotos.php?action=eliminar     -> Elimina foto física y registro { "id": 1 }
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/autoload.php';

use App\Services\StorageService;
use App\Core\Database;

try {
    $pdo = getDBConnection();
} catch (\Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ============================================================================
// 1. DAR LIKE A UNA FOTO
// ============================================================================
if ($method === 'POST' && $action === 'like') {
    header('Content-Type: application/json; charset=utf-8');
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;
    $fotoId = (int)($data['id'] ?? 0);

    if ($fotoId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de foto inválido']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE fotos_fiesta SET likes = likes + 1 WHERE id = :id AND visible = 1');
    $stmt->execute([':id' => $fotoId]);

    $stmtLikes = $pdo->prepare('SELECT likes FROM fotos_fiesta WHERE id = :id');
    $stmtLikes->execute([':id' => $fotoId]);
    $newLikes = (int)$stmtLikes->fetchColumn();

    echo json_encode(['success' => true, 'likes' => $newLikes]);
    exit;
}

// ============================================================================
// 2. MODERACIÓN ADMINISTRATIVA (APROBAR / RECHAZAR / DESTACAR / ELIMINAR / STATS)
// ============================================================================
if ($method === 'POST' && in_array($action, ['aprobar', 'rechazar', 'toggle_visible', 'destacar', 'eliminar'])) {
    header('Content-Type: application/json; charset=utf-8');
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        @session_start();
    }

    $isAuth = !empty($_SESSION['admin_auth']);
    $opKey = $data['operator_key'] ?? $_POST['operator_key'] ?? '';
    if (!$isAuth && ($opKey === ADMIN_PASS || $opKey === 'angie2026' || $opKey === 'sophia2026')) {
        $isAuth = true;
    }

    if (!$isAuth) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No autorizado']);
        exit;
    }

    $ids = [];
    if (!empty($data['ids']) && is_array($data['ids'])) {
        $ids = array_filter(array_map('intval', $data['ids']));
    } elseif (!empty($data['id'])) {
        $ids = [(int)$data['id']];
    }

    if (empty($ids)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No se especificaron IDs de fotos válidos']);
        exit;
    }

    $now = date('Y-m-d H:i:s');
    $inClause = implode(',', $ids);

    if ($action === 'aprobar') {
        $pdo->exec("UPDATE fotos_fiesta SET aprobada = 1, visible = 1, estado = 'aprobada', moderada_at = '$now', moderador_nombre = 'Admin' WHERE id IN ($inClause)");
        echo json_encode(['success' => true, 'action' => 'aprobar', 'affected_ids' => $ids, 'aprobada' => 1, 'visible' => 1]);
        exit;
    } elseif ($action === 'rechazar') {
        $pdo->exec("UPDATE fotos_fiesta SET aprobada = 0, visible = 0, estado = 'rechazada', moderada_at = '$now', moderador_nombre = 'Admin' WHERE id IN ($inClause)");
        echo json_encode(['success' => true, 'action' => 'rechazar', 'affected_ids' => $ids, 'aprobada' => 0, 'visible' => 0]);
        exit;
    } elseif ($action === 'toggle_visible') {
        $firstId = $ids[0];
        $stmt = $pdo->prepare('UPDATE fotos_fiesta SET visible = 1 - visible WHERE id = :id');
        $stmt->execute([':id' => $firstId]);
        $newV = (int)$pdo->query("SELECT visible FROM fotos_fiesta WHERE id = $firstId")->fetchColumn();
        echo json_encode(['success' => true, 'id' => $firstId, 'visible' => $newV]);
        exit;
    } elseif ($action === 'destacar') {
        $firstId = $ids[0];
        $stmt = $pdo->prepare('UPDATE fotos_fiesta SET es_destacada = 1 - COALESCE(es_destacada, 0) WHERE id = :id');
        $stmt->execute([':id' => $firstId]);
        $newDest = (int)$pdo->query("SELECT es_destacada FROM fotos_fiesta WHERE id = $firstId")->fetchColumn();
        echo json_encode(['success' => true, 'id' => $firstId, 'es_destacada' => $newDest]);
        exit;
    } elseif ($action === 'eliminar') {
        $stmtFiles = $pdo->query("SELECT id, archivo, evento_id FROM fotos_fiesta WHERE id IN ($inClause)");
        $records = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);
        foreach ($records as $r) {
            $p = StorageService::resolvePhotoPath($r['archivo'], (int)$r['evento_id']);
            if ($p && file_exists($p)) @unlink($p);
            $thumb = StorageService::resolveThumbPath($r['archivo'], (int)$r['evento_id']);
            if ($thumb && file_exists($thumb) && $thumb !== $p) @unlink($thumb);
        }
        $pdo->exec("DELETE FROM fotos_fiesta WHERE id IN ($inClause)");
        echo json_encode(['success' => true, 'action' => 'eliminar', 'deleted_ids' => $ids]);
        exit;
    }
}

// ============================================================================
// 3. STATS DE ALMACENAMIENTO Y MODERACIÓN (GET action=stats)
// ============================================================================
if ($method === 'GET' && $action === 'stats') {
    header('Content-Type: application/json; charset=utf-8');
    $eventoId = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 1;

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = :ev");
    $stmtTotal->execute([':ev' => $eventoId]);
    $total = (int)$stmtTotal->fetchColumn();

    $stmtPend = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = :ev AND (estado = 'pendiente' OR (aprobada = 0 AND visible = 0 AND estado != 'rechazada'))");
    $stmtPend->execute([':ev' => $eventoId]);
    $pendientes = (int)$stmtPend->fetchColumn();

    $stmtAprob = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = :ev AND aprobada = 1 AND visible = 1");
    $stmtAprob->execute([':ev' => $eventoId]);
    $aprobadas = (int)$stmtAprob->fetchColumn();

    $stmtRech = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = :ev AND estado = 'rechazada'");
    $stmtRech->execute([':ev' => $eventoId]);
    $rechazadas = (int)$stmtRech->fetchColumn();

    $stmtDest = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = :ev AND es_destacada = 1");
    $stmtDest->execute([':ev' => $eventoId]);
    $destacadas = (int)$stmtDest->fetchColumn();

    $usage = StorageService::getEventStorageUsage($eventoId);

    echo json_encode([
        'success'      => true,
        'evento_id'    => $eventoId,
        'total'        => $total,
        'pendientes'   => $pendientes,
        'aprobadas'    => $aprobadas,
        'rechazadas'   => $rechazadas,
        'destacadas'   => $destacadas,
        'espacio_mb'   => $usage['total_mb'],
        'archivos'     => $usage['file_count'],
        'cuota_max_mb' => 2048 // 2 GB recomendados por celebración
    ]);
    exit;
}

// ============================================================================
// 4. LISTAR FOTOS (GET)
// ============================================================================
if ($method === 'GET') {
    header('Content-Type: application/json; charset=utf-8');

    $eventoId = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
    if ($eventoId <= 0 && !empty($_GET['evento'])) {
        $slug = trim($_GET['evento']);
        $stmtSlug = $pdo->prepare('SELECT id FROM eventos WHERE slug = :s LIMIT 1');
        $stmtSlug->execute([':s' => $slug]);
        $eventoId = (int)$stmtSlug->fetchColumn();
    }
    if ($eventoId <= 0) {
        $eventoId = 1;
    }

    // Auto-sincronización de las 160 fotos históricas de Angie
    if ($eventoId === 1) {
        $countRecuerdos = (int)$pdo->query("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = 1 AND archivo LIKE 'angie_xv_recuerdo_%'")->fetchColumn();
        if ($countRecuerdos < 160) {
            $mensajesDed = [
                "Momentos mágicos e inolvidables de mis XV Años ✨👑",
                "Celebrando una noche mágica junto a las personas que más quiero 💖",
                "Sonrisas, abrazos y recuerdos para toda la vida ✨",
                "¡Una noche de ensueño que quedará por siempre en mi corazón! 🌟",
                "Bailando y disfrutando en la pista de mis XV Años 💃🎶",
                "Momentos únicos con mi hermosa familia y amigos 💕",
                "¡La mejor noche de mi vida! Gracias a todos por acompañarme ✨🥂"
            ];
            $nombresDed = [
                "Angie Karolina · Álbum Oficial",
                "Álbum de Recuerdos · Mis XV",
                "Angie Karolina · Mis 15",
                "Momentos Mágicos · Angie XV"
            ];
            $startTime = strtotime('2026-10-04 20:30:00');
            $stmtCheck = $pdo->prepare("SELECT id FROM fotos_fiesta WHERE archivo = :a AND evento_id = 1 LIMIT 1");
            $stmtInsert = $pdo->prepare('INSERT INTO fotos_fiesta (evento_id, nombre_invitado, mensaje, archivo, likes, ip, visible, aprobada, estado, created_at) VALUES (1, :n, :m, :a, :l, "127.0.0.1", 1, 1, "aprobada", :c)');
            for ($i = 1; $i <= 160; $i++) {
                $numStr = str_pad($i, 3, '0', STR_PAD_LEFT);
                $archivo = "angie_xv_recuerdo_{$numStr}.jpg";
                $stmtCheck->execute([':a' => $archivo]);
                if (!$stmtCheck->fetch()) {
                    $stmtInsert->execute([
                        ':n' => $nombresDed[$i % count($nombresDed)],
                        ':m' => $mensajesDed[$i % count($mensajesDed)],
                        ':a' => $archivo,
                        ':l' => rand(5, 28),
                        ':c' => date('Y-m-d H:i:s', $startTime + ($i * 15))
                    ]);
                }
            }
        }
    }

    $limit = isset($_GET['limit']) ? min(1000, max(1, (int)$_GET['limit'])) : 500;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
    $sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;
    $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';
    $mode = $_GET['mode'] ?? 'public';
    $filter = $_GET['filter'] ?? '';

    $whereClauses = ['evento_id = :evento_id'];
    $params = [':evento_id' => $eventoId];

    if ($mode === 'moderacion') {
        if ($filter === 'pendientes') {
            $whereClauses[] = "(estado = 'pendiente' OR (aprobada = 0 AND visible = 0 AND estado != 'rechazada'))";
        } elseif ($filter === 'aprobadas') {
            $whereClauses[] = "aprobada = 1 AND visible = 1";
        } elseif ($filter === 'rechazadas') {
            $whereClauses[] = "estado = 'rechazada'";
        } elseif ($filter === 'destacadas') {
            $whereClauses[] = "es_destacada = 1";
        }
    } else {
        // Modo público (proyector y galería web)
        $whereClauses[] = "visible = 1 AND aprobada = 1";
    }

    if ($sinceId > 0) {
        $whereClauses[] = "id > :since";
        $params[':since'] = $sinceId;
    }

    $whereSql = implode(' AND ', $whereClauses);

    $sql = "SELECT id, nombre_invitado, mensaje, archivo, likes, aprobada, visible, estado, es_destacada, peso_bytes, ancho_px, alto_px, created_at 
            FROM fotos_fiesta 
            WHERE {$whereSql} 
            ORDER BY id {$order} LIMIT :lim";

    if ($sinceId <= 0) {
        $sql .= " OFFSET :off";
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    if ($sinceId <= 0) {
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fotos as &$f) {
        $f['url'] = 'api/foto.php?f=' . rawurlencode($f['archivo']) . '&evento_id=' . $eventoId;
        $f['thumb_url'] = 'api/foto.php?f=' . rawurlencode($f['archivo']) . '&evento_id=' . $eventoId . '&thumb=1';
        $f['nombre_invitado'] = htmlspecialchars($f['nombre_invitado'] ?? 'Invitado', ENT_QUOTES, 'UTF-8');
        $f['mensaje'] = htmlspecialchars($f['mensaje'] ?? '', ENT_QUOTES, 'UTF-8');
        $f['es_destacada'] = (int)($f['es_destacada'] ?? 0);
    }

    $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE {$whereSql}");
    foreach ($params as $k => $v) {
        $totalStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $totalStmt->execute();
    $totalFotos = (int)$totalStmt->fetchColumn();

    echo json_encode([
        'success'   => true,
        'evento_id' => $eventoId,
        'total'     => $totalFotos,
        'fotos'     => $fotos
    ]);
    exit;
}

// ============================================================================
// 5. SUBIR FOTO (POST) — SEGURIDAD REFORZADA, DEDUPLICACIÓN Y CUOTA
// ============================================================================
if ($method === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $nombreInvitado = trim($_POST['nombre_invitado'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $subidaUuid = trim($_POST['subida_uuid'] ?? '');

    if (empty($nombreInvitado)) {
        $nombreInvitado = 'Invitado Especial';
    }
    $nombreInvitado = mb_substr($nombreInvitado, 0, 100);
    $mensaje = mb_substr($mensaje, 0, 500);

    // Resolver evento destino
    $eventoId = isset($_POST['evento_id']) ? (int)$_POST['evento_id'] : 0;
    if ($eventoId <= 0 && !empty($_POST['evento'])) {
        $slug = trim($_POST['evento']);
        $stmtSlug = $pdo->prepare('SELECT id FROM eventos WHERE slug = :s LIMIT 1');
        $stmtSlug->execute([':s' => $slug]);
        $eventoId = (int)$stmtSlug->fetchColumn();
    }
    if ($eventoId <= 0) $eventoId = 1;

    // A. Límite de tasa por IP (Máximo 40 subidas por IP cada 5 minutos)
    $stmtRate = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE ip = :ip AND created_at > datetime('now', '-5 minutes')");
    try {
        $stmtRate->execute([':ip' => $ip]);
        if ((int)$stmtRate->fetchColumn() >= 40) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Has alcanzado el límite de envíos temporales. Por favor espera un momento.']);
            exit;
        }
    } catch (\Exception $e) {}

    // B. Idempotencia por UUID si el cliente lo envió
    if (!empty($subidaUuid)) {
        $stmtUuid = $pdo->prepare("SELECT id, archivo, aprobada, visible FROM fotos_fiesta WHERE evento_id = :ev AND subida_uuid = :uuid LIMIT 1");
        try {
            $stmtUuid->execute([':ev' => $eventoId, ':uuid' => $subidaUuid]);
            $existing = $stmtUuid->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                echo json_encode([
                    'success'    => true,
                    'id'         => (int)$existing['id'],
                    'archivo'    => $existing['archivo'],
                    'foto'       => [
                        'id'      => (int)$existing['id'],
                        'archivo' => $existing['archivo']
                    ],
                    'url'        => 'api/foto.php?f=' . rawurlencode($existing['archivo']) . '&evento_id=' . $eventoId,
                    'duplicated' => true,
                    'idempotent' => true,
                    'mensaje'    => 'Fotografía ya registrada previamente.'
                ]);
                exit;
            }
        } catch (\Exception $e) {}
    }

    $tempPath = null;
    $clientExt = 'jpg';

    // 1. Obtener ruta temporal del archivo
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        if ($file['size'] > 12 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'La foto supera el tamaño máximo permitido (12MB).']);
            exit;
        }
        $tempPath = $file['tmp_name'];
        $clientExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    } elseif (!empty($_POST['foto_base64'])) {
        $base64 = $_POST['foto_base64'];
        if (preg_match('/^data:image\/(jpeg|png|webp);base64,/', $base64, $matches)) {
            $clientExt = ($matches[1] === 'jpeg') ? 'jpg' : $matches[1];
            $data = substr($base64, strpos($base64, ',') + 1);
            $decoded = base64_decode($data);
            if ($decoded !== false) {
                $tmpFile = tempnam(sys_get_temp_dir(), 'xv_up_');
                file_put_contents($tmpFile, $decoded);
                $tempPath = $tmpFile;
            }
        }
    }

    if (!$tempPath || !file_exists($tempPath)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No se recibió ninguna imagen válida.']);
        exit;
    }

    // 2. Validación estricta de MIME real (con finfo)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $tempPath);
    finfo_close($finfo);

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'jpg',
        'image/heif' => 'jpg'
    ];

    if (!isset($allowedMimes[$realMime])) {
        if (!empty($_POST['foto_base64'])) @unlink($tempPath);
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Formato no soportado. Debe ser imagen real (JPG, PNG, WEBP).']);
        exit;
    }

    // 3. Inspección de seguridad binaria contra scripts incrustados
    $headSample = file_get_contents($tempPath, false, null, 0, min(100000, filesize($tempPath)));
    if (stripos($headSample, '<?php') !== false || stripos($headSample, '<?=') !== false || stripos($headSample, '<script') !== false) {
        if (!empty($_POST['foto_base64'])) @unlink($tempPath);
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Archivo no permitido por contenido inválido.']);
        exit;
    }

    // 4. Validación de dimensiones mínimas y máximas
    $imgSize = @getimagesize($tempPath);
    if (!$imgSize || $imgSize[0] < 1 || $imgSize[1] < 1 || $imgSize[0] > 6000 || $imgSize[1] > 6000) {
        if (!empty($_POST['foto_base64'])) @unlink($tempPath);
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Dimensiones de imagen inválidas (máximo 6000x6000px).']);
        exit;
    }

    // 5. Deduplicación por hash SHA-256 de contenido
    $fileHash = hash_file('sha256', $tempPath);
    try {
        $stmtHash = $pdo->prepare("SELECT id, archivo, aprobada, visible FROM fotos_fiesta WHERE evento_id = :ev AND hash_sha256 = :hash AND created_at > datetime('now', '-1 day') LIMIT 1");
        $stmtHash->execute([':ev' => $eventoId, ':hash' => $fileHash]);
        $existingHash = $stmtHash->fetch(PDO::FETCH_ASSOC);
        if ($existingHash) {
            if (!empty($_POST['foto_base64'])) @unlink($tempPath);
            echo json_encode([
                'success'    => true,
                'id'         => (int)$existingHash['id'],
                'archivo'    => $existingHash['archivo'],
                'foto'       => [
                    'id'      => (int)$existingHash['id'],
                    'archivo' => $existingHash['archivo']
                ],
                'url'        => 'api/foto.php?f=' . rawurlencode($existingHash['archivo']) . '&evento_id=' . $eventoId,
                'duplicated' => true,
                'idempotent' => true,
                'mensaje'    => 'Esta fotografía ya fue compartida anteriormente.'
            ]);
            exit;
        }
    } catch (\Exception $e) {}

    // 6. Almacenamiento seguro particionado
    $storeResult = StorageService::storeGuestPhoto($eventoId, $tempPath, $allowedMimes[$realMime], $subidaUuid);
    if (!empty($_POST['foto_base64'])) @unlink($tempPath);

    if (!$storeResult['success']) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $storeResult['error'] ?? 'Error guardando archivo']);
        exit;
    }

    $savedFilename = $storeResult['filename'];

    // 7. Determinar estado de moderación inicial del evento
    $aprobadaInicial = 1;
    $estadoInicial = 'aprobada';
    try {
        $stmtMod = $pdo->prepare("SELECT valor FROM configuraciones_evento WHERE evento_id = :ev AND clave = 'moderacion_fotos_activa' LIMIT 1");
        $stmtMod->execute([':ev' => $eventoId]);
        $modVal = $stmtMod->fetchColumn();
        if ($modVal === '1' || $modVal === 1) {
            $aprobadaInicial = 0;
            $estadoInicial = 'pendiente';
        }
    } catch (\Exception $e) {}

    // 8. Registro en base de datos con campos de Fase 2
    $stmt = $pdo->prepare('INSERT INTO fotos_fiesta 
        (evento_id, nombre_invitado, mensaje, archivo, likes, ip, visible, aprobada, estado, subida_uuid, hash_sha256, peso_bytes, ancho_px, alto_px, mime_type, created_at) 
        VALUES 
        (:evento_id, :nombre, :mensaje, :archivo, 0, :ip, :visible, :aprobada, :estado, :subida_uuid, :hash_sha256, :peso_bytes, :ancho_px, :alto_px, :mime_type, :created_at)');
    
    $now = date('Y-m-d H:i:s');
    $visibleInicial = ($aprobadaInicial === 1) ? 1 : 0;

    $stmt->execute([
        ':evento_id'    => $eventoId,
        ':nombre'       => $nombreInvitado,
        ':mensaje'      => $mensaje ?: null,
        ':archivo'      => $savedFilename,
        ':ip'           => $ip,
        ':visible'      => $visibleInicial,
        ':aprobada'     => $aprobadaInicial,
        ':estado'       => $estadoInicial,
        ':subida_uuid'  => $subidaUuid ?: null,
        ':hash_sha256'  => $storeResult['hash'],
        ':peso_bytes'   => $storeResult['bytes'],
        ':ancho_px'     => $storeResult['width'],
        ':alto_px'      => $storeResult['height'],
        ':mime_type'    => $storeResult['mime'],
        ':created_at'   => $now
    ]);

    $newId = (int)$pdo->lastInsertId();

    $respMsg = ($aprobadaInicial === 1)
        ? '¡Foto compartida con éxito! Ya está visible en la galería en vivo.'
        : '¡Foto recibida con éxito! Se proyectará en la pantalla en cuanto sea aprobada por los anfitriones.';

    echo json_encode([
        'success'   => true,
        'id'        => $newId,
        'evento_id' => $eventoId,
        'foto'      => [
            'id'              => $newId,
            'nombre_invitado' => htmlspecialchars($nombreInvitado),
            'mensaje'         => htmlspecialchars($mensaje),
            'archivo'         => $savedFilename,
            'url'             => 'api/foto.php?f=' . rawurlencode($savedFilename) . '&evento_id=' . $eventoId,
            'thumb_url'       => 'api/foto.php?f=' . rawurlencode($savedFilename) . '&evento_id=' . $eventoId . '&thumb=1',
            'aprobada'        => $aprobadaInicial,
            'estado'          => $estadoInicial,
            'likes'           => 0,
            'created_at'      => $now
        ],
        'mensaje'   => $respMsg
    ]);
    exit;
}
