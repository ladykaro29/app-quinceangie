<?php
namespace App\Services;

/**
 * Servicio de Abstracción de Almacenamiento Multimedia
 * Plataforma Multi-Celebración — XV Experience
 * 
 * Centraliza la gestión de archivos físicos, particionado por celebración (tenant),
 * cuotas de almacenamiento y preparación para integración S3 / Cloudflare R2.
 */
class StorageService
{
    private static string $baseStorageDir = '';
    private static string $legacyUploadDir = '';
    private static string $legacyPublicFotosDir = '';

    private static function initDirs(): void
    {
        if (empty(self::$baseStorageDir)) {
            $root = dirname(__DIR__, 2);
            self::$baseStorageDir = $root . '/storage/media';
            self::$legacyUploadDir = $root . '/database/uploads/fotos';
            self::$legacyPublicFotosDir = $root . '/public/assets/fotos';
        }
    }

    /**
     * Retorna la ruta física del directorio correspondiente al evento y categoría
     */
    public static function getEventDir(int $eventoId, string $type = 'fotos'): string
    {
        self::initDirs();
        $safeType = preg_replace('/[^a-z0-9_-]/i', '', $type);
        $dir = self::$baseStorageDir . '/' . $eventoId . '/' . $safeType;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    /**
     * Resuelve la ruta física real de una fotografía, buscando en el almacenamiento
     * particionado por evento, o en los directorios legacy (database/uploads y public/assets)
     */
    public static function resolvePhotoPath(string $filename, ?int $eventoId = null): ?string
    {
        self::initDirs();
        $cleanName = basename($filename);
        if (empty($cleanName)) {
            return null;
        }

        // 1. Buscar en el directorio particionado del evento (si se conoce)
        if ($eventoId !== null && $eventoId > 0) {
            $eventFile = self::getEventDir($eventoId, 'fotos') . '/' . $cleanName;
            if (file_exists($eventFile) && is_readable($eventFile)) {
                return $eventFile;
            }
        }

        // 2. Buscar en legacy uploads
        $legacyUpload = self::$legacyUploadDir . '/' . $cleanName;
        if (file_exists($legacyUpload) && is_readable($legacyUpload)) {
            return $legacyUpload;
        }

        // 3. Buscar en public assets (fotos históricas de Angie u oficiales)
        $legacyPublic = self::$legacyPublicFotosDir . '/' . $cleanName;
        if (file_exists($legacyPublic) && is_readable($legacyPublic)) {
            return $legacyPublic;
        }

        // 4. Búsqueda profunda en storage/media/*/fotos/
        if (is_dir(self::$baseStorageDir)) {
            $dirs = glob(self::$baseStorageDir . '/*/fotos/' . $cleanName);
            if (!empty($dirs) && file_exists($dirs[0]) && is_readable($dirs[0])) {
                return $dirs[0];
            }
        }

        return null;
    }

    /**
     * Resuelve la ruta de la miniatura optimizada de una fotografía
     */
    public static function resolveThumbPath(string $filename, ?int $eventoId = null): ?string
    {
        self::initDirs();
        $cleanName = basename($filename);
        if ($eventoId !== null && $eventoId > 0) {
            $thumbFile = self::getEventDir($eventoId, 'thumbs') . '/thumb_' . $cleanName;
            if (file_exists($thumbFile) && is_readable($thumbFile)) {
                return $thumbFile;
            }
        }
        return self::resolvePhotoPath($filename, $eventoId);
    }

    /**
     * Guarda una fotografía de invitado de forma segura y particionada por celebración
     * 
     * @return array [success => bool, filename => string, path => string, hash => string, bytes => int, width => int, height => int, mime => string]
     */
    public static function storeGuestPhoto(int $eventoId, string $tmpFilePath, string $extension, ?string $subidaUuid = null): array
    {
        self::initDirs();

        if (!file_exists($tmpFilePath) || !is_readable($tmpFilePath)) {
            return ['success' => false, 'error' => 'Archivo temporal inaccesible.'];
        }

        $bytes = filesize($tmpFilePath);
        $hash = hash_file('sha256', $tmpFilePath);

        // Detectar dimensiones reales
        $imgInfo = @getimagesize($tmpFilePath);
        $width = $imgInfo[0] ?? 0;
        $height = $imgInfo[1] ?? 0;
        $mime = $imgInfo['mime'] ?? 'image/jpeg';

        $cleanExt = strtolower(preg_replace('/[^a-z0-9]/', '', $extension)) ?: 'jpg';
        $uniqueName = 'xv_' . $eventoId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $cleanExt;

        // Rutas de destino
        $eventDir = self::getEventDir($eventoId, 'fotos');
        $targetPath = $eventDir . '/' . $uniqueName;

        // También sincronizar con legacy uploads para compatibilidad garantizada
        if (!is_dir(self::$legacyUploadDir)) {
            @mkdir(self::$legacyUploadDir, 0775, true);
        }
        $legacyPath = self::$legacyUploadDir . '/' . $uniqueName;

        if (is_uploaded_file($tmpFilePath)) {
            if (!move_uploaded_file($tmpFilePath, $targetPath)) {
                return ['success' => false, 'error' => 'No se pudo mover el archivo al almacenamiento del evento.'];
            }
        } else {
            if (!copy($tmpFilePath, $targetPath)) {
                return ['success' => false, 'error' => 'No se pudo copiar el archivo al almacenamiento del evento.'];
            }
        }

        // Copia a legacy para retrocompatibilidad total con endpoints anteriores
        @copy($targetPath, $legacyPath);

        // Generar miniatura liviana en segundo plano si GD está disponible
        self::generateThumbnail($targetPath, self::getEventDir($eventoId, 'thumbs') . '/thumb_' . $uniqueName, 400);

        return [
            'success'   => true,
            'filename'  => $uniqueName,
            'path'      => $targetPath,
            'hash'      => $hash,
            'bytes'     => $bytes,
            'width'     => $width,
            'height'    => $height,
            'mime'      => $mime,
            'uuid'      => $subidaUuid
        ];
    }

    /**
     * Genera una miniatura liviana (máx. 400px)
     */
    public static function generateThumbnail(string $sourcePath, string $targetPath, int $maxDim = 400): bool
    {
        if (!function_exists('imagecreatefromjpeg') || !file_exists($sourcePath)) {
            return false;
        }

        try {
            $info = @getimagesize($sourcePath);
            if (!$info) return false;

            $srcW = $info[0];
            $srcH = $info[1];
            $mime = $info['mime'];

            if ($srcW <= $maxDim && $srcH <= $maxDim) {
                return @copy($sourcePath, $targetPath);
            }

            if ($srcW > $srcH) {
                $dstW = $maxDim;
                $dstH = (int)round(($srcH * $maxDim) / $srcW);
            } else {
                $dstH = $maxDim;
                $dstW = (int)round(($srcW * $maxDim) / $srcH);
            }

            $srcImg = null;
            switch ($mime) {
                case 'image/jpeg':
                    $srcImg = @imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $srcImg = @imagecreatefrompng($sourcePath);
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $srcImg = @imagecreatefromwebp($sourcePath);
                    }
                    break;
            }

            if (!$srcImg) return false;

            $dstImg = imagecreatetruecolor($dstW, $dstH);
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($dstImg, false);
                imagesavealpha($dstImg, true);
            }

            imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);

            $saved = false;
            if (function_exists('imagewebp')) {
                $targetWebp = preg_replace('/\.[^.]+$/', '.webp', $targetPath);
                $saved = imagewebp($dstImg, $targetWebp, 80);
            } else {
                $saved = imagejpeg($dstImg, $targetPath, 80);
            }

            imagedestroy($srcImg);
            imagedestroy($dstImg);
            return $saved;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Calcula el uso de almacenamiento para una celebración (en bytes y cantidad de fotos)
     */
    public static function getEventStorageUsage(int $eventoId): array
    {
        self::initDirs();
        $eventDir = self::$baseStorageDir . '/' . $eventoId;
        $totalBytes = 0;
        $fileCount = 0;

        if (is_dir($eventDir)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($eventDir, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $totalBytes += $item->getSize();
                    $fileCount++;
                }
            }
        }

        return [
            'evento_id'   => $eventoId,
            'total_bytes' => $totalBytes,
            'total_mb'    => round($totalBytes / (1024 * 1024), 2),
            'file_count'  => $fileCount
        ];
    }
}
