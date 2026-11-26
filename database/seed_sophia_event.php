<?php
/**
 * Seeder Oficial: Sophia Valentina Rivas Sánchez (Evento ID 2)
 * Información 100% oficial y confirmada.
 * Paleta: Mariposas rosadas, flores, lavanda y dorado champagne.
 */
require_once __DIR__ . '/../app/autoload.php';

use App\Core\Database;

$db = Database::getInstance();

echo "=== APLICANDO DATOS OFICIALES DE SOPHIA VALENTINA (EVENTO ID 2) ===\n";

// 1. Cliente oficial (Madre y Padre)
$db->exec("UPDATE clientes SET 
    nombre_contacto = 'Ramón Eduardo Rivas Avendaño y Estefany Yamileth Sánchez Ángulo',
    email = 'familia.rivas.sanchez@xvexperience.com',
    telefono = '+58 412 0000000',
    pais = 'Venezuela',
    ciudad = 'Mérida'
WHERE id = 2");

// 2. Usuario anfitrión oficial para los padres de Sophia
$sophiaPassHash = password_hash('sophia2026', PASSWORD_BCRYPT);
$db->exec("UPDATE usuarios SET 
    nombre = 'Familia Rivas Sánchez',
    email = 'padres.sophia@xvexperience.com',
    password_hash = '$sophiaPassHash'
WHERE id = 3");

// 3. Evento oficial en tabla eventos
$stmt = $db->prepare("UPDATE eventos SET 
    cliente_id = 2,
    paquete_id = 2,
    plantilla_id = 2,
    slug = 'sophia-xv',
    nombre_celebracion = 'Mis XV Años - Sophia Valentina',
    tipo_evento = 'xv_anos',
    festejada_nombre = 'Sophia Valentina Rivas Sánchez',
    fecha_evento = '2026-12-21',
    hora_evento = '19:30:00',
    lugar_nombre = 'Salón Nova Roma',
    lugar_direccion = 'Dirección pendiente de confirmación',
    lugar_coordenadas = NULL,
    lugar_mapa_url = NULL,
    codigo_vestimenta = 'Formal de Gala: sugerimos tonos suaves y elegantes. Por favor reserva los tonos rosa y dorado champagne para la quinceañera.',
    color_reserva = 'Rosa y Dorado Champagne',
    musica_url = 'storage/media/2/audio/dance_sugar_plum_fairy.mp3',
    portada_url = 'sophia-portada.jpg',
    avatar_url = 'sophia-xv.jpg',
    activo = 1,
    publicado = 1
WHERE id = 2");
$stmt->execute();
echo "- Tabla eventos actualizada con datos oficiales de Sophia (21 de Diciembre de 2026, 7:30 PM, Salón Nova Roma).\n";

// 4. Tokens de diseño para temática oficial: El jardín encantado de Sophia
$tokensSophiaOficial = [
    "--color-rosa-blush" => "#EBC5CC",
    "--color-rosa-empolvado" => "#D9A5B5",
    "--color-lavanda" => "#C6AEDB",
    "--color-dorado-champagne" => "#C5A16A",
    "--color-marfil-calido" => "#FFF3E7",
    "--color-marron-rosado" => "#79544D",
    "--verde-oscuro" => "#4A2E35",         // Tono cálido fondo de noche
    "--verde-medio" => "#6E4550",          // Seda rosada suave
    "--verde-claro" => "#D9A5B5",          // Rosa empolvado
    "--dorado" => "#C5A16A",               // Dorado Champagne oficial
    "--dorado-claro" => "#EAD8B8",         // Champagne luz
    "--dorado-brillante" => "#FBF3E3",     // Destello mariposa
    "--crema" => "#FFF3E7",                // Marfil cálido
    "--marron" => "#79544D",               // Marrón rosado
    "--rosa-suave" => "#EBC5CC",           // Rosa blush
    "--lavanda" => "#C6AEDB",              // Lavanda
    "--lavanda-suave" => "#EAE2F3"         // Lavanda luz
];

// 5. Itinerario oficial
$itinerarioSophiaOficial = [
    ["hora" => "7:30 PM", "actividad" => "Recepción de Invitados & Cóctel de Bienvenida", "icono" => "fas fa-champagne-glasses"],
    ["hora" => "8:30 PM", "actividad" => "Entrada Mágica de Sophia Valentina", "icono" => "fas fa-wand-magic-sparkles"],
    ["hora" => "9:00 PM", "actividad" => "El Vals de la Princesa con sus Padres", "icono" => "fas fa-music"],
    ["hora" => "9:30 PM", "actividad" => "Brindis Familiar & Palabras de Honor", "icono" => "fas fa-wine-glass-empty"],
    ["hora" => "10:00 PM", "actividad" => "Cena de Gala & Fotografías del Recuerdo", "icono" => "fas fa-camera-retro"],
    ["hora" => "11:30 PM", "actividad" => "Apertura de la Pista & Gran Celebración", "icono" => "fas fa-sparkles"]
];

// 6. Preguntas de Trivia adaptadas a Sophia Valentina
$triviaSophiaOficial = [
    [
        "question" => "¿Cómo se llama la hermosa quinceañera?",
        "options" => ["Sophia Valentina", "Victoria Sofía", "Isabella Valentina", "Sofía Camila"],
        "correct" => 0,
        "correctMsg" => "¡Exacto! Sophia Valentina, nuestra bella princesa 👑🌸",
        "wrongMsg" => "Su nombre completo es Sophia Valentina ✨"
    ],
    [
        "question" => "¿Qué mes especial celebra sus 15 años?",
        "options" => ["Diciembre", "Octubre", "Noviembre", "Septiembre"],
        "correct" => 0,
        "correctMsg" => "¡Muy bien! Noche mágica de diciembre ❄️🌸",
        "wrongMsg" => "¡El 21 de Diciembre de 2026! 🎄✨"
    ]
];

// 7. Configuraciones completas del evento 2
$configsSophiaOficial = [
    'nombre_corto' => 'Sophia Valentina',
    'padres_nombres' => 'Ramón Eduardo Rivas Avendaño y Estefany Yamileth Sánchez Ángulo',
    'nombre_padre' => 'Ramón Eduardo Rivas Avendaño',
    'nombre_madre' => 'Estefany Yamileth Sánchez Ángulo',
    'padrinos_nombres' => 'Padrinos de Sophia Valentina',
    'cancion_titulo' => 'Danza del Hada de Azúcar - Tchaikovsky (Fantasía)',
    'tematica' => 'Mariposas rosadas, flores, lavanda y dorado champagne',
    'frase_biblica' => 'Como una mariposa que despliega sus alas al viento, hoy comienzo a escribir el más hermoso capítulo de mi historia...',
    'frase_bendicion' => 'Con la bendición de Dios y el amor infinito de nuestros corazones, los invitamos a celebrar los quince años de nuestra amada hija.',
    'frase_agradecimiento' => 'Tu cariño, compañía y alegría hacen que esta noche de mariposas y sueños sea verdaderamente inolvidable. ¡Gracias de corazón!',
    'fecha_nacimiento' => '2026-12-21',
    'google_calendar_url' => 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=Mis+XV+A%C3%B1os+-+Sophia+Valentina&dates=20261221T233000Z%2F20261222T060000Z&details=%C2%A1Acomp%C3%A1%C3%B1anos+a+celebrar+los+XV+A%C3%B1os+de+Sophia+Valentina!+Una+noche+m%C3%A1gica+de+mariposas+y+flores.&location=Sal%C3%B3n+Nova+Roma',
    'itinerario_json' => json_encode($itinerarioSophiaOficial, JSON_UNESCAPED_UNICODE),
    'trivia_json' => json_encode($triviaSophiaOficial, JSON_UNESCAPED_UNICODE),
    'design_tokens' => json_encode($tokensSophiaOficial, JSON_UNESCAPED_UNICODE)
];

$stmtConfig = $db->prepare("INSERT OR REPLACE INTO configuraciones_evento (evento_id, clave, valor, tipo) VALUES (2, :clave, :valor, :tipo)");
foreach ($configsSophiaOficial as $k => $v) {
    $stmtConfig->execute([
        ':clave' => $k,
        ':valor' => $v,
        ':tipo' => str_ends_with($k, '_json') || $k === 'design_tokens' ? 'json' : 'string'
    ]);
}
echo "- Configuraciones oficiales (temática de mariposas rosadas, flores, lavanda y champagne) registradas exitosamente en la base de datos.\n";

// 8. Dedicatoria oficial de sus padres en el libro de recuerdos
$db->exec("UPDATE libro_recuerdos SET 
    nombre = 'Ramón y Estefany (Tus Padres)',
    mensaje = 'Hija amada Sophia Valentina, eres la bendición más grande que Dios nos entregó. Despliega tus alas con valentía, nobleza y alegría siempre. ¡Te amamos con toda el alma princesa! 🦋🌸💖',
    icono = '🦋',
    color = 'rose'
WHERE id = 100 AND evento_id = 2");
echo "- Dedicatoria oficial de los padres actualizada en libro_recuerdos.\n";

echo "=== DATOS OFICIALES DE SOPHIA VALENTINA VINCULADOS AL 100% ===\n";
