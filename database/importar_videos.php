<?php
$sourceDir = 'C:/Users/troni/Downloads/FotosAngie15';
$destDir   = __DIR__ . '/uploads/videos';

if (!is_dir($destDir)) {
    mkdir($destDir, 0775, true);
}

$files = scandir($sourceDir);
$videos = [];

foreach ($files as $f) {
    if (preg_match('/\.mp4$/i', $f)) {
        $fullPath = $sourceDir . '/' . $f;
        $videos[] = [
            'src_name' => $f,
            'src_path' => $fullPath,
            'size'     => filesize($fullPath),
            'mtime'    => filemtime($fullPath)
        ];
    }
}

// Orden natural por nombre para mantener secuencia cronológica de WhatsApp
usort($videos, function($a, $b) {
    return strnatcasecmp($a['src_name'], $b['src_name']);
});

$copied = 0;
foreach ($videos as $idx => $v) {
    $num = str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
    $destFilename = "angie_xv_video_{$num}.mp4";
    $destPath = $destDir . '/' . $destFilename;

    if (copy($v['src_path'], $destPath)) {
        $copied++;
        echo "Copiado: {$destFilename} (" . round($v['size'] / 1024 / 1024, 2) . " MB)\n";
    } else {
        echo "Error copiando: {$v['src_name']}\n";
    }
}

echo "Total videos copiados a {$destDir}: {$copied} de " . count($videos) . "\n";
