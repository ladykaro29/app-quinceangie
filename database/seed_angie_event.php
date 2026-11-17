<?php
/**
 * Seeder de Inicialización y Migración del Evento de Angie
 * Plataforma: XV Experience
 */
require_once __DIR__ . '/../app/autoload.php';

use App\Core\Database;

$db = Database::getInstance();

echo "=== INICIANDO SEEDER DE ANGIE KAROLINA (EVENTO ID 1) ===\n";

// 1. Roles
$db->exec("INSERT OR IGNORE INTO roles (id, nombre, descripcion) VALUES 
(1, 'superadmin', 'Administrador Global de la Plataforma XV Experience'),
(2, 'anfitrion', 'Anfitrión / Familia de la Festejada'),
(3, 'staff', 'Personal de Puerta / Escaneo QR Check-in')");
echo "- Roles verificados.\n";

// 2. Paquetes comerciales
$db->exec("INSERT OR IGNORE INTO paquetes (id, codigo, nombre, precio, moneda, max_invitados, max_fotos, max_videos, incluye_libro_3d, incluye_proyector_envivo, incluye_rifa_sorteo, incluye_checkin_qr, activo) VALUES 
(1, 'royal_diamond', 'Royal Diamond Experience', 180.00, 'USD', 300, 250, 25, 1, 1, 1, 1, 1),
(2, 'crystal_luxury', 'Crystal Luxury', 120.00, 'USD', 150, 100, 10, 1, 1, 1, 1, 1),
(3, 'emerald_classic', 'Emerald Classic', 75.00, 'USD', 80, 50, 0, 0, 0, 1, 1, 1)");
echo "- Paquetes comerciales insertados.\n";

// 3. Plantillas
$db->exec("INSERT OR IGNORE INTO plantillas (id, codigo, nombre, descripcion, ruta_directorio, activo) VALUES 
(1, 'emerald-prestige', 'Esmeralda Real & Dorado Glam', 'Diseño lujoso verde esmeralda con partículas doradas y animaciones de gala', 'emerald-prestige', 1),
(2, 'rose-gold-princess', 'Rose Gold Princess & Flores', 'Diseño romántico en oro rosa, destellos suaves y ornamentos florales', 'rose-gold-princess', 1)");
echo "- Plantillas registradas.\n";

// 4. Cliente (Familia Angie)
$stmt = $db->query("SELECT id FROM clientes WHERE email = 'familia.angie@xvexperience.com'");
$clienteId = $stmt->fetchColumn();
if (!$clienteId) {
    $db->exec("INSERT INTO clientes (nombre_contacto, email, telefono, pais, ciudad) VALUES 
    ('Edwin Johan Avendaño y Lady Rivera', 'familia.angie@xvexperience.com', '+58 412 1234567', 'Venezuela', 'Mérida')");
    $clienteId = $db->lastInsertId();
}
echo "- Cliente verificado (ID: $clienteId).\n";

// 5. Usuario Administrador y Anfitrión
$adminPassHash = password_hash('admin2026', PASSWORD_BCRYPT);
$db->exec("INSERT OR IGNORE INTO usuarios (id, rol_id, nombre, email, password_hash, activo) VALUES 
(1, 1, 'Super Administrador', 'admin@xvexperience.com', '$adminPassHash', 1)");

$anfitrionPassHash = password_hash('angie2026', PASSWORD_BCRYPT);
$db->exec("INSERT OR IGNORE INTO usuarios (id, rol_id, nombre, email, password_hash, activo) VALUES 
(2, 2, 'Anfitrión Angie Karolina', 'angie@xvexperience.com', '$anfitrionPassHash', 1)");
echo "- Usuarios administrativos y anfitrión listos.\n";

// 6. Cargar datos del perfil de Angie
$angieJsonPath = __DIR__ . '/../BACKUP_ANGIE_SNAPSHOT/angie_full_profile.json';
$angie = json_decode(file_get_contents($angieJsonPath), true);

$stmt = $db->query("SELECT id FROM eventos WHERE slug = 'angie-xv' OR id = 1");
$eventoExists = $stmt->fetchColumn();

if (!$eventoExists) {
    $stmt = $db->prepare("INSERT INTO eventos (
        id, cliente_id, paquete_id, plantilla_id, slug, nombre_celebracion, tipo_evento,
        festejada_nombre, fecha_evento, hora_evento, lugar_nombre, lugar_direccion,
        lugar_coordenadas, lugar_mapa_url, codigo_vestimenta, color_reserva,
        musica_url, portada_url, avatar_url, activo, publicado
    ) VALUES (
        1, :cliente_id, 1, 1, 'angie-xv', :nombre_celebracion, 'xv_anos',
        :festejada_nombre, :fecha_evento, :hora_evento, :lugar_nombre, :lugar_direccion,
        :lugar_coordenadas, :lugar_mapa_url, :codigo_vestimenta, :color_reserva,
        :musica_url, :portada_url, :avatar_url, 1, 1
    )");

    $stmt->execute([
        ':cliente_id' => $clienteId,
        ':nombre_celebracion' => $angie['evento']['titulo_evento'] . ' - ' . $angie['evento']['nombre_corto'],
        ':festejada_nombre' => $angie['evento']['nombre_festejada'],
        ':fecha_evento' => '2026-10-03',
        ':hora_evento' => '20:00:00',
        ':lugar_nombre' => $angie['ubicacion']['nombre_salon'],
        ':lugar_direccion' => $angie['ubicacion']['direccion_completa'],
        ':lugar_coordenadas' => '8.5910,-71.1444',
        ':lugar_mapa_url' => $angie['ubicacion']['google_maps_url'],
        ':codigo_vestimenta' => $angie['dress_code']['titulo'] . ': ' . $angie['dress_code']['descripcion'],
        ':color_reserva' => 'Verde Esmeralda',
        ':musica_url' => 'assets/audio/musica.mp3',
        ':portada_url' => 'assets/img/angie-portada.jpg',
        ':avatar_url' => 'assets/img/angie-xv.jpg'
    ]);
    echo "- Evento #1 (angie-xv) registrado correctamente.\n";
} else {
    echo "- Evento #1 ya existe.\n";
}

// 7. Asociar permiso de anfitrión
$db->exec("INSERT OR IGNORE INTO permisos_evento_usuario (usuario_id, evento_id, nivel_acceso) VALUES (2, 1, 'anfitrion')");

// 8. Configuraciones de evento
$configs = [
    'padres_nombres' => $angie['familia']['padre'] . ' y ' . $angie['familia']['madre'],
    'padrinos_nombres' => $angie['familia']['padrinos'],
    'frase_biblica' => $angie['familia']['texto_historia'],
    'frase_bendicion' => $angie['familia']['texto_bendicion'],
    'frase_agradecimiento' => $angie['familia']['texto_agradecimiento'],
    'fecha_nacimiento' => $angie['evento']['fecha_nacimiento'],
    'google_calendar_url' => $angie['ubicacion']['google_calendar_url'],
    'itinerario_json' => json_encode($angie['itinerario'], JSON_UNESCAPED_UNICODE),
    'trivia_json' => json_encode($angie['trivia'], JSON_UNESCAPED_UNICODE),
    'design_tokens' => json_encode($angie['tokens_css'], JSON_UNESCAPED_UNICODE),
    'videos_metadata' => json_encode($angie['videos'], JSON_UNESCAPED_UNICODE),
    'musica_info' => json_encode($angie['musica'], JSON_UNESCAPED_UNICODE)
];

$stmtConfig = $db->prepare("INSERT OR REPLACE INTO configuraciones_evento (evento_id, clave, valor, tipo) VALUES (1, :clave, :valor, :tipo)");
foreach ($configs as $k => $v) {
    $stmtConfig->execute([
        ':clave' => $k,
        ':valor' => $v,
        ':tipo' => str_ends_with($k, '_json') || in_array($k, ['design_tokens', 'videos_metadata', 'musica_info']) ? 'json' : 'string'
    ]);
}
echo "- Configuraciones avanzadas (itinerario, trivia, tokens) vinculadas al Evento #1.\n";

// 9. Asegurar que todos los registros de fotos y libro pertenecen a evento_id = 1
$db->exec("UPDATE fotos_fiesta SET evento_id = 1 WHERE evento_id IS NULL OR evento_id = 0");
$db->exec("UPDATE libro_recuerdos SET evento_id = 1 WHERE evento_id IS NULL OR evento_id = 0");

$totalFotos = $db->query("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = 1")->fetchColumn();
$totalLibro = $db->query("SELECT COUNT(*) FROM libro_recuerdos WHERE evento_id = 1")->fetchColumn();

echo "=== MIGRACIÓN Y SEEDING COMPLETADOS EXITOSAMENTE ===\n";
echo "Total fotos vinculadas al Evento 1: $totalFotos\n";
echo "Total dedicatorias vinculadas al Evento 1: $totalLibro\n";
