<?php
/**
 * Tarjeta Imprimible con Código QR para Álbum Colaborativo
 * Formatos estándar: 10 x 15 cm (Mesa) y A6 (Recuerdo)
 * Plataforma Multi-Celebración — XV Experience
 */

require_once __DIR__ . '/../app/autoload.php';

use App\Middleware\TenantResolverMiddleware;
use App\Core\CurrentEventContext;

TenantResolverMiddleware::resolve();

$event = CurrentEventContext::get();
$configs = CurrentEventContext::allConfigs();
$eventoId = (int)($event['id'] ?? 1);
$slug = CurrentEventContext::slug() ?? 'angie-xv';
$festejada = htmlspecialchars($event['festejada_nombre'] ?? 'La Quinceañera');
$nombreEvento = htmlspecialchars($event['nombre_celebracion'] ?? 'Mis XV Años');
$isSophia = str_contains($festejada, 'Sophia');

$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$protocol = $isHttps ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$qrTargetUrl = $protocol . $host . '/subir-fotos/' . urlencode($slug);

$qrImage = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($qrTargetUrl) . "&margin=1&color=062E25&bgcolor=FFFFFF";
if ($isSophia) {
    $qrImage = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($qrTargetUrl) . "&margin=1&color=3B1E37&bgcolor=FFFFFF";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarjeta QR Imprimible · <?= $festejada ?></title>
    <!-- Favicon Corona Real -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='30' fill='%23062E25' stroke='%23C8A24A' stroke-width='2.5'/%3E%3Cpath d='M13 43 L17 24 L24 33 L32 15 L40 33 L47 24 L51 43 Z' fill='%23FFD700' stroke='%23FFF0BA' stroke-width='0.8'/%3E%3Cpath d='M13 43 Q32 47 51 43 L51 47 Q32 51 13 47 Z' fill='%23C8A24A'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Great+Vibes&family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-dark: <?= $isSophia ? '#1A0C18' : '#041B16' ?>;
            --primary-color: <?= $isSophia ? '#4A2645' : '#062E25' ?>;
            --accent-gold: #C8A24A;
            --gold-bright: #FFD700;
            --text-dark: #2C2C2C;
            --text-sub: #555555;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #EAEAE5;
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .controls-panel {
            background: #fff;
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            gap: 16px;
            align-items: center;
            margin-bottom: 25px;
        }

        .btn-print {
            background: linear-gradient(135deg, #FFD700, #C8A24A);
            color: #000;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Tarjeta 10x15 cm (Proporción exacta 2:3) */
        .card-frame {
            width: 380px;
            height: 570px;
            background: #FFFDF9;
            border-radius: 14px;
            box-shadow: 0 15px 45px rgba(0,0,0,0.18);
            position: relative;
            padding: 35px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            border: 2px solid var(--accent-gold);
            overflow: hidden;
        }

        .corner-decor {
            position: absolute;
            width: 50px;
            height: 50px;
            border-color: var(--accent-gold);
            pointer-events: none;
        }
        .corner-tl { top: 12px; left: 12px; border-top: 2px solid var(--accent-gold); border-left: 2px solid var(--accent-gold); }
        .corner-tr { top: 12px; right: 12px; border-top: 2px solid var(--accent-gold); border-right: 2px solid var(--accent-gold); }
        .corner-bl { bottom: 12px; left: 12px; border-bottom: 2px solid var(--accent-gold); border-left: 2px solid var(--accent-gold); }
        .corner-br { bottom: 12px; right: 12px; border-bottom: 2px solid var(--accent-gold); border-right: 2px solid var(--accent-gold); }

        .card-header-badge {
            font-family: 'Cinzel Decorative', serif;
            font-size: 0.72rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--accent-gold);
            margin-bottom: 4px;
        }

        .card-title {
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 4px;
        }

        .card-subtitle {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-sub);
            margin-bottom: 15px;
        }

        .qr-wrapper {
            background: #FFF;
            padding: 12px;
            border-radius: 14px;
            border: 1.5px solid rgba(200, 162, 74, 0.4);
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
            display: inline-block;
            margin-bottom: 12px;
        }

        .qr-wrapper img {
            width: 190px;
            height: 190px;
            display: block;
        }

        .callout-title {
            font-family: 'Cinzel Decorative', serif;
            font-size: 1.05rem;
            color: var(--primary-color);
            margin-bottom: 6px;
            font-weight: 700;
        }

        .callout-desc {
            font-size: 0.8rem;
            color: var(--text-sub);
            line-height: 1.45;
            max-width: 280px;
            margin: 0 auto;
        }

        .card-footer {
            font-size: 0.68rem;
            color: #999;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        @media print {
            body { background: transparent; padding: 0; }
            .controls-panel { display: none; }
            .card-frame {
                box-shadow: none;
                border: 1px solid var(--accent-gold);
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="controls-panel">
        <button type="button" class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Tarjeta (10×15 cm)
        </button>
        <span style="font-size:0.8rem; color:#666;">Coloca esta tarjeta en cada mesa del salón para que los invitados compartan sus fotos.</span>
    </div>

    <div class="card-frame">
        <div class="corner-decor corner-tl"></div>
        <div class="corner-decor corner-tr"></div>
        <div class="corner-decor corner-bl"></div>
        <div class="corner-decor corner-br"></div>

        <div>
            <div class="card-header-badge">
                <i class="fas <?= $isSophia ? 'fa-butterfly' : 'fa-crown' ?>"></i> <?= $nombreEvento ?>
            </div>
            <div class="card-title"><?= $festejada ?></div>
            <div class="card-subtitle">Álbum de Recuerdos en Vivo</div>
        </div>

        <div>
            <div class="qr-wrapper">
                <img src="<?= $qrImage ?>" alt="Código QR del Álbum">
            </div>
            <div class="callout-title">Comparte tus Recuerdos</div>
            <p class="callout-desc">
                Escanea el código QR y comparte las fotografías que captures durante esta noche tan especial.<br>
                Cada fotografía será parte de nuestros recuerdos.
            </p>
        </div>

        <div class="card-footer">
            ✨ Se proyectarán en la pantalla de la fiesta ✨
        </div>
    </div>

</body>
</html>
