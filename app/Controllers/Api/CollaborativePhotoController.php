<?php
namespace App\Controllers\Api;

use App\Core\Database;
use App\Core\CurrentEventContext;
use App\Middleware\TenantResolverMiddleware;
use App\Services\StorageService;
use PDO;

/**
 * Controlador de la API de Subida de Fotos Colaborativas
 * Arquitectura MVC — XV Experience
 */
class CollaborativePhotoController
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'jpg',
        'image/heif' => 'jpg'
    ];
    private const MAX_FILE_SIZE = 12582912; // 12 MB

    public static function handle(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        TenantResolverMiddleware::resolve();
        $eventoId = CurrentEventContext::id() ?? 1;

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            self::getPhotos($eventoId);
        } elseif ($method === 'POST') {
            self::uploadPhoto($eventoId);
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        }
    }

    private static function getPhotos(int $eventoId): void
    {
        $db = Database::getInstance();
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 30;
        $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
        $sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;
        $mode = $_GET['mode'] ?? 'public';
        $aprobadaCond = ($mode === 'moderacion') ? "" : "AND aprobada = 1";

        if ($sinceId > 0) {
            $stmt = $db->prepare("SELECT id, nombre_invitado, mensaje, archivo, likes, aprobada, estado, es_destacada, created_at 
                                   FROM fotos_fiesta 
                                   WHERE evento_id = :evento_id AND visible = 1 {$aprobadaCond} AND id > :since 
                                   ORDER BY id DESC LIMIT :lim");
            $stmt->bindValue(':evento_id', $eventoId, PDO::PARAM_INT);
            $stmt->bindValue(':since', $sinceId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        } else {
            $stmt = $db->prepare("SELECT id, nombre_invitado, mensaje, archivo, likes, aprobada, estado, es_destacada, created_at 
                                   FROM fotos_fiesta 
                                   WHERE evento_id = :evento_id AND visible = 1 {$aprobadaCond}
                                   ORDER BY id DESC LIMIT :lim OFFSET :off");
            $stmt->bindValue(':evento_id', $eventoId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($fotos as &$f) {
            $f['url'] = 'api/foto.php?f=' . rawurlencode($f['archivo']) . '&evento_id=' . $eventoId;
            $f['thumb_url'] = 'api/foto.php?f=' . rawurlencode($f['archivo']) . '&evento_id=' . $eventoId . '&thumb=1';
            $f['nombre_invitado'] = htmlspecialchars($f['nombre_invitado'] ?? 'Invitado', ENT_QUOTES, 'UTF-8');
            $f['mensaje'] = htmlspecialchars($f['mensaje'] ?? '', ENT_QUOTES, 'UTF-8');
        }

        echo json_encode([
            'success'   => true,
            'evento_id' => $eventoId,
            'total'     => count($fotos),
            'fotos'     => $fotos
        ], JSON_UNESCAPED_UNICODE);
    }

    private static function uploadPhoto(int $eventoId): void
    {
        $db = Database::getInstance();

        if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se recibió ninguna imagen válida o ocurrió un error al subirla.']);
            return;
        }

        $file = $_FILES['foto'];

        if ($file['size'] > self::MAX_FILE_SIZE) {
            http_response_code(413);
            echo json_encode(['success' => false, 'error' => 'La imagen excede el límite permitido de 12 MB.']);
            return;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        if (!array_key_exists($realMime, self::ALLOWED_MIME_TYPES)) {
            http_response_code(415);
            echo json_encode(['success' => false, 'error' => 'Formato no permitido. Solo se aceptan JPG, PNG, WEBP o HEIC.']);
            return;
        }

        $sample = file_get_contents($file['tmp_name'], false, null, 0, min(100000, $file['size']));
        if (stripos($sample, '<?php') !== false || stripos($sample, '<script') !== false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Archivo no permitido por contenido inválido.']);
            return;
        }

        $imgSize = @getimagesize($file['tmp_name']);
        if (!$imgSize || $imgSize[0] < 1 || $imgSize[1] < 1 || $imgSize[0] > 6000 || $imgSize[1] > 6000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Dimensiones de imagen inválidas.']);
            return;
        }

        $subidaUuid = trim($_POST['subida_uuid'] ?? '');
        $nombreInvitado = trim($_POST['nombre_invitado'] ?? 'Invitado');
        if (mb_strlen($nombreInvitado) > 60) $nombreInvitado = mb_substr($nombreInvitado, 0, 60);
        $mensaje = trim($_POST['mensaje'] ?? '');
        if (mb_strlen($mensaje) > 300) $mensaje = mb_substr($mensaje, 0, 300);

        // Deduplicación / Idempotencia
        $fileHash = hash_file('sha256', $file['tmp_name']);
        if (!empty($subidaUuid)) {
            $stmtUuid = $db->prepare("SELECT id, archivo FROM fotos_fiesta WHERE evento_id = :ev AND subida_uuid = :u LIMIT 1");
            $stmtUuid->execute([':ev' => $eventoId, ':u' => $subidaUuid]);
            $existing = $stmtUuid->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                echo json_encode([
                    'success'    => true,
                    'id'         => (int)$existing['id'],
                    'archivo'    => $existing['archivo'],
                    'url'        => 'api/foto.php?f=' . rawurlencode($existing['archivo']) . '&evento_id=' . $eventoId,
                    'duplicated' => true
                ]);
                return;
            }
        }

        $storeResult = StorageService::storeGuestPhoto($eventoId, $file['tmp_name'], self::ALLOWED_MIME_TYPES[$realMime], $subidaUuid);
        if (!$storeResult['success']) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo guardar la fotografía en el servidor.']);
            return;
        }

        $savedFilename = $storeResult['filename'];

        // Revisar moderación
        $aprobadaInicial = 1;
        $estadoInicial = 'aprobada';
        try {
            $stmtMod = $db->prepare("SELECT valor FROM configuraciones_evento WHERE evento_id = :ev AND clave = 'moderacion_fotos_activa' LIMIT 1");
            $stmtMod->execute([':ev' => $eventoId]);
            $modVal = $stmtMod->fetchColumn();
            if ($modVal === '1' || $modVal === 1) {
                $aprobadaInicial = 0;
                $estadoInicial = 'pendiente';
            }
        } catch (\Exception $e) {}

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $now = date('Y-m-d H:i:s');
        $visibleInicial = ($aprobadaInicial === 1) ? 1 : 0;

        $stmt = $db->prepare("INSERT INTO fotos_fiesta 
            (evento_id, nombre_invitado, mensaje, archivo, likes, ip, visible, aprobada, estado, subida_uuid, hash_sha256, peso_bytes, ancho_px, alto_px, mime_type, created_at) 
            VALUES 
            (:evento_id, :nombre, :mensaje, :archivo, 0, :ip, :visible, :aprobada, :estado, :subida_uuid, :hash_sha256, :peso_bytes, :ancho_px, :alto_px, :mime_type, :created_at)");

        $stmt->execute([
            ':evento_id'    => $eventoId,
            ':nombre'       => $nombreInvitado,
            ':mensaje'      => $mensaje,
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

        $insertedId = $db->lastInsertId();

        echo json_encode([
            'success'   => true,
            'id'        => (int)$insertedId,
            'evento_id' => $eventoId,
            'archivo'   => $savedFilename,
            'url'       => 'api/foto.php?f=' . rawurlencode($savedFilename) . '&evento_id=' . $eventoId,
            'aprobada'  => $aprobadaInicial,
            'estado'    => $estadoInicial,
            'mensaje'   => ($aprobadaInicial === 1) ? '¡Fotografía compartida con éxito en el álbum!' : '¡Fotografía recibida! Esperando aprobación.'
        ], JSON_UNESCAPED_UNICODE);
    }
}
