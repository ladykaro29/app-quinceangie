<?php
/**
 * API: Galería de Fotos de la Fiesta en Vivo
 * Invitación XV Años - Angie Karolina
 * 
 * Endpoints:
 * GET  api/fotos.php              -> Lista las fotos visibles (recientes primero)
 * POST api/fotos.php              -> Sube una nueva foto (multipart/form-data o JSON base64)
 * POST api/fotos.php?action=like  -> Da 'like' a una foto ({ "id": 123 })
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Directorio de almacenamiento persistente
$uploadDir = __DIR__ . '/../database/uploads/fotos';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

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
// 2. LISTAR FOTOS (GET)
// ============================================================================
if ($method === 'GET') {
    header('Content-Type: application/json; charset=utf-8');

    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 60;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
    $sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;

    if ($sinceId > 0) {
        $stmt = $pdo->prepare('SELECT id, nombre_invitado, mensaje, archivo, likes, created_at 
                               FROM fotos_fiesta 
                               WHERE visible = 1 AND id > :since 
                               ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':since', $sinceId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare('SELECT id, nombre_invitado, mensaje, archivo, likes, created_at 
                               FROM fotos_fiesta 
                               WHERE visible = 1 
                               ORDER BY id DESC LIMIT :lim OFFSET :off');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
    }

    $fotos = $stmt->fetchAll();

    // Formatear rutas relativas seguras
    foreach ($fotos as &$f) {
        $f['url'] = 'api/foto.php?f=' . rawurlencode($f['archivo']);
        $f['nombre_invitado'] = htmlspecialchars($f['nombre_invitado'] ?? 'Invitado', ENT_QUOTES, 'UTF-8');
        $f['mensaje'] = htmlspecialchars($f['mensaje'] ?? '', ENT_QUOTES, 'UTF-8');
    }

    // Contar total
    $totalStmt = $pdo->query('SELECT COUNT(*) FROM fotos_fiesta WHERE visible = 1');
    $totalFotos = (int)$totalStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'total' => $totalFotos,
        'fotos' => $fotos
    ]);
    exit;
}

// ============================================================================
// 3. SUBIR FOTO (POST)
// ============================================================================
if ($method === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $nombreInvitado = trim($_POST['nombre_invitado'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($nombreInvitado)) {
        $nombreInvitado = 'Invitado Especial';
    }

    // Limitar longitud
    $nombreInvitado = mb_substr($nombreInvitado, 0, 100);
    $mensaje = mb_substr($mensaje, 0, 500);

    $savedFilename = null;

    // A. Subida mediante multipart/form-data
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];

        // Validar tamaño máximo (12MB)
        if ($file['size'] > 12 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'La foto supera el tamaño máximo permitido (12MB).']);
            exit;
        }

        // Validar tipo de imagen real
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/heic' => 'jpg',
            'image/heif' => 'jpg'
        ];

        if (!isset($allowedMimes[$mime])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Formato no soportado. Debe ser imagen (JPG, PNG, WEBP).']);
            exit;
        }

        $ext = $allowedMimes[$mime];
        $filename = 'angie_xv_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destPath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $savedFilename = $filename;
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo guardar el archivo en el servidor.']);
            exit;
        }

    // B. Subida mediante Base64 (Canvas / WebP / JPEG)
    } elseif (!empty($_POST['foto_base64'])) {
        $base64 = $_POST['foto_base64'];
        if (preg_match('/^data:image\/(jpeg|png|webp);base64,/', $base64, $matches)) {
            $ext = ($matches[1] === 'jpeg') ? 'jpg' : $matches[1];
            $data = substr($base64, strpos($base64, ',') + 1);
            $decoded = base64_decode($data);

            if ($decoded !== false) {
                $filename = 'angie_xv_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $destPath = $uploadDir . '/' . $filename;
                if (file_put_contents($destPath, $decoded)) {
                    $savedFilename = $filename;
                }
            }
        }
    }

    if (!$savedFilename) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No se recibió ninguna imagen válida.']);
        exit;
    }

    // Guardar en la base de datos
    $stmt = $pdo->prepare('INSERT INTO fotos_fiesta (nombre_invitado, mensaje, archivo, likes, ip, visible, created_at) 
                           VALUES (:nombre, :mensaje, :archivo, 0, :ip, 1, :created_at)');
    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        ':nombre' => $nombreInvitado,
        ':mensaje' => $mensaje ?: null,
        ':archivo' => $savedFilename,
        ':ip' => $ip,
        ':created_at' => $now
    ]);

    $newId = (int)$pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => '¡Foto compartida con éxito! Ya está visible en la galería en vivo.',
        'foto' => [
            'id' => $newId,
            'nombre_invitado' => htmlspecialchars($nombreInvitado, ENT_QUOTES, 'UTF-8'),
            'mensaje' => htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'),
            'archivo' => $savedFilename,
            'url' => 'api/foto.php?f=' . rawurlencode($savedFilename),
            'likes' => 0,
            'created_at' => $now
        ]
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Método no permitido']);
