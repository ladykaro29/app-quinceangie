<?php
/**
 * Seeder de Creación del Evento Piloto: Sophia Valentina (Evento ID 2)
 * Plataforma: XV Experience
 */
require_once __DIR__ . '/../app/autoload.php';

use App\Core\Database;

$db = Database::getInstance();

echo "=== INICIANDO REGISTRO DEL PILOTO: SOPHIA VALENTINA (EVENTO ID 2) ===\n";

// 1. Alta del Cliente
$stmt = $db->query("SELECT id FROM clientes WHERE email = 'carlos.mendoza@email.com'");
$clienteId = $stmt->fetchColumn();
if (!$clienteId) {
    $db->exec("INSERT INTO clientes (id, nombre_contacto, email, telefono, pais, ciudad) VALUES 
    (2, 'Carlos Mendoza y Mariana Silva', 'carlos.mendoza@email.com', '+58 412 9876543', 'Venezuela', 'Caracas')");
    $clienteId = 2;
    echo "- Cliente #2 (Familia Mendoza) registrado.\n";
} else {
    echo "- Cliente #2 ya existe.\n";
}

// 2. Alta de la Celebración (Evento 2)
$stmt = $db->query("SELECT id FROM eventos WHERE slug = 'sophia-xv' OR id = 2");
$eventoExists = $stmt->fetchColumn();

if (!$eventoExists) {
    $stmt = $db->prepare("INSERT INTO eventos (
        id, cliente_id, paquete_id, plantilla_id, slug, nombre_celebracion, tipo_evento,
        festejada_nombre, fecha_evento, hora_evento, lugar_nombre, lugar_direccion,
        lugar_coordenadas, lugar_mapa_url, codigo_vestimenta, color_reserva,
        musica_url, portada_url, avatar_url, activo, publicado
    ) VALUES (
        2, :cliente_id, 2, 2, 'sophia-xv', 'Mis Dulces 15 Años - Sophia Valentina', 'xv_anos',
        'Sophia Valentina Mendoza Silva', '2026-11-14', '20:30:00', 'Hotel Eurobuilding',
        'Salón Grand Mansión, Hotel Eurobuilding, Calle La Guairita, Caracas, Venezuela',
        '10.4850,-66.8620', 'https://maps.google.com/?q=Hotel+Eurobuilding+Caracas',
        'BLACK TIE & GALA: Te invitamos a lucir tu mejor atuendo de gala.', 'Rosa Oro y Champán',
        'storage/media/2/audio/musica.mp3', 'storage/media/2/covers/sophia_portada.jpg',
        'storage/media/2/covers/sophia_xv.jpg', 1, 1
    )");

    $stmt->execute([':cliente_id' => $clienteId]);
    echo "- Evento #2 (sophia-xv) registrado con éxito.\n";
} else {
    echo "- Evento #2 ya existe.\n";
}

// 3. Usuario Anfitrión para los padres de Sophia
$sophiaPassHash = password_hash('sophia2026', PASSWORD_BCRYPT);
$db->exec("INSERT OR IGNORE INTO usuarios (id, rol_id, nombre, email, password_hash, activo) VALUES 
(3, 2, 'Familia Mendoza Silva', 'padres.sophia@email.com', '$sophiaPassHash', 1)");

$db->exec("INSERT OR IGNORE INTO permisos_evento_usuario (usuario_id, evento_id, nivel_acceso) VALUES (3, 2, 'anfitrion')");
echo "- Usuario Anfitrión para Sophia registrado (padres.sophia@email.com / sophia2026).\n";

// 4. Configuraciones y Tokens de Diseño de Sophia (Plantilla Rose Gold Princess)
$itinerarioSophia = [
    ["hora" => "8:30 PM", "actividad" => "Recepción y Cóctel de Bienvenida", "icono" => "fas fa-champagne-glasses"],
    ["hora" => "9:45 PM", "actividad" => "Entrada Triunfal de Sophia", "icono" => "fas fa-crown"],
    ["hora" => "10:15 PM", "actividad" => "Vals con Papá y Brindis de Honor", "icono" => "fas fa-music"],
    ["hora" => "11:00 PM", "actividad" => "Cena de Gala Internacional", "icono" => "fas fa-utensils"],
    ["hora" => "12:30 AM", "actividad" => "Show de Luces y Hora Loca VIP", "icono" => "fas fa-mask"]
];

$triviaSophia = [
    [
        "pregunta" => "¿Cuál es la pasión artística de Sophia?",
        "opciones" => ["El Ballet Clásico", "La Pintura al Óleo", "Tocar el Piano", "Canto Lírico"],
        "correcta" => 0,
        "feedback_ok" => "¡Exacto! El ballet es su gran pasión desde pequeña 🩰✨",
        "feedback_no" => "La respuesta correcta es: El Ballet Clásico 🩰"
    ],
    [
        "pregunta" => "¿Cuál es su postre irresistible?",
        "opciones" => ["Tarta de Fresa", "Cheesecake de Frutos Rojos", "Macarons Franceses", "Churros con Chocolate"],
        "correcta" => 1,
        "feedback_ok" => "¡Totalmente! Ama el cheesecake de frutos rojos 🍓🍰",
        "feedback_no" => "Su favorito es el Cheesecake de Frutos Rojos 🍰"
    ]
];

$tokensSophia = [
    "--color-primary" => "#8E4A52",
    "--color-secondary" => "#C98276",
    "--color-gold" => "#E5A985",
    "--color-gold-light" => "#FFD9C0",
    "--color-bg" => "#1C1418"
];

$configsSophia = [
    'padres_nombres' => 'Carlos Mendoza y Mariana Silva',
    'padrinos_nombres' => 'Padrinos de Sophia',
    'frase_biblica' => 'La vida es un viaje lleno de momentos mágicos que iluminan nuestro caminar...',
    'frase_bendicion' => 'Agradecemos a Dios por concedernos la alegría de celebrar los quince años de nuestra amada hija.',
    'frase_agradecimiento' => 'Tu cariño y presencia harán que esta noche sea verdaderamente inolvidable. ¡Gracias por celebrar conmigo!',
    'fecha_nacimiento' => '2026-11-05',
    'google_calendar_url' => 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=Mis+XV+A%C3%B1os+-+Sophia+Valentina&dates=20261115T003000Z%2F20261115T060000Z',
    'itinerario_json' => json_encode($itinerarioSophia, JSON_UNESCAPED_UNICODE),
    'trivia_json' => json_encode($triviaSophia, JSON_UNESCAPED_UNICODE),
    'design_tokens' => json_encode($tokensSophia, JSON_UNESCAPED_UNICODE)
];

$stmtConfig = $db->prepare("INSERT OR REPLACE INTO configuraciones_evento (evento_id, clave, valor, tipo) VALUES (2, :clave, :valor, :tipo)");
foreach ($configsSophia as $k => $v) {
    $stmtConfig->execute([
        ':clave' => $k,
        ':valor' => $v,
        ':tipo' => str_ends_with($k, '_json') || $k === 'design_tokens' ? 'json' : 'string'
    ]);
}
echo "- Configuraciones y tokens de estilo asignados al Evento #2.\n";

// 5. Sembrar 1 dedicatoria de prueba para Sophia
$stmtMsg = $db->prepare("INSERT OR IGNORE INTO libro_recuerdos (id, evento_id, nombre, mensaje, icono, color, likes, ip, created_at) VALUES 
(100, 2, 'Papá y Mamá', 'Sophia de mi corazón, verte crecer tan hermosa y noble es nuestro mayor orgullo. ¡Felices 15 años princesa! 💖🌸', '👑', 'rose', 12, '127.0.0.1', '2026-11-14 21:00:00')");
$stmtMsg->execute();
echo "- Dedicatoria de bienvenida para Sophia agregada.\n";

echo "=== PILOTO SOPHIA VALENTINA DADO DE ALTA EXITOSAMENTE ===\n";
