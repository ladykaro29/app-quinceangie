<?php
/**
 * Script de importación y optimización de fotos del cumpleaños
 * Para: Invitación XV Años - Angie Karolina
 */

require_once __DIR__ . '/../config/database.php';

$sourceDir = 'C:/Users/troni/Downloads/FotosAngie15';
$destDir   = __DIR__ . '/uploads/fotos';

if (!is_dir($sourceDir)) {
    die("ERROR: El directorio de origen no existe: $sourceDir\n");
}

if (!is_dir($destDir)) {
    if (!mkdir($destDir, 0775, true)) {
        die("ERROR: No se pudo crear el directorio de destino: $destDir\n");
    }
}

// 1. Obtener conexión a la base de datos
try {
    $pdo = getDBConnection();
    echo "Conexión a BD exitosa. Motor activo: " . getActiveDbEngine() . "\n";
} catch (Exception $e) {
    die("ERROR de BD: " . $e->getMessage() . "\n");
}

// 2. Escanear y filtrar imágenes válidas
$files = scandir($sourceDir);
$images = [];

foreach ($files as $f) {
    if ($f === '.' || $f === '..') continue;
    $fullPath = $sourceDir . '/' . $f;
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        $images[] = [
            'src_name' => $f,
            'src_path' => $fullPath,
            'mtime'    => filemtime($fullPath),
            'size'     => filesize($fullPath)
        ];
    }
}

// Orden natural por nombre de WhatsApp (agrupado cronológicamente por hora)
usort($images, function($a, $b) {
    return strnatcasecmp($a['src_name'], $b['src_name']);
});

$totalImages = count($images);
echo "Total de imágenes encontradas para importar: $totalImages\n";

if ($totalImages === 0) {
    die("No se encontraron imágenes válidas en el directorio.\n");
}

// Mensajes y dedicatorias emotivas para el álbum
$mensajesDedicatorias = [
    "Momentos mágicos e inolvidables de mis XV Años ✨👑",
    "Celebrando una noche mágica junto a las personas que más quiero 💖",
    "Sonrisas, abrazos y recuerdos para toda la vida ✨",
    "¡Una noche de ensueño que quedará por siempre en mi corazón! 🌟",
    "Bailando y disfrutando en la pista de mis XV Años 💃🎶",
    "Momentos únicos con mi hermosa familia y amigos 💕",
    "¡La mejor noche de mi vida! Gracias a todos por acompañarme ✨🥂",
    "Risas, alegría y pura diversión celebrando mis 15 ✨",
    "Recuerdos dorados de una celebración inolvidable 👑✨",
    "¡Festejando por todo lo alto! Mis XV Años 💖✨"
];

$nombresInvitado = [
    "Angie Karolina · Álbum Oficial",
    "Álbum de Recuerdos · Mis XV",
    "Angie Karolina · Mis 15",
    "Familia y Amigos de Angie",
    "Momentos Mágicos · Angie XV"
];

// Limpiar fotos previas si se desea o comprobar existentes
// Verificamos cuántas hay actualmente
$countExistentes = (int)$pdo->query("SELECT COUNT(*) FROM fotos_fiesta")->fetchColumn();
echo "Fotos actualmente registradas en la tabla fotos_fiesta: $countExistentes\n";

// Si hay fotos de prueba o queremos refrescar limpiamente
if ($countExistentes > 0) {
    echo "Vaciando fotos previas para una importación completa y limpia...\n";
    $pdo->exec("DELETE FROM fotos_fiesta");
    // Limpiar archivos anteriores en destDir si hubiese
    foreach (glob($destDir . '/*.*') as $oldFile) {
        if (is_file($oldFile)) @unlink($oldFile);
    }
}

$insertStmt = $pdo->prepare("INSERT INTO fotos_fiesta (nombre_invitado, mensaje, archivo, likes, ip, visible, created_at) VALUES (:nombre, :mensaje, :archivo, :likes, :ip, 1, :created_at)");

$sqlExportLines = [];
$sqlExportLines[] = "-- Script de siembra para fotos_fiesta (MySQL / SQLite)";
$sqlExportLines[] = "-- Generado automáticamente para la Invitación XV Angie Karolina";
$sqlExportLines[] = "TRUNCATE TABLE fotos_fiesta;";

$copiadas = 0;
$startTime = strtotime('2026-10-04 20:30:00'); // Hora de la fiesta

foreach ($images as $idx => $img) {
    $num = $idx + 1;
    $numPadded = str_pad($num, 3, '0', STR_PAD_LEFT);
    $destFilename = "angie_xv_recuerdo_{$numPadded}.jpg";
    $destPath = $destDir . '/' . $destFilename;

    // Copiar archivo al destino
    if (!copy($img['src_path'], $destPath)) {
        echo "Error copiando {$img['src_name']} a $destPath\n";
        continue;
    }

    $nombre = $nombresInvitado[$idx % count($nombresInvitado)];
    $mensaje = $mensajesDedicatorias[$idx % count($mensajesDedicatorias)];
    $likes = rand(5, 28);
    $ip = '127.0.0.1';
    
    // Incrementar 15 segundos entre cada foto para mantener orden cronológico exacto
    $createdAt = date('Y-m-d H:i:s', $startTime + ($idx * 15));

    $insertStmt->execute([
        ':nombre'     => $nombre,
        ':mensaje'    => $mensaje,
        ':archivo'    => $destFilename,
        ':likes'      => $likes,
        ':ip'         => $ip,
        ':created_at' => $createdAt
    ]);

    // Generar línea SQL para exportación
    $escNombre = addslashes($nombre);
    $escMensaje = addslashes($mensaje);
    $sqlExportLines[] = "INSERT INTO fotos_fiesta (nombre_invitado, mensaje, archivo, likes, ip, visible, created_at) VALUES ('{$escNombre}', '{$escMensaje}', '{$destFilename}', {$likes}, '{$ip}', 1, '{$createdAt}');";

    $copiadas++;
}

// Guardar seed SQL
file_put_contents(__DIR__ . '/seed_fotos.sql', implode("\n", $sqlExportLines) . "\n");

echo "====================================================\n";
echo "¡IMPORTACIÓN EXITOSA!\n";
echo "Fotos copiadas físicamente a database/uploads/fotos: $copiadas\n";
$totalDb = (int)$pdo->query("SELECT COUNT(*) FROM fotos_fiesta")->fetchColumn();
echo "Total de registros en base de datos (fotos_fiesta): $totalDb\n";
echo "Archivo SQL de respaldo generado en: database/seed_fotos.sql\n";
echo "====================================================\n";
