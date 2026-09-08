<?php
/**
 * Servidor de entrega segura de fotos de la fiesta
 * Invitación XV Años - Angie Karolina
 */

$filename = $_GET['f'] ?? '';
$filename = basename($filename); // Evitar Directory Traversal

if (empty($filename)) {
    http_response_code(400);
    exit('Archivo no especificado');
}

$filepath = __DIR__ . '/../database/uploads/fotos/' . $filename;

if (!file_exists($filepath) || !is_readable($filepath)) {
    http_response_code(404);
    exit('Foto no encontrada');
}

$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
$mimes = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'gif'  => 'image/gif'
];

$contentType = $mimes[$ext] ?? 'application/octet-stream';

// Cache HTTP para máximo rendimiento y ahorro de datos
header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: public, max-age=86400, immutable');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

readfile($filepath);
exit;
