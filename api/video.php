<?php
/**
 * Servidor de entrega y streaming seguro de videos de la fiesta
 * Con soporte de HTTP Range (206 Partial Content) para máxima compatibilidad móvil (iOS Safari, Android Chrome)
 * Invitación XV Años - Angie Karolina
 */

$filename = $_GET['v'] ?? '';
$filename = basename($filename); // Evitar Directory Traversal

if (empty($filename)) {
    http_response_code(400);
    exit('Archivo no especificado');
}

$filepath = __DIR__ . '/../database/uploads/videos/' . $filename;

if (!file_exists($filepath) || !is_readable($filepath)) {
    http_response_code(404);
    exit('Video no encontrado');
}

$filesize = filesize($filepath);
$length = $filesize;
$start = 0;
$end = $filesize - 1;

header('Content-Type: video/mp4');
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=604800, immutable');

if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $range, $matches)) {
        $start = (int)$matches[1];
        if (!empty($matches[2])) {
            $end = (int)$matches[2];
        }
    }

    if ($start > $end || $start >= $filesize) {
        http_response_code(416);
        header("Content-Range: bytes */$filesize");
        exit;
    }

    $length = $end - $start + 1;
    http_response_code(206);
    header("Content-Range: bytes $start-$end/$filesize");
} else {
    http_response_code(200);
}

header("Content-Length: $length");

$fp = fopen($filepath, 'rb');
if ($fp) {
    fseek($fp, $start);
    $buffer = 1024 * 64;
    $bytesSent = 0;
    while (!feof($fp) && $bytesSent < $length && connection_status() === 0) {
        $bytesToRead = min($buffer, $length - $bytesSent);
        $data = fread($fp, $bytesToRead);
        echo $data;
        flush();
        $bytesSent += strlen($data);
    }
    fclose($fp);
}
exit;
