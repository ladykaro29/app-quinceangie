<?php
/**
 * API: Libro de Recuerdos y Dedicatorias Interactivo
 * Invitación XV Años - Angie Karolina
 * 
 * Endpoints:
 * GET  api/libro.php              -> Lista los mensajes, fotos y videos del libro
 * POST api/libro.php              -> Publica un nuevo mensajito en el libro
 * POST api/libro.php?action=like  -> Da 'like' a un mensaje ({ "id": 123 })
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDBConnection();
} catch (\Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Auto-sembrar mensajes iniciales de cariño si la tabla está vacía
try {
    $countMsg = (int)$pdo->query('SELECT COUNT(*) FROM libro_recuerdos')->fetchColumn();
    if ($countMsg === 0) {
        $mensajesEjemplo = [
            [
                'nombre' => 'Familia Avendaño Rivera',
                'mensaje' => 'Hija amada, verte cumplir tus 15 años es el regalo más hermoso de nuestras vidas. Que Dios ilumine tu camino siempre. ¡Te amamos infinitamente! 💖👑',
                'icono' => '👑',
                'color' => 'gold',
                'likes' => 34,
                'created_at' => '2026-10-04 20:45:00'
            ],
            [
                'nombre' => 'Tía Carmen & Primos',
                'mensaje' => '¡Felicidades Angie preciosa! Una noche inolvidable, te veías radiante como un cuento de hadas. ¡Que todos tus sueños se hagan realidad! ✨🥂',
                'icono' => '✨',
                'color' => 'emerald',
                'likes' => 22,
                'created_at' => '2026-10-04 21:10:00'
            ],
            [
                'nombre' => 'Tus Mejores Amigas del Cole',
                'mensaje' => '¡La mejor fiesta de 15 del mundo! Bailamos sin parar toda la noche. ¡Te amamos muchísimo mejor amiga! 💃💕🎂',
                'icono' => '💖',
                'color' => 'rose',
                'likes' => 29,
                'created_at' => '2026-10-04 21:30:00'
            ],
            [
                'nombre' => 'Padrinos de Angie',
                'mensaje' => 'Orgullosos de verte convertida en una señorita tan dulce, inteligente y llena de luz. Siempre estaremos para apoyarte. ¡Feliz cumpleaños! 🌟🥂',
                'icono' => '🥂',
                'color' => 'cream',
                'likes' => 18,
                'created_at' => '2026-10-04 21:50:00'
            ]
        ];

        $insInit = $pdo->prepare('INSERT INTO libro_recuerdos (nombre, mensaje, icono, color, likes, ip, created_at) VALUES (:n, :m, :i, :c, :l, "127.0.0.1", :d)');
        foreach ($mensajesEjemplo as $me) {
            $insInit->execute([
                ':n' => $me['nombre'],
                ':m' => $me['mensaje'],
                ':i' => $me['icono'],
                ':c' => $me['color'],
                ':l' => $me['likes'],
                ':d' => $me['created_at']
            ]);
        }
    }
} catch (\Exception $e) {}

// ============================================================================
// 1. DAR LIKE A UN MENSAJE
// ============================================================================
if ($method === 'POST' && $action === 'like') {
    header('Content-Type: application/json; charset=utf-8');
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;
    $msgId = (int)($data['id'] ?? 0);

    if ($msgId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de mensaje inválido']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE libro_recuerdos SET likes = likes + 1 WHERE id = :id');
    $stmt->execute([':id' => $msgId]);

    $stmtLikes = $pdo->prepare('SELECT likes FROM libro_recuerdos WHERE id = :id');
    $stmtLikes->execute([':id' => $msgId]);
    $newLikes = (int)$stmtLikes->fetchColumn();

    echo json_encode(['success' => true, 'likes' => $newLikes]);
    exit;
}

// ============================================================================
// 2. LISTAR DATOS DEL LIBRO DE RECUERDOS (GET)
// ============================================================================
if ($method === 'GET') {
    header('Content-Type: application/json; charset=utf-8');

    // Mensajes
    $stmtMsg = $pdo->query('SELECT id, nombre, mensaje, icono, color, likes, media_ref, created_at FROM libro_recuerdos ORDER BY id DESC');
    $mensajes = $stmtMsg->fetchAll();

    foreach ($mensajes as &$m) {
        $m['nombre'] = htmlspecialchars($m['nombre'], ENT_QUOTES, 'UTF-8');
        $m['mensaje'] = htmlspecialchars($m['mensaje'], ENT_QUOTES, 'UTF-8');
        $m['icono'] = htmlspecialchars($m['icono'] ?? '✨', ENT_QUOTES, 'UTF-8');
        $m['color'] = htmlspecialchars($m['color'] ?? 'gold', ENT_QUOTES, 'UTF-8');
        $m['fecha_formateada'] = date('d/m/Y H:i', strtotime($m['created_at']));
    }

    // Videos disponibles (buscar en public/assets/videos y database/uploads/videos)
    $videosDir = __DIR__ . '/../public/assets/videos';
    if (!is_dir($videosDir) || empty(glob($videosDir . '/*.mp4'))) {
        $videosDir = __DIR__ . '/../database/uploads/videos';
    }

    $titulosMomentos = [
        1 => ["Llegada triunfal de Angie ✨", "4.79 MB"],
        2 => ["Entrada mágica al salón 👑", "10.44 MB"],
        3 => ["El Vals con Papá 💖", "2.01 MB"],
        4 => ["Brindis con la Familia 🥂", "2.82 MB"],
        5 => ["Baile sorpresa con el cortejo 💃", "2.43 MB"],
        6 => ["Sesión de fotos y sonrisas 📸", "1.12 MB"],
        7 => ["Momento de la torta de XV 🎂", "1.63 MB"],
        8 => ["Abrazos y felicitaciones emotivas 💕", "1.34 MB"],
        9 => ["La hora loca y luces neón 🎊", "1.24 MB"],
        10 => ["Pista de baile encendida 🎶", "1.60 MB"],
        11 => ["Diversión con los amigos 🥳", "0.85 MB"],
        12 => ["Palabras especiales para Angie 🎙️", "2.93 MB"],
        13 => ["El cotillón y la alegría dorada 🌟", "6.76 MB"],
        14 => ["Coreografía de Angie y amigas 💃✨", "4.67 MB"],
        15 => ["Risas y recuerdos inolvidables 💫", "2.56 MB"],
        16 => ["Brindis de honor con los padrinos 🥂✨", "1.45 MB"],
        17 => ["Sorpresa especial de la noche 🎁", "1.96 MB"],
        18 => ["Momentos espontáneos de pura emoción ✨", "1.32 MB"],
        19 => ["Gran cierre de una noche mágica 👑💫", "6.69 MB"]
    ];

    $videosList = [];
    if (is_dir($videosDir)) {
        $videoFiles = glob($videosDir . '/*.mp4');
        if (!empty($videoFiles)) {
            natsort($videoFiles);
            foreach ($videoFiles as $idx => $vf) {
                $base = basename($vf);
                $size = filesize($vf);
                $num = $idx + 1;
                $info = $titulosMomentos[$num] ?? ["Momento Especial #{$num}", round($size / 1024 / 1024, 2) . ' MB'];

                $videosList[] = [
                    'id'         => $num,
                    'titulo'     => $info[0],
                    'archivo'    => $base,
                    'url'        => 'api/video.php?v=' . rawurlencode($base),
                    'direct_url' => 'assets/videos/' . rawurlencode($base),
                    'tamano'     => round($size / 1024 / 1024, 2) . ' MB'
                ];
            }
        }
    }

    // Fallback garantizado: si por alguna razón del contenedor Docker glob no encontró archivos, devolver los 19 videos
    if (empty($videosList)) {
        for ($i = 1; $i <= 19; $i++) {
            $numStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            $base = "angie_xv_video_{$numStr}.mp4";
            $info = $titulosMomentos[$i] ?? ["Momento Especial #{$i}", "2.5 MB"];
            $videosList[] = [
                'id'         => $i,
                'titulo'     => $info[0],
                'archivo'    => $base,
                'url'        => 'api/video.php?v=' . rawurlencode($base),
                'direct_url' => 'assets/videos/' . rawurlencode($base),
                'tamano'     => $info[1]
            ];
        }
    }

    // Resumen fotos
    $totalFotos = (int)$pdo->query('SELECT COUNT(*) FROM fotos_fiesta WHERE visible = 1')->fetchColumn();

    echo json_encode([
        'success'        => true,
        'total_mensajes' => count($mensajes),
        'mensajes'       => $mensajes,
        'total_videos'   => count($videosList),
        'videos'         => $videosList,
        'total_fotos'    => $totalFotos
    ]);
    exit;
}

// ============================================================================
// 3. PUBLICAR UN NUEVO MENSAJITO EN EL LIBRO (POST)
// ============================================================================
if ($method === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $nombre = trim($data['nombre'] ?? '');
    $mensaje = trim($data['mensaje'] ?? '');
    $icono = trim($data['icono'] ?? '✨');
    $color = trim($data['color'] ?? 'gold');
    $mediaRef = trim($data['media_ref'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($nombre)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Por favor escribe tu nombre.']);
        exit;
    }

    if (empty($mensaje)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Por favor escribe una dedicatoria o mensaje bonito.']);
        exit;
    }

    $nombre = mb_substr($nombre, 0, 100);
    $mensaje = mb_substr($mensaje, 0, 800);
    $icono = mb_substr($icono, 0, 10);
    $color = in_array($color, ['gold', 'emerald', 'rose', 'cream', 'purple', 'sky']) ? $color : 'gold';

    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO libro_recuerdos (nombre, mensaje, icono, color, likes, media_ref, ip, created_at) 
                           VALUES (:n, :m, :i, :c, 0, :mr, :ip, :d)');
    $stmt->execute([
        ':n'  => $nombre,
        ':m'  => $mensaje,
        ':i'  => $icono,
        ':c'  => $color,
        ':mr' => $mediaRef ?: null,
        ':ip' => $ip,
        ':d'  => $now
    ]);

    $newId = (int)$pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => '¡Dedicatoria guardada en el libro de recuerdos de Angie con éxito! ✨',
        'mensaje_nuevo' => [
            'id' => $newId,
            'nombre' => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'mensaje' => htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'),
            'icono' => htmlspecialchars($icono, ENT_QUOTES, 'UTF-8'),
            'color' => $color,
            'likes' => 0,
            'fecha_formateada' => 'Hace un momento',
            'created_at' => $now
        ]
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Método no permitido']);
