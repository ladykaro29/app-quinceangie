<?php
namespace App\Controllers\Api;

use App\Core\Database;
use App\Core\CurrentEventContext;
use App\Middleware\TenantResolverMiddleware;
use PDO;

/**
 * Controlador de la API de Subida de Fotos Colaborativas
 * Cumple con el diseño del documento ALBUM_COLABORATIVO_XV_EXPERIENCE
 */
class CollaborativePhotoController
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'heic'
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

        // Resolver contexto de evento
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

        if ($sinceId > 0) {
            $stmt = $db->prepare("SELECT id, nombre_invitado, mensaje, archivo, likes, created_at 
                                   FROM fotos_fiesta 
                                   WHERE evento_id = :evento_id AND visible = 1 AND id > :since 
                                   ORDER BY id DESC LIMIT :lim");
            $stmt->bindValue(':evento_id', $eventoId, PDO::PARAM_INT);
            $stmt->bindValue(':since', $sinceId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        } else {
            $stmt = $db->prepare("SELECT id, nombre_invitado, mensaje, archivo, likes, created_at 
                                   FROM fotos_fiesta 
                                   WHERE evento_id = :evento_id AND visible = 1 
                                   ORDER BY id DESC LIMIT :lim OFFSET :off");
            $stmt->bindValue(':evento_id', $eventoId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($fotos as &$f) {
            $f['url'] = 'api/foto.php?f=' . rawurlencode($f['archivo']);
            $f['nombre_invitado'] = htmlspecialchars($f['nombre_invitado'] ?? 'Invitado', ENT_QUOTES, 'UTF-8');
            $f['mensaje'] = htmlspecialchars($f['mensaje'] ?? '', ENT_QUOTES, 'UTF-8');
        }

        echo json_encode([
            'success' => true,
            'evento_id' => $eventoId,
            'total' => count($fotos),
            'fotos' => $fotos
        ], JSON_UNESCAPED_UNICODE);
    }

    private static function uploadPhoto(int $eventoId): void
    {
        $db = Database::getInstance();

        // 1. Validar archivo recibido (multipart/form-data)
        if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se recibió ninguna imagen válida o ocurrió un error al subirla.']);
            return;
        }

        $file = $_FILES['foto'];

        // 2. Validar tamaño
        if ($file['size'] > self::MAX_FILE_SIZE) {
            http_response_code(413);
            echo json_encode(['success' => false, 'error' => 'La imagen excede el límite permitido de 12 MB.']);
            return;
        }

        // 3. Validar MIME real con finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        if (!array_key_exists($realMime, self::ALLOWED_MIME_TYPES)) {
            http_response_code(415);
            echo json_encode(['success' => false, 'error' => 'Formato no permitido. Solo se aceptan JPG, PNG, WEBP o HEIC.']);
            return;
        }

        $ext = self::ALLOWED_MIME_TYPES[$realMime];

        // 4. Nombre sanitizado
        $nombreInvitado = trim($_POST['nombre_invitado'] ?? 'Invitado');
        if (mb_strlen($nombreInvitado) > 60) {
            $nombreInvitado = mb_substr($nombreInvitado, 0, 60);
        }
        $mensaje = trim($_POST['mensaje'] ?? '');
        if (mb_strlen($mensaje) > 300) {
            $mensaje = mb_substr($mensaje, 0, 300);
        }

        $uniqueName = 'xv_' . $eventoId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        // 5. Directorios de destino
        $targetDirPrimary = dirname(__DIR__, 3) . '/database/uploads/fotos';
        $targetDirStorage = dirname(__DIR__, 3) . '/storage/media/' . $eventoId;

        if (!is_dir($targetDirPrimary)) @mkdir($targetDirPrimary, 0775, true);
        if (!is_dir($targetDirStorage)) @mkdir($targetDirStorage, 0775, true);

        $destPrimary = $targetDirPrimary . '/' . $uniqueName;
        $destStorage = $targetDirStorage . '/' . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destPrimary)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo guardar la fotografía en el servidor.']);
            return;
        }

        // Copia en storage/media
        @copy($destPrimary, $destStorage);

        // 6. Registrar en Base de Datos
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $db->prepare("INSERT INTO fotos_fiesta (evento_id, nombre_invitado, mensaje, archivo, likes, ip, visible, created_at) 
                               VALUES (:evento_id, :nombre, :mensaje, :archivo, 0, :ip, 1, :created_at)");
        $stmt->execute([
            ':evento_id' => $eventoId,
            ':nombre' => $nombreInvitado,
            ':mensaje' => $mensaje,
            ':archivo' => $uniqueName,
            ':ip' => $ip,
            ':created_at' => date('Y-m-d H:i:s')
        ]);

        $insertedId = $db->lastInsertId();

        echo json_encode([
            'success' => true,
            'id' => (int)$insertedId,
            'evento_id' => $eventoId,
            'archivo' => $uniqueName,
            'url' => 'api/foto.php?f=' . rawurlencode($uniqueName),
            'mensaje' => '¡Fotografía compartida con éxito en el álbum!'
        ], JSON_UNESCAPED_UNICODE);
    }
}
