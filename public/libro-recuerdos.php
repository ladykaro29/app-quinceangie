<?php
/**
 * Libro Mágico de Fantasía & Recuerdos Interactivo
 * Mis XV Años — Angie Karolina Avendaño Rivera
 * 
 * Experiencia de Libro 3D con animación de paso de páginas:
 * - Encuadernación de grimorio real en cuero esmeralda y filigranas de oro
 * - Paso de páginas 3D interactivo con sonido realista de pergamino (Web Audio API)
 * - Navegación por capítulos: Portada Real, Fotos Polaroid (160), Videos (19) y Dedicatorias
 * - Música de fondo con reproductor vinilo dinámico
 * - Modal para dejar dedicatorias en tiempo real con stickers y confeti
 * - Visor Lightbox a pantalla completa
 */

$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$protocol = $isHttps ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . $host;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>📖 Libro Mágico de Recuerdos · Mis XV Años · Angie Karolina</title>
    
    <!-- Metaetiquetas SEO y Open Graph para compartir en WhatsApp -->
    <meta name="description" content="Abre el Libro Mágico de Fantasía de los XV Años de Angie Karolina: voltea sus páginas, revive 160 fotos, 19 videos y déjale tu dedicatoria.">
    <meta property="og:title" content="📖 Libro Mágico de Recuerdos · Mis XV Años Angie Karolina">
    <meta property="og:description" content="¡Pasa las páginas del libro mágico de Angie! Mira todas las fotos, videos exclusivos y escribe tu dedicatoria ✨👑">
    <meta property="og:image" content="<?= $baseUrl ?>/angie-xv.jpg">
    <meta property="og:type" content="website">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon.png">

    <!-- Tipografías elegantes de Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Great+Vibes&family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Caveat:wght@600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Confetti JS para celebraciones -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <style>
        :root {
            --verde-reino: #021410;
            --verde-esmeralda: #062E25;
            --verde-bosque: #0B4538;
            --cuero-libro: #05211A;
            --dorado-puro: #FFD700;
            --dorado: #C8A24A;
            --dorado-claro: #F5E6C4;
            --dorado-brillante: #FFE89E;
            --dorado-oscuro: #8C6B1C;
            --pergamino: #FBF4E4;
            --pergamino-borde: #E8D9B8;
            --pergamino-sombra: #D4C198;
            --tinta: #2C2216;
            --tinta-suave: #5D4A36;
            --font-script: 'Great Vibes', cursive;
            --font-serif: 'Playfair Display', Georgia, serif;
            --font-sans: 'Montserrat', sans-serif;
            --font-hand: 'Caveat', cursive, sans-serif;
            --font-royal: 'Cinzel Decorative', serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--verde-reino);
            background-image: 
                radial-gradient(ellipse at 50% 20%, rgba(11, 69, 56, 0.7) 0%, rgba(2, 20, 16, 0.98) 75%),
                radial-gradient(circle at 15% 85%, rgba(200, 162, 74, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(200, 162, 74, 0.08) 0%, transparent 45%);
            background-attachment: fixed;
            color: var(--pergamino);
            font-family: var(--font-sans);
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        /* Canvas de estrellas mágicas */
        #magicCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 1;
        }

        /* Contenedor general */
        .stage-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1320px;
            padding: 16px 14px 60px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Barra superior */
        .top-bar {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--dorado-claro);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 18px;
            background: rgba(6, 46, 37, 0.7);
            border: 1px solid rgba(200, 162, 74, 0.4);
            border-radius: 25px;
            backdrop-filter: blur(8px);
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: var(--dorado);
            color: #021410;
            transform: translateX(-3px);
            box-shadow: 0 4px 15px rgba(200, 162, 74, 0.4);
        }

        /* Widget de música estilo vinilo */
        .music-pill {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(6, 46, 37, 0.75);
            border: 1.5px solid rgba(200, 162, 74, 0.45);
            border-radius: 40px;
            padding: 6px 16px 6px 8px;
            backdrop-filter: blur(10px);
            cursor: pointer;
            transition: all 0.3s;
            user-select: none;
        }

        .music-pill:hover {
            border-color: var(--dorado-puro);
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.3);
        }

        .vinyl-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: radial-gradient(circle, #222 20%, #111 60%, #000 100%);
            border: 2px solid var(--dorado);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .vinyl-circle.spinning {
            animation: spinVinyl 3.5s linear infinite;
        }

        .vinyl-inner-dot {
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: var(--dorado);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.5rem;
            color: #021410;
        }

        @keyframes spinVinyl {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .music-meta {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .music-text-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--dorado-brillante);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .music-text-sub {
            font-size: 0.68rem;
            color: rgba(255,255,255,0.7);
        }

        .eq-bars {
            display: inline-flex;
            align-items: flex-end;
            gap: 2px;
            height: 12px;
        }

        .eq-bar {
            width: 2.5px;
            background: var(--dorado-puro);
            border-radius: 1px;
            height: 4px;
        }

        .spinning .eq-bar:nth-child(1) { animation: eqA 0.7s infinite alternate ease-in-out; }
        .spinning .eq-bar:nth-child(2) { animation: eqA 0.9s infinite alternate 0.2s ease-in-out; }
        .spinning .eq-bar:nth-child(3) { animation: eqA 0.6s infinite alternate 0.4s ease-in-out; }

        @keyframes eqA {
            0% { height: 3px; }
            100% { height: 12px; }
        }

        /* Pestañas estilo Marcapáginas / Separadores del Libro */
        .book-bookmarks-nav {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 6px;
            margin-bottom: 2px;
            flex-wrap: wrap;
            z-index: 10;
        }

        .bookmark-tab {
            background: linear-gradient(180deg, rgba(6, 46, 37, 0.9) 0%, rgba(4, 27, 22, 0.95) 100%);
            border: 1.5px solid rgba(200, 162, 74, 0.45);
            border-bottom: none;
            color: var(--dorado-claro);
            padding: 8px 18px 6px 18px;
            border-radius: 14px 14px 0 0;
            font-size: 0.84rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-family: var(--font-sans);
            position: relative;
            top: 2px;
        }

        .bookmark-tab:hover {
            color: #FFF;
            background: rgba(10, 60, 49, 0.95);
            transform: translateY(-3px);
            border-color: var(--dorado);
        }

        .bookmark-tab.active {
            background: linear-gradient(180deg, #FFD700 0%, #C8A24A 100%);
            color: #021410;
            font-weight: 700;
            border-color: var(--dorado-puro);
            box-shadow: 0 -4px 15px rgba(255, 215, 0, 0.35);
            transform: translateY(-4px);
            z-index: 15;
        }

        .btn-tab-dedication {
            background: linear-gradient(180deg, #FF6B8B 0%, #D81B60 100%) !important;
            color: #FFF !important;
            border-color: #FF8DA7 !important;
            font-weight: 700 !important;
            box-shadow: 0 -4px 16px rgba(216, 27, 96, 0.45) !important;
        }

        /* ============================================================
           ESCENARIO 3D DEL LIBRO DE FANTASÍA
           ============================================================ */
        .fantasy-stage {
            width: 100%;
            perspective: 2600px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        /* Cubierta y sombras del Gran Libro */
        .fantasy-book-casing {
            position: relative;
            width: 100%;
            max-width: 1180px;
            min-height: 720px;
            background: var(--cuero-libro);
            border-radius: 20px;
            padding: 14px;
            box-shadow: 
                0 30px 90px rgba(0, 0, 0, 0.95),
                0 10px 30px rgba(0, 0, 0, 0.7),
                inset 0 0 40px rgba(0, 0, 0, 0.8),
                0 0 0 3px rgba(200, 162, 74, 0.35);
            display: flex;
            flex-direction: column;
        }

        /* Adornos esquineros de filigrana dorada de la encuadernación */
        .casing-corner {
            position: absolute;
            width: 44px;
            height: 44px;
            pointer-events: none;
            opacity: 0.85;
            z-index: 5;
        }
        .c-tl { top: 6px; left: 6px; border-top: 3px solid var(--dorado-puro); border-left: 3px solid var(--dorado-puro); border-radius: 12px 0 0 0; }
        .c-tr { top: 6px; right: 6px; border-top: 3px solid var(--dorado-puro); border-right: 3px solid var(--dorado-puro); border-radius: 0 12px 0 0; }
        .c-bl { bottom: 6px; left: 6px; border-bottom: 3px solid var(--dorado-puro); border-left: 3px solid var(--dorado-puro); border-radius: 0 0 0 12px; }
        .c-br { bottom: 6px; right: 6px; border-bottom: 3px solid var(--dorado-puro); border-right: 3px solid var(--dorado-puro); border-radius: 0 0 12px 0; }

        /* Cinta marcapáginas de seda colgante */
        .ribbon-bookmark {
            position: absolute;
            bottom: -32px;
            left: 50%;
            transform: translateX(-50%);
            width: 28px;
            height: 52px;
            background: linear-gradient(180deg, #C8A24A 0%, #FFD700 70%, #9E7D23 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.6);
            z-index: 30;
            clip-path: polygon(0% 0%, 100% 0%, 100% 80%, 50% 100%, 0% 80%);
            animation: swayRibbon 4s ease-in-out infinite alternate;
        }

        @keyframes swayRibbon {
            0% { transform: translateX(-50%) rotate(-2deg); }
            100% { transform: translateX(-50%) rotate(2deg); }
        }

        /* Contenedor del Libro Abierto (Páginas Izquierda y Derecha) */
        .book-spread {
            position: relative;
            width: 100%;
            min-height: 680px;
            display: flex;
            background: var(--pergamino);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: inset 0 0 60px rgba(140, 107, 28, 0.15);
            transition: all 0.3s ease;
        }

        /* Lomo central con sombra y pliegue de páginas */
        .book-spine-crease {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            background: linear-gradient(to right, 
                rgba(0,0,0,0.22) 0%, 
                rgba(0,0,0,0.06) 35%, 
                rgba(255,255,255,0.25) 50%, 
                rgba(0,0,0,0.06) 65%, 
                rgba(0,0,0,0.22) 100%
            );
            pointer-events: none;
            z-index: 20;
        }

        /* Costura central decorativa */
        .book-spine-stitch {
            position: absolute;
            top: 15px;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            border-left: 1.5px dashed rgba(140, 107, 28, 0.45);
            pointer-events: none;
            z-index: 21;
        }

        /* Cada página del libro */
        .book-page {
            flex: 1;
            padding: 30px 28px 45px 28px;
            color: var(--tinta);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: linear-gradient(135deg, #FFFDF7 0%, #F9F2E2 100%);
            min-height: 680px;
        }

        .book-page-left {
            border-right: 1px solid rgba(140, 107, 28, 0.2);
            box-shadow: inset -25px 0 35px -15px rgba(0,0,0,0.07);
        }

        .book-page-right {
            border-left: 1px solid rgba(140, 107, 28, 0.2);
            box-shadow: inset 25px 0 35px -15px rgba(0,0,0,0.07);
        }

        /* Marco dorado interior de cada página */
        .page-inner-border {
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            bottom: 12px;
            border: 1px solid rgba(200, 162, 74, 0.4);
            pointer-events: none;
            border-radius: 6px;
        }
        .page-inner-border::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            right: 3px;
            bottom: 3px;
            border: 1px dashed rgba(200, 162, 74, 0.25);
            border-radius: 4px;
        }

        /* Floreos en las esquinas de página */
        .page-flourish {
            position: absolute;
            width: 22px;
            height: 22px;
            color: var(--dorado);
            opacity: 0.65;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .pf-tl { top: 14px; left: 14px; }
        .pf-tr { top: 14px; right: 14px; }
        .pf-bl { bottom: 14px; left: 14px; }
        .pf-br { bottom: 14px; right: 14px; }

        /* Número de página ornamental */
        .page-number {
            font-family: var(--font-royal);
            font-size: 0.8rem;
            color: var(--dorado-oscuro);
            text-align: center;
            letter-spacing: 2px;
            margin-top: 10px;
            z-index: 5;
        }

        /* ============================================================
           HOJA 3D DE TRANSICIÓN DE VOLTEO (PAGE FLIP ANIMATION)
           ============================================================ */
        .flipping-sheet {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 50%;
            background: linear-gradient(135deg, #FFFDF7 0%, #F5E8D0 100%);
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
            transform-style: preserve-3d;
            transition: transform 0.65s cubic-bezier(0.645, 0.045, 0.355, 1);
            pointer-events: none;
            z-index: 40;
            display: none;
        }

        .flipping-sheet.flip-forward {
            right: 0;
            transform-origin: left center;
            display: block;
            animation: flipPageForward 0.65s cubic-bezier(0.645, 0.045, 0.355, 1) forwards;
        }

        .flipping-sheet.flip-backward {
            left: 0;
            transform-origin: right center;
            display: block;
            animation: flipPageBackward 0.65s cubic-bezier(0.645, 0.045, 0.355, 1) forwards;
        }

        @keyframes flipPageForward {
            0% {
                transform: rotateY(0deg);
                box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            }
            50% {
                box-shadow: -20px 25px 60px rgba(0,0,0,0.6);
            }
            100% {
                transform: rotateY(-180deg);
                box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            }
        }

        @keyframes flipPageBackward {
            0% {
                transform: rotateY(0deg);
                box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            }
            50% {
                box-shadow: 20px 25px 60px rgba(0,0,0,0.6);
            }
            100% {
                transform: rotateY(180deg);
                box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            }
        }

        /* ============================================================
           BOTONES Y CONTROLES DE PASO DE PÁGINAS
           ============================================================ */
        .page-turn-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFD700 0%, #C8A24A 100%);
            color: #021410;
            border: 2px solid #FFFDF5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.6), 0 0 15px rgba(255, 215, 0, 0.4);
            z-index: 50;
            transition: all 0.25s ease;
        }

        .page-turn-btn:hover {
            transform: translateY(-50%) scale(1.12);
            background: #FFF;
            color: #062E25;
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.65);
        }

        .page-turn-prev { left: -26px; }
        .page-turn-next { right: -26px; }

        .page-turn-btn:disabled {
            opacity: 0.35;
            pointer-events: none;
            transform: translateY(-50%) scale(0.95);
        }

        /* Barra inferior de estado y navegación rápida */
        .book-bottom-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 900px;
            margin-top: 18px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .page-indicator-pill {
            background: rgba(6, 46, 37, 0.8);
            border: 1px solid rgba(200, 162, 74, 0.4);
            border-radius: 20px;
            padding: 6px 18px;
            font-family: var(--font-royal);
            font-size: 0.85rem;
            color: var(--dorado-brillante);
            letter-spacing: 1.5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }

        .quick-page-jump {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .jump-btn {
            background: rgba(6, 46, 37, 0.6);
            border: 1px solid rgba(200, 162, 74, 0.3);
            color: var(--dorado-claro);
            padding: 5px 12px;
            border-radius: 16px;
            font-size: 0.78rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .jump-btn:hover {
            background: var(--dorado);
            color: #021410;
        }

        /* ============================================================
           CONTENIDOS ESPECÍFICOS DE LAS PÁGINAS DEL LIBRO
           ============================================================ */
        
        /* 1. PORTADA & BIENVENIDA */
        .cover-page-content {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 20px 10px;
        }

        .royal-crest-icon {
            font-size: 3.2rem;
            color: var(--dorado);
            margin-bottom: 8px;
            filter: drop-shadow(0 4px 8px rgba(200, 162, 74, 0.3));
        }

        .book-main-title {
            font-family: var(--font-script);
            font-size: clamp(2.8rem, 5vw, 4.2rem);
            color: #062E25;
            line-height: 1.05;
            margin-bottom: 2px;
            text-shadow: 0 2px 4px rgba(200, 162, 74, 0.3);
        }

        .book-main-sub {
            font-family: var(--font-royal);
            font-size: clamp(0.85rem, 1.8vw, 1.15rem);
            letter-spacing: 4px;
            color: var(--dorado-oscuro);
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .vintage-gold-rule {
            width: 130px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--dorado), transparent);
            margin: 10px auto;
        }

        .prologue-quote {
            font-family: var(--font-serif);
            font-style: italic;
            font-size: 0.96rem;
            line-height: 1.6;
            color: var(--tinta-suave);
            max-width: 440px;
            margin: 12px auto;
        }

        .oval-photo-frame {
            width: 170px;
            height: 220px;
            border-radius: 50% / 40%;
            border: 4px solid var(--dorado);
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.35), inset 0 0 15px rgba(0,0,0,0.2);
            margin: 14px auto;
            position: relative;
        }

        .oval-photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-open-book-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: linear-gradient(135deg, #062E25 0%, #0B4538 100%);
            border: 1.5px solid var(--dorado);
            color: var(--dorado-brillante);
            border-radius: 30px;
            font-family: var(--font-royal);
            font-size: 0.85rem;
            letter-spacing: 1px;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.25s;
        }

        .btn-open-book-cta:hover {
            background: var(--dorado);
            color: #021410;
            transform: scale(1.04);
            box-shadow: 0 6px 20px rgba(200, 162, 74, 0.4);
        }

        /* 2. PÁGINAS DE FOTOS POLAROID (ÁLBUM SCRAPBOOK) */
        .chapter-header {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 5;
        }

        .chapter-num {
            font-family: var(--font-royal);
            font-size: 0.72rem;
            letter-spacing: 3px;
            color: var(--dorado-oscuro);
            text-transform: uppercase;
        }

        .chapter-title {
            font-family: var(--font-serif);
            font-size: 1.35rem;
            color: #062E25;
            font-weight: 700;
        }

        .photos-book-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 10px;
            z-index: 5;
        }

        .book-polaroid {
            background: #FFF;
            padding: 8px 8px 12px 8px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.18), 0 1px 3px rgba(0,0,0,0.1);
            cursor: pointer;
            position: relative;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0,0,0,0.06);
        }

        .book-polaroid:hover {
            transform: scale(1.05) rotate(0deg) !important;
            box-shadow: 0 12px 28px rgba(0,0,0,0.35);
            z-index: 10;
        }

        .book-polaroid:nth-child(odd) { transform: rotate(-1.5deg); }
        .book-polaroid:nth-child(even) { transform: rotate(1.5deg); }

        .polaroid-gold-pin {
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #FFE58F, #C8A24A 70%, #684B12 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.35);
            z-index: 2;
        }

        .book-polaroid-img {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #111;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .book-polaroid-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s;
        }

        .book-polaroid:hover .book-polaroid-img img {
            transform: scale(1.06);
        }

        .book-polaroid-caption {
            font-family: var(--font-hand);
            font-size: 0.95rem;
            color: #222;
            line-height: 1.15;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 0 2px;
        }

        .book-polaroid-likes {
            font-size: 0.72rem;
            color: #D81B60;
            font-weight: 700;
            text-align: center;
            margin-top: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
        }

        /* 3. PÁGINAS DE VIDEOS CINEMATOGRÁFICOS */
        .videos-book-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
            z-index: 5;
        }

        .book-video-card {
            background: rgba(6, 46, 37, 0.04);
            border: 1.5px solid rgba(200, 162, 74, 0.5);
            border-radius: 12px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            position: relative;
        }

        .book-video-frame {
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--dorado);
            position: relative;
        }

        .book-video-frame video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .book-video-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .book-video-title {
            font-family: var(--font-serif);
            font-size: 0.92rem;
            font-weight: 700;
            color: #062E25;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-fullscreen-cine {
            background: #062E25;
            color: var(--dorado-brillante);
            border: 1px solid var(--dorado);
            font-size: 0.72rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .btn-fullscreen-cine:hover {
            background: var(--dorado);
            color: #021410;
        }

        /* 4. PÁGINAS DEL LIBRO DE FIRMAS & DEDICATORIAS */
        .guestbook-page-entries {
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 5;
        }

        .guest-entry-postit {
            background: #FFF;
            padding: 12px 14px;
            border-radius: 8px;
            border-left: 4px solid var(--dorado);
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            position: relative;
        }

        .entry-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .entry-icon {
            font-size: 1.1rem;
        }

        .entry-author {
            font-family: var(--font-serif);
            font-size: 0.9rem;
            font-weight: 700;
            color: #062E25;
        }

        .entry-date {
            font-size: 0.68rem;
            color: #888;
            margin-left: auto;
        }

        .entry-msg {
            font-family: var(--font-hand);
            font-size: 1.1rem;
            color: #2C2216;
            line-height: 1.25;
            word-break: break-word;
        }

        .entry-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
            font-size: 0.72rem;
        }

        .btn-like-entry {
            background: none;
            border: none;
            color: #D81B60;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 6px;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .btn-like-entry:hover {
            transform: scale(1.15);
        }

        .btn-leave-signature {
            background: linear-gradient(135deg, #FF6B8B 0%, #D81B60 100%);
            color: #FFF;
            border: none;
            padding: 9px 18px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            box-shadow: 0 4px 15px rgba(216, 27, 96, 0.4);
            margin: 10px auto 0 auto;
            transition: all 0.2s;
        }

        .btn-leave-signature:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(216, 27, 96, 0.6);
        }

        /* ============================================================
           MODAL PARA ESCRIBIR MENSAJITO
           ============================================================ */
        .msg-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(10px);
            z-index: 100000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .msg-modal-box {
            background: linear-gradient(145deg, #062E25, #004D38);
            border: 2px solid var(--dorado-puro);
            border-radius: 24px;
            max-width: 500px;
            width: 100%;
            padding: 26px 22px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.85), inset 0 0 40px rgba(200, 162, 74, 0.15);
            position: relative;
            color: #FFF;
        }

        .modal-close-btn {
            position: absolute;
            top: 14px;
            right: 16px;
            background: rgba(255,255,255,0.1);
            border: none;
            color: #FFF;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-title {
            font-family: var(--font-royal);
            font-size: 1.2rem;
            color: var(--dorado-brillante);
            text-align: center;
            margin-bottom: 2px;
        }

        .modal-subtitle {
            font-family: var(--font-serif);
            font-size: 0.82rem;
            color: var(--dorado-claro);
            text-align: center;
            margin-bottom: 16px;
            font-style: italic;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 0.78rem;
            color: var(--dorado-claro);
            font-weight: 600;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .form-input, .form-textarea {
            width: 100%;
            background: rgba(0,0,0,0.35);
            border: 1.5px solid rgba(200, 162, 74, 0.4);
            border-radius: 10px;
            padding: 10px 14px;
            color: #FFF;
            font-family: var(--font-sans);
            font-size: 0.88rem;
            outline: none;
        }

        .form-input:focus, .form-textarea:focus {
            border-color: var(--dorado-puro);
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
        }

        .emoji-selector {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .emoji-option {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            border: 1.5px solid transparent;
            font-size: 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .emoji-option.selected {
            border-color: var(--dorado-puro);
            background: rgba(255, 215, 0, 0.25);
            transform: scale(1.15);
        }

        .quick-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 4px;
        }

        .quick-chip {
            background: rgba(200, 162, 74, 0.15);
            border: 1px solid rgba(200, 162, 74, 0.35);
            color: var(--dorado-claro);
            font-size: 0.72rem;
            padding: 3px 8px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quick-chip:hover {
            background: var(--dorado);
            color: #021410;
        }

        .btn-submit-dedication {
            width: 100%;
            background: linear-gradient(135deg, #FFD700 0%, #C8A24A 100%);
            color: #021410;
            border: none;
            padding: 12px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.92rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 18px rgba(200, 162, 74, 0.4);
            margin-top: 8px;
            transition: all 0.25s;
        }

        .btn-submit-dedication:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 25px rgba(255, 215, 0, 0.6);
        }

        /* ============================================================
           MODAL LIGHTBOX FULLSCREEN (FOTOS Y VIDEOS)
           ============================================================ */
        .lightbox-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.95);
            backdrop-filter: blur(12px);
            z-index: 200000;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 16px;
            user-select: none;
        }

        .lightbox-close-btn {
            position: absolute;
            top: 20px;
            right: 24px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: #FFF;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .lightbox-counter-badge {
            position: absolute;
            top: 22px;
            left: 24px;
            background: rgba(0,0,0,0.6);
            border: 1px solid var(--dorado);
            color: var(--dorado-claro);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.84rem;
            font-family: var(--font-royal);
            letter-spacing: 1px;
        }

        .lightbox-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(0,0,0,0.7);
            border: 1.5px solid var(--dorado);
            color: #FFF;
            font-size: 1.3rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s;
            z-index: 10;
        }

        .lightbox-prev-btn { left: 20px; }
        .lightbox-next-btn { right: 20px; }

        .lightbox-media-container {
            max-width: 90vw;
            max-height: 72vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lightbox-info {
            text-align: center;
            margin-top: 14px;
            max-width: 600px;
        }

        .lightbox-title {
            font-family: var(--font-serif);
            font-size: 1.15rem;
            color: var(--dorado-brillante);
            font-weight: 700;
        }

        .lightbox-text {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.8);
            margin-top: 2px;
        }

        .btn-download-media {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 6px 16px;
            background: rgba(200, 162, 74, 0.2);
            border: 1px solid var(--dorado);
            border-radius: 20px;
            color: var(--dorado-claro);
            text-decoration: none;
            font-size: 0.78rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-download-media:hover {
            background: var(--dorado);
            color: #021410;
        }

        /* ============================================================
           RESPONSIVIDAD MÓVIL (PANTALLAS < 768px)
           ============================================================ */
        @media (max-width: 768px) {
            .book-spread {
                flex-direction: column;
                min-height: auto;
            }

            .book-page-right {
                display: none; /* En celular muestra una sola página amplia y elegante a la vez */
                border-left: none;
            }

            .book-page-left {
                border-right: none;
                min-height: 600px;
                padding: 24px 16px 35px 16px;
            }

            .book-spine-crease, .book-spine-stitch {
                display: none;
            }

            .flipping-sheet {
                width: 100%;
            }

            .page-turn-btn {
                width: 44px;
                height: 44px;
                font-size: 1.1rem;
            }

            .page-turn-prev { left: -10px; }
            .page-turn-next { right: -10px; }

            .photos-book-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .book-main-title {
                font-size: 3rem;
            }

            .oval-photo-frame {
                width: 140px;
                height: 180px;
            }
        }
    </style>
</head>
<body>

    <!-- Canvas de polvo de hadas / destellos mágicos -->
    <canvas id="magicCanvas"></canvas>

    <!-- Audio de fondo (Canción de la fiesta) -->
    <audio id="bgMusic" loop preload="auto">
        <source src="musica.mp3" type="audio/mpeg">
        <source src="musica.m4a" type="audio/mp4">
    </audio>

    <div class="stage-wrapper">

        <!-- Barra Superior -->
        <header class="top-bar">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver a la Invitación
            </a>

            <!-- Vinilo giratorio de música -->
            <div class="music-pill" id="musicToggleWidget" title="Reproducir / Pausar Música">
                <div class="vinyl-circle" id="vinylCircle">
                    <div class="vinyl-inner-dot">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
                <div class="music-meta">
                    <div class="music-text-title">
                        <span id="musicStateText">Música de la Fiesta</span>
                        <div class="eq-bars" id="eqBars">
                            <span class="eq-bar"></span>
                            <span class="eq-bar"></span>
                            <span class="eq-bar"></span>
                        </div>
                    </div>
                    <div class="music-text-sub">Mis XV Años · Angie Karolina</div>
                </div>
            </div>
        </header>

        <!-- Pestañas Marcapáginas (Saltar entre capítulos) -->
        <nav class="book-bookmarks-nav">
            <button class="bookmark-tab active" data-chapter="portada">
                <i class="fas fa-crown"></i> Portada
            </button>
            <button class="bookmark-tab" data-chapter="fotos">
                <i class="fas fa-camera-retro"></i> Fotos (160)
            </button>
            <button class="bookmark-tab" data-chapter="videos">
                <i class="fas fa-film"></i> Videos (19)
            </button>
            <button class="bookmark-tab" data-chapter="firmas">
                <i class="fas fa-feather-alt"></i> Firmas & Deseos
            </button>
            <button class="bookmark-tab btn-tab-dedication" id="btnOpenDedicationModal">
                <i class="fas fa-heart"></i> ¡Dejar Mensaje!
            </button>
        </nav>

        <!-- Escenario 3D del Libro de Fantasía -->
        <div class="fantasy-stage">

            <!-- Botones laterales de voltear página -->
            <button class="page-turn-btn page-turn-prev" id="btnTurnPrev" title="Página Anterior (Flecha Izquierda)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="page-turn-btn page-turn-next" id="btnTurnNext" title="Página Siguiente (Flecha Derecha)">
                <i class="fas fa-chevron-right"></i>
            </button>

            <!-- Encuadernación en Cuero Esmeralda con Filigranas -->
            <div class="fantasy-book-casing">
                <div class="casing-corner c-tl"></div>
                <div class="casing-corner c-tr"></div>
                <div class="casing-corner c-bl"></div>
                <div class="casing-corner c-br"></div>

                <!-- Cinta marcapáginas -->
                <div class="ribbon-bookmark"></div>

                <!-- Libro Abierto (Spread con 2 páginas) -->
                <div class="book-spread" id="bookSpread">
                    <!-- Sombra y lomo central -->
                    <div class="book-spine-crease"></div>
                    <div class="book-spine-stitch"></div>

                    <!-- Hoja de volteo 3D dinámica -->
                    <div class="flipping-sheet" id="flippingSheet"></div>

                    <!-- PÁGINA IZQUIERDA -->
                    <div class="book-page book-page-left" id="pageLeft">
                        <div class="page-inner-border"></div>
                        <div class="page-flourish pf-tl">❧</div>
                        <div class="page-flourish pf-tr">☙</div>
                        <div class="page-flourish pf-bl">❧</div>
                        <div class="page-flourish pf-br">☙</div>

                        <div id="pageLeftContent" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                            <!-- Inyectado dinámicamente -->
                        </div>

                        <div class="page-number" id="pageLeftNumber">I</div>
                    </div>

                    <!-- PÁGINA DERECHA -->
                    <div class="book-page book-page-right" id="pageRight">
                        <div class="page-inner-border"></div>
                        <div class="page-flourish pf-tl">❧</div>
                        <div class="page-flourish pf-tr">☙</div>
                        <div class="page-flourish pf-bl">❧</div>
                        <div class="page-flourish pf-br">☙</div>

                        <div id="pageRightContent" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                            <!-- Inyectado dinámicamente -->
                        </div>

                        <div class="page-number" id="pageRightNumber">II</div>
                    </div>
                </div>

            </div>

            <!-- Navegación inferior y selector rápido de páginas -->
            <footer class="book-bottom-nav">
                <div class="page-indicator-pill" id="pageIndicatorText">
                    PÁGINAS 1 - 2 · PORTADA REAL
                </div>

                <div class="quick-page-jump">
                    <button class="jump-btn" id="btnJumpCover">👑 Portada</button>
                    <button class="jump-btn" id="btnJumpPhotos">📸 Fotos</button>
                    <button class="jump-btn" id="btnJumpVideos">🎬 Videos</button>
                    <button class="jump-btn" id="btnJumpSignatures">💌 Firmas</button>
                </div>
            </footer>

        </div>

    </div>

    <!-- ============================================================
         MODAL: ESCRIBIR NUEVA DEDICATORIA EN EL LIBRO
         ============================================================ -->
    <div class="msg-modal-overlay" id="writeMessageModal">
        <div class="msg-modal-box">
            <button type="button" class="modal-close-btn" id="btnCloseWriteModal" title="Cerrar">&times;</button>
            
            <div class="modal-title">Escribir Dedicatoria</div>
            <div class="modal-subtitle">Libro de Recuerdos de Angie ✨</div>

            <form id="formNewDedication">
                <div class="form-group">
                    <label class="form-label" for="dedicationName">Tu Nombre o Familia:</label>
                    <input type="text" id="dedicationName" class="form-input" placeholder="Ej: Tía Sofía, Amigos del Colegio..." required maxlength="100">
                </div>

                <div class="form-group">
                    <label class="form-label">Elige un Sticker / Emoción:</label>
                    <div class="emoji-selector" id="emojiSelector">
                        <div class="emoji-option selected" data-emoji="👑" title="Corona Real">👑</div>
                        <div class="emoji-option" data-emoji="💖" title="Con amor">💖</div>
                        <div class="emoji-option" data-emoji="✨" title="Mágico">✨</div>
                        <div class="emoji-option" data-emoji="🥂" title="Brindis">🥂</div>
                        <div class="emoji-option" data-emoji="🎂" title="Feliz Cumple">🎂</div>
                        <div class="emoji-option" data-emoji="💃" title="Fiesta">💃</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Dedicatorias Rápidas:</label>
                    <div class="quick-chips">
                        <span class="quick-chip" data-text="¡Te veías hermosa como una reina! 👑✨">¡Te veías como una reina! 👑✨</span>
                        <span class="quick-chip" data-text="¡Una noche mágica e inolvidable! 💃🥂">¡Noche mágica e inolvidable! 💃🥂</span>
                        <span class="quick-chip" data-text="Que Dios bendiga siempre tus pasos Angie hermosa 💖">Dios te bendiga siempre 💖</span>
                        <span class="quick-chip" data-text="¡Los mejores 15 años del mundo! ¡Te amamos! 🎂🥳">¡Los mejores 15 años! 🎂🥳</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="dedicationMessage">Tu Mensajito Especial:</label>
                    <textarea id="dedicationMessage" class="form-textarea" rows="3" placeholder="Escribe tus felicitaciones, anécdotas o deseos para Angie..." required maxlength="600" style="resize: none;"></textarea>
                </div>

                <div id="formStatusMsg" style="font-size: 0.82rem; margin-bottom: 12px; min-height: 18px; text-align: center;"></div>

                <button type="submit" class="btn-submit-dedication" id="btnSubmitDedication">
                    <i class="fas fa-heart"></i> Grabar en el Libro
                </button>
            </form>
        </div>
    </div>

    <!-- ============================================================
         MODAL LIGHTBOX FULLSCREEN (FOTOS Y VIDEOS)
         ============================================================ -->
    <div class="lightbox-modal" id="mediaLightbox">
        <button type="button" class="lightbox-close-btn" id="btnCloseLightbox" title="Cerrar">&times;</button>
        <div class="lightbox-counter-badge" id="lightboxCounterBadge">1 / 160</div>

        <button type="button" class="lightbox-nav-btn lightbox-prev-btn" id="btnPrevMedia" title="Anterior">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div class="lightbox-media-container" id="lightboxMediaContainer">
            <!-- Imagen o Video inyectado dinámicamente -->
        </div>

        <button type="button" class="lightbox-nav-btn lightbox-next-btn" id="btnNextMedia" title="Siguiente">
            <i class="fas fa-chevron-right"></i>
        </button>

        <div class="lightbox-info">
            <div class="lightbox-title" id="lightboxMediaTitle">Recuerdos XV Años</div>
            <div class="lightbox-text" id="lightboxMediaText">Momentos mágicos de mis 15</div>
            <div class="lightbox-actions">
                <a href="#" id="btnDownloadMedia" class="btn-download-media" download target="_blank">
                    <i class="fas fa-download"></i> Descargar en Alta Calidad
                </a>
            </div>
        </div>
    </div>

    <!-- Script del Libro Mágico de Fantasía 3D -->
    <script>
    (() => {
        // Detección de ruta relativa segura
        const isPublicSubdir = window.location.pathname.includes('/public/');
        const basePrefix = isPublicSubdir ? '../' : './';

        // 1. REPRODUCCIÓN DE SONIDO REALISTA DE PASO DE PÁGINA (Web Audio API)
        function playPageTurnSound() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const bufferSize = ctx.sampleRate * 0.16;
                const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
                const data = buffer.getChannelData(0);
                for (let i = 0; i < bufferSize; i++) {
                    data[i] = (Math.random() * 2 - 1) * Math.exp(-i / (ctx.sampleRate * 0.035));
                }
                const noise = ctx.createBufferSource();
                noise.buffer = buffer;
                const filter = ctx.createBiquadFilter();
                filter.type = 'lowpass';
                filter.frequency.setValueAtTime(950, ctx.currentTime);
                filter.frequency.exponentialRampToValueAtTime(120, ctx.currentTime + 0.15);
                const gain = ctx.createGain();
                gain.gain.setValueAtTime(0.4, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                noise.connect(filter);
                filter.connect(gain);
                gain.connect(ctx.destination);
                noise.start();
            } catch(e) {}
        }

        // 2. DATOS PRECARGADOS (19 Videos, 160 Fotos, Dedicatorias)
        const defaultVideos = [
            { id: 1, titulo: "Llegada triunfal de Angie ✨", archivo: "angie_xv_video_01.mp4", tamano: "4.79 MB" },
            { id: 2, titulo: "Entrada mágica al salón 👑", archivo: "angie_xv_video_02.mp4", tamano: "10.44 MB" },
            { id: 3, titulo: "El Vals con Papá 💖", archivo: "angie_xv_video_03.mp4", tamano: "2.01 MB" },
            { id: 4, titulo: "Brindis con la Familia 🥂", archivo: "angie_xv_video_04.mp4", tamano: "2.82 MB" },
            { id: 5, titulo: "Baile sorpresa con el cortejo 💃", archivo: "angie_xv_video_05.mp4", tamano: "2.43 MB" },
            { id: 6, titulo: "Sesión de fotos y sonrisas 📸", archivo: "angie_xv_video_06.mp4", tamano: "1.12 MB" },
            { id: 7, titulo: "Momento de la torta de XV 🎂", archivo: "angie_xv_video_07.mp4", tamano: "1.63 MB" },
            { id: 8, titulo: "Abrazos y felicitaciones emotivas 💕", archivo: "angie_xv_video_08.mp4", tamano: "1.34 MB" },
            { id: 9, titulo: "La hora loca y luces neón 🎊", archivo: "angie_xv_video_09.mp4", tamano: "1.24 MB" },
            { id: 10, titulo: "Pista de baile encendida 🎶", archivo: "angie_xv_video_10.mp4", tamano: "1.60 MB" },
            { id: 11, titulo: "Diversión con los amigos 🥳", archivo: "angie_xv_video_11.mp4", tamano: "0.85 MB" },
            { id: 12, titulo: "Palabras especiales para Angie 🎙️", archivo: "angie_xv_video_12.mp4", tamano: "2.93 MB" },
            { id: 13, titulo: "El cotillón y la alegría dorada 🌟", archivo: "angie_xv_video_13.mp4", tamano: "6.76 MB" },
            { id: 14, titulo: "Coreografía de Angie y amigas 💃✨", archivo: "angie_xv_video_14.mp4", tamano: "4.67 MB" },
            { id: 15, titulo: "Risas y recuerdos inolvidables 💫", archivo: "angie_xv_video_15.mp4", tamano: "2.56 MB" },
            { id: 16, titulo: "Brindis de honor con los padrinos 🥂✨", archivo: "angie_xv_video_16.mp4", tamano: "1.45 MB" },
            { id: 17, titulo: "Sorpresa especial de la noche 🎁", archivo: "angie_xv_video_17.mp4", tamano: "1.96 MB" },
            { id: 18, titulo: "Momentos espontáneos de pura emoción ✨", archivo: "angie_xv_video_18.mp4", tamano: "1.32 MB" },
            { id: 19, titulo: "Gran cierre de una noche mágica 👑💫", archivo: "angie_xv_video_19.mp4", tamano: "6.69 MB" }
        ].map(v => ({
            ...v,
            url: `api/video.php?v=${encodeURIComponent(v.archivo)}`,
            direct_url: `assets/videos/${encodeURIComponent(v.archivo)}`
        }));

        const frasesDed = [
            "Momentos mágicos e inolvidables de mis XV Años ✨👑",
            "Celebrando una noche mágica junto a las personas que más quiero 💖",
            "Sonrisas, abrazos y recuerdos para toda la vida ✨",
            "¡Una noche de ensueño que quedará por siempre en mi corazón! 🌟",
            "Bailando y disfrutando en la pista de mis XV Años 💃🎶",
            "Momentos únicos con mi hermosa familia y amigos 💕",
            "¡La mejor noche de mi vida! Gracias a todos por acompañarme ✨🥂"
        ];

        const defaultPhotos = Array.from({ length: 160 }, (_, i) => {
            const num = i + 1;
            const numStr = String(num).padStart(3, '0');
            const archivo = `angie_xv_recuerdo_${numStr}.jpg`;
            return {
                id: num,
                nombre_invitado: 'Álbum Oficial · Angie Karolina',
                mensaje: frasesDed[i % frasesDed.length],
                archivo: archivo,
                likes: ((num * 7) % 25) + 5,
                url: `api/foto.php?f=${encodeURIComponent(archivo)}`,
                direct_url: `assets/fotos/${encodeURIComponent(archivo)}`
            };
        });

        const defaultMensajes = [
            {
                id: 1,
                nombre: 'Edwin & Lady (Papás)',
                mensaje: 'Hija amada, verte cumplir tus 15 años es el regalo más hermoso de nuestras vidas. Que Dios ilumine tu camino siempre. ¡Te amamos infinitamente! 💖👑',
                icono: '👑',
                likes: 38,
                fecha_formateada: '04/10/2026 20:45'
            },
            {
                id: 2,
                nombre: 'Tía Carmen & Primos',
                mensaje: '¡Felicidades Angie preciosa! Una noche inolvidable, te veías radiante como un cuento de hadas. ¡Que todos tus sueños se hagan realidad! ✨🥂',
                icono: '✨',
                likes: 24,
                fecha_formateada: '04/10/2026 21:10'
            },
            {
                id: 3,
                nombre: 'Tus Mejores Amigas del Cole',
                mensaje: '¡La mejor fiesta de 15 del mundo! Bailamos sin parar toda la noche. ¡Te amamos muchísimo mejor amiga! 💃💕🎂',
                icono: '💖',
                likes: 31,
                fecha_formateada: '04/10/2026 21:30'
            },
            {
                id: 4,
                nombre: 'Padrinos de Angie',
                mensaje: 'Orgullosos de verte convertida en una señorita tan dulce, inteligente y llena de luz. Siempre estaremos para apoyarte. ¡Feliz cumpleaños! 🌟🥂',
                icono: '🥂',
                likes: 19,
                fecha_formateada: '04/10/2026 21:50'
            }
        ];

        let albumData = {
            mensajes: defaultMensajes,
            videos: defaultVideos,
            fotos: defaultPhotos
        };

        // ============================================================
        // 3. ESTRUCTURA DE CAPÍTULOS Y PÁGINAS DEL LIBRO (SPREADS)
        // ============================================================
        // Un spread representa 2 páginas abiertas simultáneas (Izquierda y Derecha)
        // Calculamos los spreads necesarios:
        // Spread 0: Portada & Prólogo
        // Spreads 1 a 40: Fotos (4 fotos por spread: 2 en izquierda, 2 en derecha) -> 160 fotos
        // Spreads 41 a 46: Videos (3 a 4 videos por spread) -> 19 videos
        // Spreads 47 a 50: Muro de Dedicatorias y Firmas
        // Spread 51: Epílogo & Contraportada

        let currentSpread = 0;
        let isFlipping = false;

        function getTotalSpreads() {
            const photoSpreads = Math.ceil(albumData.fotos.length / 4); // 40
            const videoSpreads = Math.ceil(albumData.videos.length / 4); // 5
            const msgSpreads = Math.max(2, Math.ceil(albumData.mensajes.length / 4)); // ~2
            return 1 + photoSpreads + videoSpreads + msgSpreads + 1; // ~49 spreads
        }

        // Renderizar el contenido de las páginas izquierda y derecha según el spread activo
        function renderCurrentSpread() {
            const totalSpreads = getTotalSpreads();
            const photoSpreadCount = Math.ceil(albumData.fotos.length / 4);
            const videoSpreadCount = Math.ceil(albumData.videos.length / 4);

            const pageLeftContent = document.getElementById('pageLeftContent');
            const pageRightContent = document.getElementById('pageRightContent');
            const pageLeftNumber = document.getElementById('pageLeftNumber');
            const pageRightNumber = document.getElementById('pageRightNumber');
            const pageIndicatorText = document.getElementById('pageIndicatorText');

            // Pausar cualquier video activo antes de cambiar página
            document.querySelectorAll('video').forEach(v => v.pause());

            // 1. SPREAD 0: PORTADA REAL & PRÓLOGO
            if (currentSpread === 0) {
                pageLeftContent.innerHTML = `
                    <div class="cover-page-content">
                        <div class="royal-crest-icon"><i class="fas fa-crown"></i></div>
                        <div class="book-main-sub">MIS XV AÑOS</div>
                        <h1 class="book-main-title">Angie Karolina</h1>
                        <div class="vintage-gold-rule"></div>
                        <div style="font-family: var(--font-royal); font-size: 0.8rem; letter-spacing: 3px; color: var(--dorado-oscuro); margin-bottom: 8px;">
                            3 · OCTUBRE · 2026
                        </div>
                        <p class="prologue-quote">
                            "Hay momentos en la vida que duran solo un instante, pero dejan una huella de oro para siempre en el corazón." ✨👑
                        </p>
                        <div style="font-family: var(--font-hand); font-size: 1.25rem; color: #062E25; margin-top: 6px;">
                            Libro Oficial de Recuerdos
                        </div>
                    </div>
                `;

                pageRightContent.innerHTML = `
                    <div class="cover-page-content">
                        <div class="chapter-num">EDICIÓN CONMEMORATIVA</div>
                        <div class="chapter-title" style="font-size: 1.25rem;">Bienvenido a mi Historia</div>
                        
                        <div class="oval-photo-frame">
                            <img src="${basePrefix}angie-xv.jpg" onerror="this.src='angie-xv.jpg'" alt="Angie Karolina">
                        </div>

                        <p class="prologue-quote" style="font-size: 0.88rem; max-width: 360px;">
                            Pasa estas páginas para revivir los bailes, las risas, 
                            los 19 videos cinematográficos y todas las fotos mágicas de mi fiesta. 💖
                        </p>

                        <button type="button" class="btn-open-book-cta" onclick="turnToSpread(1)">
                            <span>Abrir el Álbum</span> <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                `;

                pageLeftNumber.textContent = 'I';
                pageRightNumber.textContent = 'II';
                pageIndicatorText.textContent = `PÁGINA 1 - 2 · PORTADA REAL`;
                updateBookmarkHighlight('portada');
                updateButtonsState();
                return;
            }

            // 2. SPREADS DE FOTOGRAFÍAS POLAROID (Spread 1 a photoSpreadCount)
            if (currentSpread >= 1 && currentSpread <= photoSpreadCount) {
                const photoOffset = (currentSpread - 1) * 4;
                const photosLeft = albumData.fotos.slice(photoOffset, photoOffset + 2);
                const photosRight = albumData.fotos.slice(photoOffset + 2, photoOffset + 4);

                const renderPhotoCards = (list, startIdx) => list.map((f, i) => {
                    const globalIdx = photoOffset + startIdx + i;
                    const directSrc = basePrefix + (f.direct_url || `assets/fotos/${f.archivo}`);
                    const apiSrc = basePrefix + (f.url || `api/foto.php?f=${encodeURIComponent(f.archivo)}`);

                    return `
                    <div class="book-polaroid" onclick="openLightboxPhoto(${globalIdx})">
                        <div class="polaroid-gold-pin"></div>
                        <div class="book-polaroid-img">
                            <img src="${directSrc}" 
                                 onerror="if(!this.dataset.triedApi){this.dataset.triedApi=1; this.src='${apiSrc}';}" 
                                 alt="${f.nombre_invitado}" loading="lazy">
                        </div>
                        <div class="book-polaroid-caption">${f.mensaje || 'Recuerdo Mágico ✨'}</div>
                        <div class="book-polaroid-likes">
                            <i class="fas fa-heart"></i> ${f.likes || 0}
                        </div>
                    </div>
                    `;
                }).join('');

                pageLeftContent.innerHTML = `
                    <div>
                        <div class="chapter-header">
                            <div class="chapter-num">CAPÍTULO I · GALERÍA DE RECUERDOS</div>
                            <div class="chapter-title">Fotos de la Fiesta</div>
                        </div>
                        <div class="photos-book-grid">
                            ${renderPhotoCards(photosLeft, 0)}
                        </div>
                    </div>
                    <div style="font-size: 0.72rem; color: #888; text-align: center; font-family: var(--font-serif); font-style: italic;">
                        Toca cualquier foto para ampliarla a pantalla completa
                    </div>
                `;

                pageRightContent.innerHTML = `
                    <div>
                        <div class="chapter-header">
                            <div class="chapter-num">ÁLBUM SCRAPBOOK</div>
                            <div class="chapter-title">Sonrisas Doradas</div>
                        </div>
                        <div class="photos-book-grid">
                            ${renderPhotoCards(photosRight, 2)}
                        </div>
                    </div>
                    <div style="font-size: 0.72rem; color: #888; text-align: center; font-family: var(--font-serif); font-style: italic;">
                        Fotos #${photoOffset + 1} a #${Math.min(albumData.fotos.length, photoOffset + 4)} de ${albumData.fotos.length}
                    </div>
                `;

                const pageNumLeft = (currentSpread * 2) + 1;
                const pageNumRight = (currentSpread * 2) + 2;
                pageLeftNumber.textContent = pageNumLeft;
                pageRightNumber.textContent = pageNumRight;
                pageIndicatorText.textContent = `PÁGINAS ${pageNumLeft} - ${pageNumRight} · FOTOS (${photoOffset + 1} a ${Math.min(albumData.fotos.length, photoOffset + 4)})`;
                updateBookmarkHighlight('fotos');
                updateButtonsState();
                return;
            }

            // 3. SPREADS DE VIDEOS (Spread photoSpreadCount + 1 a photoSpreadCount + videoSpreadCount)
            const videoStartSpread = photoSpreadCount + 1;
            const videoEndSpread = photoSpreadCount + videoSpreadCount;

            if (currentSpread >= videoStartSpread && currentSpread <= videoEndSpread) {
                const videoOffset = (currentSpread - videoStartSpread) * 4;
                const videosLeft = albumData.videos.slice(videoOffset, videoOffset + 2);
                const videosRight = albumData.videos.slice(videoOffset + 2, videoOffset + 4);

                const renderVideoCards = (list, startIdx) => list.map((v, i) => {
                    const globalIdx = videoOffset + startIdx + i;
                    const directSrc = basePrefix + (v.direct_url || `assets/videos/${v.archivo}`);
                    const apiSrc = basePrefix + (v.url || `api/video.php?v=${encodeURIComponent(v.archivo)}`);

                    return `
                    <div class="book-video-card">
                        <div class="book-video-frame">
                            <video preload="metadata" playsinline controls>
                                <source src="${directSrc}" type="video/mp4">
                                <source src="${apiSrc}" type="video/mp4">
                            </video>
                        </div>
                        <div class="book-video-info">
                            <div class="book-video-title">
                                <i class="fas fa-play-circle" style="color: var(--dorado-oscuro);"></i>
                                ${v.titulo}
                            </div>
                            <button type="button" class="btn-fullscreen-cine" onclick="openLightboxVideo(${globalIdx})">
                                <i class="fas fa-expand"></i> Cine
                            </button>
                        </div>
                    </div>
                    `;
                }).join('');

                pageLeftContent.innerHTML = `
                    <div>
                        <div class="chapter-header">
                            <div class="chapter-num">CAPÍTULO II · SALA DE CINE</div>
                            <div class="chapter-title">Momentos en Movimiento</div>
                        </div>
                        <div class="videos-book-grid">
                            ${renderVideoCards(videosLeft, 0)}
                        </div>
                    </div>
                    <div style="font-size: 0.72rem; color: #888; text-align: center; font-style: italic;">
                        Reproduce los videos directamente en la página
                    </div>
                `;

                pageRightContent.innerHTML = `
                    <div>
                        <div class="chapter-header">
                            <div class="chapter-num">CORTES REALES</div>
                            <div class="chapter-title">La Noche Mágica</div>
                        </div>
                        <div class="videos-book-grid">
                            ${renderVideoCards(videosRight, 2)}
                        </div>
                    </div>
                    <div style="font-size: 0.72rem; color: #888; text-align: center; font-style: italic;">
                        Videos #${videoOffset + 1} a #${Math.min(albumData.videos.length, videoOffset + 4)} de ${albumData.videos.length}
                    </div>
                `;

                const pageNumLeft = (currentSpread * 2) + 1;
                const pageNumRight = (currentSpread * 2) + 2;
                pageLeftNumber.textContent = pageNumLeft;
                pageRightNumber.textContent = pageNumRight;
                pageIndicatorText.textContent = `PÁGINAS ${pageNumLeft} - ${pageNumRight} · VIDEOS (${videoOffset + 1} a ${Math.min(albumData.videos.length, videoOffset + 4)})`;
                updateBookmarkHighlight('videos');
                updateButtonsState();
                return;
            }

            // 4. SPREADS DE DEDICATORIAS & FIRMAS
            const msgStartSpread = videoEndSpread + 1;
            const msgEndSpread = totalSpreads - 1;

            if (currentSpread >= msgStartSpread && currentSpread < totalSpreads) {
                const msgOffset = (currentSpread - msgStartSpread) * 4;
                const msgsLeft = albumData.mensajes.slice(msgOffset, msgOffset + 2);
                const msgsRight = albumData.mensajes.slice(msgOffset + 2, msgOffset + 4);

                const renderMsgList = (list) => {
                    if (list.length === 0) {
                        return `<div style="text-align: center; padding: 25px 10px; color: #888; font-style: italic;">Página disponible para nuevas firmas doradas... ✨</div>`;
                    }
                    return list.map(m => `
                        <div class="guest-entry-postit">
                            <div class="entry-header">
                                <span class="entry-icon">${m.icono || '✨'}</span>
                                <span class="entry-author">${m.nombre}</span>
                                <span class="entry-date">${m.fecha_formateada || 'Mis XV'}</span>
                            </div>
                            <div class="entry-msg">"${m.mensaje}"</div>
                            <div class="entry-footer">
                                <button type="button" class="btn-like-entry" onclick="likeDedication(${m.id}, this)">
                                    <i class="fas fa-heart"></i> <span class="like-num">${m.likes || 0}</span> Me encanta
                                </button>
                                <span style="color: #999; font-size: 0.68rem;">✨ Libro de Angie</span>
                            </div>
                        </div>
                    `).join('');
                };

                pageLeftContent.innerHTML = `
                    <div>
                        <div class="chapter-header">
                            <div class="chapter-num">CAPÍTULO III · EL LIBRO DE VISITAS</div>
                            <div class="chapter-title">Firmas & Bendiciones</div>
                        </div>
                        <div class="guestbook-page-entries">
                            ${renderMsgList(msgsLeft)}
                        </div>
                    </div>
                    <button type="button" class="btn-leave-signature" onclick="document.getElementById('btnOpenDedicationModal').click()">
                        <i class="fas fa-pen-nib"></i> Escribir mi Dedicatoria
                    </button>
                `;

                pageRightContent.innerHTML = `
                    <div>
                        <div class="chapter-header">
                            <div class="chapter-num">DESEOS DE AMOR</div>
                            <div class="chapter-title">Páginas de Cariño</div>
                        </div>
                        <div class="guestbook-page-entries">
                            ${renderMsgList(msgsRight)}
                        </div>
                    </div>
                    <div style="text-align: center; font-size: 0.72rem; color: #888; font-style: italic;">
                        ${albumData.mensajes.length} dedicatorias grabadas para siempre en el corazón de Angie
                    </div>
                `;

                const pageNumLeft = (currentSpread * 2) + 1;
                const pageNumRight = (currentSpread * 2) + 2;
                pageLeftNumber.textContent = pageNumLeft;
                pageRightNumber.textContent = pageNumRight;
                pageIndicatorText.textContent = `PÁGINAS ${pageNumLeft} - ${pageNumRight} · DEDICATORIAS`;
                updateBookmarkHighlight('firmas');
                updateButtonsState();
                return;
            }

            // 5. CONTRAPORTADA FINAL
            pageLeftContent.innerHTML = `
                <div class="cover-page-content">
                    <div class="royal-crest-icon" style="font-size: 2.5rem;"><i class="fas fa-heart"></i></div>
                    <div class="chapter-title" style="font-size: 1.5rem; margin-bottom: 6px;">Infinitas Gracias</div>
                    <div class="vintage-gold-rule"></div>
                    <p class="prologue-quote" style="font-size: 0.95rem;">
                        "A Dios, a mis amados padres Edwin y Lady, a mi familia y a cada uno de mis amigos: 
                        gracias por hacer de mis XV Años la noche más feliz e inolvidable de mi vida." 💖✨
                    </p>
                    <div style="font-family: var(--font-script); font-size: 2.8rem; color: #062E25; margin-top: 10px;">
                        Angie Karolina
                    </div>
                </div>
            `;

            pageRightContent.innerHTML = `
                <div class="cover-page-content">
                    <div class="royal-crest-icon"><i class="fas fa-book-sparkles"></i></div>
                    <div class="chapter-num">FIN DEL LIBRO MÁGICO</div>
                    <div class="chapter-title" style="font-size: 1.25rem;">3 de Octubre de 2026</div>
                    <div class="vintage-gold-rule"></div>
                    <p class="prologue-quote" style="font-size: 0.88rem;">
                        Puedes volver a hojear este libro cuando lo desees o compartirlo con tus seres queridos.
                    </p>
                    <button type="button" class="btn-open-book-cta" onclick="turnToSpread(0)">
                        <i class="fas fa-undo"></i> Volver a la Portada
                    </button>
                </div>
            `;

            pageLeftNumber.textContent = 'FIN';
            pageRightNumber.textContent = '👑';
            pageIndicatorText.textContent = `CONTRAPORTADA · FIN DEL LIBRO`;
            updateButtonsState();
        }

        // 4. ANIMACIÓN DE PASO DE PÁGINAS 3D (FLIP)
        function turnToSpread(targetSpread) {
            if (isFlipping) return;
            const totalSpreads = getTotalSpreads();
            if (targetSpread < 0 || targetSpread >= totalSpreads) return;
            if (targetSpread === currentSpread) return;

            isFlipping = true;
            playPageTurnSound();

            const isForward = targetSpread > currentSpread;
            const flippingSheet = document.getElementById('flippingSheet');

            flippingSheet.className = 'flipping-sheet ' + (isForward ? 'flip-forward' : 'flip-backward');

            setTimeout(() => {
                currentSpread = targetSpread;
                renderCurrentSpread();
                flippingSheet.className = 'flipping-sheet';
                isFlipping = false;
            }, 620);
        }

        function turnNext() {
            turnToSpread(currentSpread + 1);
        }

        function turnPrev() {
            turnToSpread(currentSpread - 1);
        }

        function updateButtonsState() {
            const btnPrev = document.getElementById('btnTurnPrev');
            const btnNext = document.getElementById('btnTurnNext');
            const total = getTotalSpreads();
            if (btnPrev) btnPrev.disabled = (currentSpread === 0);
            if (btnNext) btnNext.disabled = (currentSpread >= total - 1);
        }

        function updateBookmarkHighlight(chapterName) {
            document.querySelectorAll('.bookmark-tab[data-chapter]').forEach(tab => {
                tab.classList.toggle('active', tab.dataset.chapter === chapterName);
            });
        }

        // Botones de flecha
        document.getElementById('btnTurnNext').addEventListener('click', turnNext);
        document.getElementById('btnTurnPrev').addEventListener('click', turnPrev);

        // Teclado
        window.addEventListener('keydown', (e) => {
            if (document.getElementById('mediaLightbox').style.display === 'flex' ||
                document.getElementById('writeMessageModal').style.display === 'flex') {
                return;
            }
            if (e.key === 'ArrowRight') turnNext();
            if (e.key === 'ArrowLeft') turnPrev();
        });

        // Swipe táctil en celulares
        let touchStartX = 0;
        const bookSpread = document.getElementById('bookSpread');
        bookSpread.addEventListener('touchstart', (e) => {
            if (e.changedTouches && e.changedTouches[0]) touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        bookSpread.addEventListener('touchend', (e) => {
            if (e.changedTouches && e.changedTouches[0]) {
                const diff = e.changedTouches[0].screenX - touchStartX;
                if (Math.abs(diff) > 55) {
                    if (diff < 0) turnNext();
                    else turnPrev();
                }
            }
        }, { passive: true });

        // Navegación por Marcapáginas
        document.querySelectorAll('.bookmark-tab[data-chapter]').forEach(tab => {
            tab.addEventListener('click', () => {
                const ch = tab.dataset.chapter;
                const photoSpreads = Math.ceil(albumData.fotos.length / 4);
                if (ch === 'portada') turnToSpread(0);
                if (ch === 'fotos') turnToSpread(1);
                if (ch === 'videos') turnToSpread(photoSpreads + 1);
                if (ch === 'firmas') turnToSpread(photoSpreads + Math.ceil(albumData.videos.length / 4) + 1);
            });
        });

        // Botones de salto rápido inferiores
        document.getElementById('btnJumpCover').addEventListener('click', () => turnToSpread(0));
        document.getElementById('btnJumpPhotos').addEventListener('click', () => turnToSpread(1));
        document.getElementById('btnJumpVideos').addEventListener('click', () => {
            const photoSpreads = Math.ceil(albumData.fotos.length / 4);
            turnToSpread(photoSpreads + 1);
        });
        document.getElementById('btnJumpSignatures').addEventListener('click', () => {
            const photoSpreads = Math.ceil(albumData.fotos.length / 4);
            const videoSpreads = Math.ceil(albumData.videos.length / 4);
            turnToSpread(photoSpreads + videoSpreads + 1);
        });

        // 5. REPRODUCTOR DE MÚSICA & AUTOPLAY INTELIGENTE
        const bgMusic = document.getElementById('bgMusic');
        const musicToggleWidget = document.getElementById('musicToggleWidget');
        const vinylCircle = document.getElementById('vinylCircle');
        const musicStateText = document.getElementById('musicStateText');
        const eqBars = document.getElementById('eqBars');

        let isMusicPlaying = false;
        function updateMusicUI(playing) {
            isMusicPlaying = playing;
            if (playing) {
                vinylCircle.classList.add('spinning');
                eqBars.classList.add('spinning');
                musicStateText.textContent = 'Sonando Música ✨';
            } else {
                vinylCircle.classList.remove('spinning');
                eqBars.classList.remove('spinning');
                musicStateText.textContent = 'Música Pausada';
            }
        }

        async function tryPlayMusic() {
            try {
                await bgMusic.play();
                updateMusicUI(true);
            } catch (err) {
                updateMusicUI(false);
            }
        }

        function toggleMusic() {
            if (bgMusic.paused) {
                bgMusic.play().then(() => updateMusicUI(true)).catch(() => {});
            } else {
                bgMusic.pause();
                updateMusicUI(false);
            }
        }

        musicToggleWidget.addEventListener('click', toggleMusic);
        window.addEventListener('click', function unlockOnce() {
            if (bgMusic.paused && !isMusicPlaying) {
                bgMusic.play().then(() => updateMusicUI(true)).catch(() => {});
            }
            window.removeEventListener('click', unlockOnce);
        }, { once: true });
        tryPlayMusic();

        // 6. CARGAR DATOS ASÍNCRONOS DE LA API (FOTOS, VIDEOS, DEDICATORIAS)
        async function loadAlbumData() {
            // Cargar dedicatorias y videos
            try {
                const resLibro = await fetch(basePrefix + 'api/libro.php?t=' + Date.now());
                if (resLibro.ok) {
                    const dataLibro = await resLibro.json();
                    if (dataLibro && dataLibro.success) {
                        if (Array.isArray(dataLibro.mensajes) && dataLibro.mensajes.length > 0) {
                            albumData.mensajes = dataLibro.mensajes;
                        }
                        if (Array.isArray(dataLibro.videos) && dataLibro.videos.length > 0) {
                            albumData.videos = dataLibro.videos;
                        }
                        renderCurrentSpread();
                    }
                }
            } catch (err) {
                console.warn('API libro offline, usando datos locales:', err);
            }

            // Cargar las fotos
            try {
                const resFotos = await fetch(basePrefix + 'api/fotos.php?limit=500&order=asc&t=' + Date.now());
                if (resFotos.ok) {
                    const dataFotos = await resFotos.json();
                    if (dataFotos && dataFotos.success && Array.isArray(dataFotos.fotos) && dataFotos.fotos.length > 0) {
                        albumData.fotos = dataFotos.fotos;
                        renderCurrentSpread();
                    }
                }
            } catch (err) {
                console.warn('API fotos offline, usando fotos locales:', err);
            }
        }

        // Like a una dedicatoria
        window.likeDedication = async function(id, btn) {
            btn.style.transform = 'scale(1.35)';
            setTimeout(() => { btn.style.transform = ''; }, 200);

            try {
                const res = await fetch(basePrefix + 'api/libro.php?action=like', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data && data.success) {
                    const numEl = btn.querySelector('.like-num');
                    if (numEl) numEl.textContent = data.likes;
                    confetti({
                        particleCount: 20,
                        spread: 60,
                        origin: { y: 0.7 },
                        colors: ['#FF69B4', '#FFD700', '#FF1493']
                    });
                }
            } catch(e) {}
        };

        // 7. MODAL: ESCRIBIR NUEVA DEDICATORIA
        const writeMessageModal = document.getElementById('writeMessageModal');
        const btnOpenDedicationModal = document.getElementById('btnOpenDedicationModal');
        const btnCloseWriteModal = document.getElementById('btnCloseWriteModal');
        const formNewDedication = document.getElementById('formNewDedication');
        const formStatusMsg = document.getElementById('formStatusMsg');
        const btnSubmitDedication = document.getElementById('btnSubmitDedication');
        let selectedEmoji = '👑';

        btnOpenDedicationModal.addEventListener('click', () => {
            writeMessageModal.style.display = 'flex';
            formStatusMsg.textContent = '';
            document.getElementById('dedicationName').focus();
        });

        btnCloseWriteModal.addEventListener('click', () => {
            writeMessageModal.style.display = 'none';
        });

        writeMessageModal.addEventListener('click', (e) => {
            if (e.target === writeMessageModal) writeMessageModal.style.display = 'none';
        });

        document.querySelectorAll('.emoji-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.emoji-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                selectedEmoji = opt.dataset.emoji;
            });
        });

        document.querySelectorAll('.quick-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const txt = document.getElementById('dedicationMessage');
                txt.value = chip.dataset.text;
                txt.focus();
            });
        });

        formNewDedication.addEventListener('submit', async (e) => {
            e.preventDefault();
            const nombre = document.getElementById('dedicationName').value.trim();
            const mensaje = document.getElementById('dedicationMessage').value.trim();
            if (!nombre || !mensaje) return;

            btnSubmitDedication.disabled = true;
            btnSubmitDedication.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Grabando recuerdo...';
            formStatusMsg.textContent = 'Escribiendo en las páginas doradas...';
            formStatusMsg.style.color = '#FFD700';

            try {
                const res = await fetch(basePrefix + 'api/libro.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        nombre: nombre,
                        mensaje: mensaje,
                        icono: selectedEmoji,
                        color: 'gold'
                    })
                });

                const data = await res.json();
                if (data && data.success) {
                    formStatusMsg.textContent = '✨ ¡Tu dedicatoria quedó grabada en el libro!';
                    formStatusMsg.style.color = '#A7F3D0';

                    confetti({
                        particleCount: 100,
                        spread: 80,
                        origin: { y: 0.6 },
                        colors: ['#FFD700', '#C8A24A', '#E91E63', '#00E676', '#FFFFFF']
                    });

                    if (data.mensaje_nuevo) {
                        albumData.mensajes.unshift(data.mensaje_nuevo);
                        renderCurrentSpread();
                    }

                    setTimeout(() => {
                        writeMessageModal.style.display = 'none';
                        formNewDedication.reset();
                        btnSubmitDedication.disabled = false;
                        btnSubmitDedication.innerHTML = '<i class="fas fa-heart"></i> Grabar en el Libro';
                    }, 1200);
                } else {
                    throw new Error(data.error || 'Error al guardar');
                }
            } catch (err) {
                btnSubmitDedication.disabled = false;
                btnSubmitDedication.innerHTML = '<i class="fas fa-heart"></i> Intentar de Nuevo';
                formStatusMsg.textContent = 'Error: ' + err.message;
                formStatusMsg.style.color = '#ff9999';
            }
        });

        // 8. VISOR A PANTALLA COMPLETA (LIGHTBOX)
        const mediaLightbox = document.getElementById('mediaLightbox');
        const btnCloseLightbox = document.getElementById('btnCloseLightbox');
        const btnPrevMedia = document.getElementById('btnPrevMedia');
        const btnNextMedia = document.getElementById('btnNextMedia');
        const lightboxCounterBadge = document.getElementById('lightboxCounterBadge');
        const lightboxMediaContainer = document.getElementById('lightboxMediaContainer');
        const lightboxMediaTitle = document.getElementById('lightboxMediaTitle');
        const lightboxMediaText = document.getElementById('lightboxMediaText');
        const btnDownloadMedia = document.getElementById('btnDownloadMedia');

        let currentMediaList = [];
        let currentMediaIndex = 0;
        let isViewingVideo = false;

        window.openLightboxPhoto = function(idx) {
            isViewingVideo = false;
            currentMediaList = albumData.fotos;
            currentMediaIndex = idx;
            renderLightboxItem();
            mediaLightbox.style.display = 'flex';
        };

        window.openLightboxVideo = function(idx) {
            isViewingVideo = true;
            currentMediaList = albumData.videos;
            currentMediaIndex = idx;
            renderLightboxItem();
            mediaLightbox.style.display = 'flex';
        };

        function renderLightboxItem() {
            if (!currentMediaList || currentMediaList.length === 0) return;
            const item = currentMediaList[currentMediaIndex];
            if (!item) return;

            lightboxCounterBadge.textContent = `${currentMediaIndex + 1} / ${currentMediaList.length}`;

            if (isViewingVideo) {
                const directSrc = basePrefix + (item.direct_url || `assets/videos/${item.archivo}`);
                const apiSrc = basePrefix + (item.url || `api/video.php?v=${encodeURIComponent(item.archivo)}`);

                lightboxMediaContainer.innerHTML = `
                    <video id="lightboxVideoPlayer" controls autoplay playsinline style="max-width: 90vw; max-height: 70vh; border-radius: 12px; border: 2px solid var(--dorado); box-shadow: 0 15px 50px rgba(0,0,0,0.9);">
                        <source src="${directSrc}" type="video/mp4">
                        <source src="${apiSrc}" type="video/mp4">
                    </video>
                `;
                lightboxMediaTitle.textContent = item.titulo || 'Video de la Fiesta';
                lightboxMediaText.textContent = `🎬 Momento Especial de los XV de Angie (${item.tamano})`;
                btnDownloadMedia.href = directSrc;
                btnDownloadMedia.download = item.archivo || 'video_angie_xv.mp4';
            } else {
                const directSrc = basePrefix + (item.direct_url || `assets/fotos/${item.archivo}`);
                const apiSrc = basePrefix + (item.url || `api/foto.php?f=${encodeURIComponent(item.archivo)}`);

                lightboxMediaContainer.innerHTML = `
                    <img src="${directSrc}" 
                         onerror="if(!this.dataset.triedApi){this.dataset.triedApi=1; this.src='${apiSrc}';}" 
                         alt="${item.nombre_invitado}" style="max-width: 90vw; max-height: 70vh; object-fit: contain; border-radius: 12px; border: 2px solid var(--dorado); box-shadow: 0 15px 50px rgba(0,0,0,0.9);">
                `;
                lightboxMediaTitle.textContent = item.nombre_invitado || 'Momento Mágico';
                lightboxMediaText.textContent = item.mensaje ? `"${item.mensaje}"` : '✨ ¡Celebrando los XV de Angie Karolina!';
                btnDownloadMedia.href = directSrc;
                btnDownloadMedia.download = item.archivo || 'foto_angie_xv.jpg';
            }
        }

        function nextMedia() {
            if (currentMediaList.length === 0) return;
            currentMediaIndex = (currentMediaIndex + 1) % currentMediaList.length;
            renderLightboxItem();
        }

        function prevMedia() {
            if (currentMediaList.length === 0) return;
            currentMediaIndex = (currentMediaIndex - 1 + currentMediaList.length) % currentMediaList.length;
            renderLightboxItem();
        }

        btnNextMedia.addEventListener('click', (e) => { e.stopPropagation(); nextMedia(); });
        btnPrevMedia.addEventListener('click', (e) => { e.stopPropagation(); prevMedia(); });
        btnCloseLightbox.addEventListener('click', () => {
            mediaLightbox.style.display = 'none';
            lightboxMediaContainer.innerHTML = '';
        });
        mediaLightbox.addEventListener('click', (e) => {
            if (e.target === mediaLightbox) {
                mediaLightbox.style.display = 'none';
                lightboxMediaContainer.innerHTML = '';
            }
        });

        // 9. INICIALIZACIÓN INMEDIATA DEL LIBRO
        renderCurrentSpread();
        loadAlbumData();

        // 10. POLVO DE ESTRELLAS Y HADAS MÁGICAS EN EL FONDO
        const canvas = document.getElementById('magicCanvas');
        const ctx = canvas.getContext('2d');
        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        });

        const particles = [];
        const count = Math.min(40, Math.floor(window.innerWidth / 30));

        class Sparkle {
            constructor() { this.reset(); }
            reset() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.size = Math.random() * 2.5 + 0.5;
                this.speedY = -(Math.random() * 0.35 + 0.12);
                this.speedX = (Math.random() - 0.5) * 0.3;
                this.opacity = Math.random() * 0.7 + 0.2;
                this.fade = Math.random() * 0.008 + 0.003;
                this.color = Math.random() > 0.35 ? '#FFD700' : '#FFFDF5';
            }
            update() {
                this.y += this.speedY;
                this.x += this.speedX;
                this.opacity += this.fade;
                if (this.opacity > 0.95 || this.opacity < 0.15) this.fade = -this.fade;
                if (this.y < -10 || this.x < -10 || this.x > width + 10) {
                    this.reset();
                    this.y = height + 10;
                }
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = this.color;
                ctx.globalAlpha = Math.max(0, this.opacity);
                ctx.shadowBlur = 8;
                ctx.shadowColor = '#FFD700';
                ctx.fill();
            }
        }

        for (let i = 0; i < count; i++) particles.push(new Sparkle());

        function animateCanvas() {
            ctx.clearRect(0, 0, width, height);
            for (let p of particles) {
                p.update();
                p.draw();
            }
            requestAnimationFrame(animateCanvas);
        }
        animateCanvas();

    })();
    </script>
</body>
</html>
