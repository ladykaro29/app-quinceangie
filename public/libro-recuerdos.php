<?php
/**
 * Libro Mágico de Recuerdos & Dedicatorias Interactivo
 * Mis XV Años — Angie Karolina Avendaño Rivera
 * 
 * Experiencia interactiva con:
 * - Música de fondo con reproductor vinilo dinámico
 * - 160 fotografías en formato Scrapbook Polaroid
 * - 19 videos exclusivos con reproductor cinematográfico
 * - Muro de dedicatorias y mensajitos interactivos en tiempo real
 * - Efectos de confeti, destellos dorados y visor a pantalla completa
 */

// Protocolo y dominio base
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
    <title>Libro Mágico de Recuerdos · Mis XV Años · Angie Karolina</title>
    
    <!-- Metaetiquetas SEO y Open Graph para compartir en WhatsApp -->
    <meta name="description" content="Revive los momentos mágicos de los XV Años de Angie Karolina: fotos de la fiesta, videos exclusivos y deja tu dedicatoria en el libro de recuerdos.">
    <meta property="og:title" content="📖 Libro Mágico de Recuerdos · Mis XV Años Angie Karolina">
    <meta property="og:description" content="¡Mira todas las fotos, videos y escribe un mensaje bonito en el libro oficial de recuerdos de Angie! ✨👑">
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
            --verde-oscuro: #041B16;
            --verde-esmeralda: #062E25;
            --verde-bosque: #0A3C31;
            --dorado-puro: #FFD700;
            --dorado: #C8A24A;
            --dorado-claro: #F5E6C4;
            --dorado-brillante: #FFE89E;
            --crema: #FFFDF7;
            --pergamino: #FAF3E0;
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
            background-color: var(--verde-oscuro);
            background-image: 
                radial-gradient(ellipse at top, rgba(10, 60, 49, 0.85) 0%, rgba(4, 27, 22, 0.98) 70%),
                radial-gradient(circle at 80% 20%, rgba(200, 162, 74, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 20% 80%, rgba(200, 162, 74, 0.08) 0%, transparent 40%);
            background-attachment: fixed;
            color: var(--crema);
            font-family: var(--font-sans);
            overflow-x: hidden;
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Canvas de estrellas y polvo de hadas */
        #magicCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 1;
        }

        /* Contenedor principal */
        .page-wrapper {
            position: relative;
            z-index: 2;
            max-width: 1280px;
            margin: 0 auto;
            padding: 20px 16px 80px 16px;
        }

        /* Barra superior de navegación */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--dorado-claro);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            padding: 8px 16px;
            background: rgba(6, 46, 37, 0.6);
            border: 1px solid rgba(200, 162, 74, 0.35);
            border-radius: 24px;
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }

        .btn-back-home:hover {
            background: var(--dorado);
            color: var(--verde-oscuro);
            transform: translateX(-3px);
            box-shadow: 0 4px 15px rgba(200, 162, 74, 0.35);
        }

        /* Widget de música estilo vinilo giratorio */
        .music-widget {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(6, 46, 37, 0.7);
            border: 1.5px solid rgba(200, 162, 74, 0.45);
            border-radius: 40px;
            padding: 6px 16px 6px 8px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
        }

        .music-widget:hover {
            border-color: var(--dorado-puro);
            box-shadow: 0 6px 25px rgba(255, 215, 0, 0.3);
            transform: translateY(-1px);
        }

        .vinyl-disc {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: radial-gradient(circle, #222 20%, #111 60%, #000 100%);
            border: 2px solid var(--dorado);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.6);
            transition: transform 0.3s;
        }

        .vinyl-disc.playing {
            animation: spinVinyl 3.5s linear infinite;
        }

        .vinyl-center {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--dorado);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.55rem;
            color: var(--verde-oscuro);
        }

        @keyframes spinVinyl {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .music-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .music-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--dorado-brillante);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .music-subtitle {
            font-size: 0.68rem;
            color: rgba(255,255,255,0.7);
        }

        .equalizer-bars {
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

        .playing .eq-bar:nth-child(1) { animation: eqAnim 0.7s infinite alternate ease-in-out; }
        .playing .eq-bar:nth-child(2) { animation: eqAnim 0.9s infinite alternate 0.2s ease-in-out; }
        .playing .eq-bar:nth-child(3) { animation: eqAnim 0.6s infinite alternate 0.4s ease-in-out; }

        @keyframes eqAnim {
            0% { height: 3px; }
            100% { height: 12px; }
        }

        /* Banner flotante de activación de audio */
        .audio-unblock-banner {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #FFD700 0%, #C8A24A 100%);
            color: #041B16;
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6), 0 0 20px rgba(255,215,0,0.5);
            z-index: 9999;
            cursor: pointer;
            animation: pulseGlow 2s infinite alternate;
            user-select: none;
        }

        @keyframes pulseGlow {
            0% { transform: translateX(-50%) scale(0.98); box-shadow: 0 6px 20px rgba(0,0,0,0.5); }
            100% { transform: translateX(-50%) scale(1.03); box-shadow: 0 10px 30px rgba(255,215,0,0.7); }
        }

        /* Portada / Cabecera del Libro de Recuerdos */
        .book-hero {
            text-align: center;
            padding: 30px 20px 35px 20px;
            background: linear-gradient(180deg, rgba(6, 46, 37, 0.75) 0%, rgba(4, 27, 22, 0.9) 100%);
            border: 2px solid var(--dorado);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6), inset 0 0 50px rgba(200, 162, 74, 0.12);
            position: relative;
            margin-bottom: 30px;
            overflow: hidden;
        }

        /* Adornos esquineros dorados */
        .corner-filigree {
            position: absolute;
            width: 44px;
            height: 44px;
            border: 2px solid var(--dorado);
            pointer-events: none;
            opacity: 0.8;
        }
        .corner-tl { top: 12px; left: 12px; border-right: none; border-bottom: none; border-top-left-radius: 12px; }
        .corner-tr { top: 12px; right: 12px; border-left: none; border-bottom: none; border-top-right-radius: 12px; }
        .corner-bl { bottom: 12px; left: 12px; border-right: none; border-top: none; border-bottom-left-radius: 12px; }
        .corner-br { bottom: 12px; right: 12px; border-left: none; border-top: none; border-bottom-right-radius: 12px; }

        .book-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 18px;
            background: rgba(200, 162, 74, 0.15);
            border: 1px solid rgba(200, 162, 74, 0.5);
            border-radius: 20px;
            font-size: 0.78rem;
            color: var(--dorado-brillante);
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .book-title-script {
            font-family: var(--font-script);
            font-size: clamp(3rem, 8vw, 5.2rem);
            color: var(--dorado-puro);
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.5), 0 3px 6px rgba(0,0,0,0.8);
            line-height: 1;
            margin-bottom: 4px;
        }

        .book-title-sub {
            font-family: var(--font-royal);
            font-size: clamp(0.9rem, 2.5vw, 1.4rem);
            letter-spacing: 4px;
            color: var(--dorado-claro);
            margin-bottom: 14px;
            text-transform: uppercase;
        }

        .gold-divider {
            width: 140px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--dorado-puro), transparent);
            margin: 12px auto;
        }

        .book-desc {
            max-width: 680px;
            margin: 0 auto 20px auto;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
            font-family: var(--font-serif);
            font-style: italic;
        }

        /* Barra de estadísticas */
        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(200, 162, 74, 0.3);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.8rem;
            color: var(--crema);
        }

        .stat-pill strong {
            color: var(--dorado-brillante);
        }

        /* Barra de Pestañas / Filtros */
        .nav-tabs-wrapper {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 28px;
            flex-wrap: wrap;
            position: sticky;
            top: 12px;
            z-index: 100;
            background: rgba(4, 27, 22, 0.85);
            padding: 10px 14px;
            border-radius: 35px;
            backdrop-filter: blur(14px);
            border: 1px solid rgba(200, 162, 74, 0.3);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .tab-btn {
            background: transparent;
            border: 1px solid rgba(200, 162, 74, 0.25);
            color: var(--dorado-claro);
            padding: 9px 18px;
            border-radius: 25px;
            font-size: 0.86rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-sans);
        }

        .tab-btn:hover {
            background: rgba(200, 162, 74, 0.2);
            color: #FFF;
            border-color: var(--dorado);
            transform: translateY(-1px);
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #FFD700 0%, #C8A24A 100%);
            color: #041B16;
            border-color: var(--dorado-puro);
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(200, 162, 74, 0.45);
        }

        .btn-write-message {
            background: linear-gradient(135deg, #FF6B8B 0%, #D81B60 100%) !important;
            color: #FFF !important;
            border: none !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 18px rgba(216, 27, 96, 0.45);
            animation: pulseMini 2s infinite alternate;
        }

        @keyframes pulseMini {
            0% { transform: scale(1); }
            100% { transform: scale(1.04); box-shadow: 0 6px 22px rgba(216, 27, 96, 0.65); }
        }

        /* Secciones de contenido */
        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease forwards;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ============================================================
           SECCIÓN DEDICATORIAS / MURO DE MENSAJITOS (SCRAPBOOK GUESTBOOK)
           ============================================================ */
        .guestbook-section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 12px;
            background: rgba(6, 46, 37, 0.5);
            padding: 16px 20px;
            border-radius: 18px;
            border: 1px solid rgba(200, 162, 74, 0.25);
        }

        .guestbook-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        /* Tarjeta de dedicatoria estilo post-it pergamino */
        .note-card {
            background: var(--pergamino);
            color: #1A1410;
            padding: 22px 20px 18px 20px;
            border-radius: 12px;
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.45), 0 2px 5px rgba(0,0,0,0.2);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-top: 5px solid var(--dorado);
        }

        .note-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0,0,0,0.55), 0 0 15px rgba(200, 162, 74, 0.25);
            z-index: 5;
        }

        /* Variantes de color de notas */
        .note-card.gold { background: #FFF9E6; border-top-color: #C8A24A; }
        .note-card.emerald { background: #E8F5E9; border-top-color: #2E7D32; }
        .note-card.rose { background: #FFF0F5; border-top-color: #E91E63; }
        .note-card.cream { background: #FAF5E8; border-top-color: #D4AF37; }

        /* Washi tape decorativa superior */
        .washi-tape {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 75px;
            height: 18px;
            background: rgba(200, 162, 74, 0.65);
            backdrop-filter: blur(2px);
            border-left: 2px dashed rgba(0,0,0,0.15);
            border-right: 2px dashed rgba(0,0,0,0.15);
            opacity: 0.9;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .note-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .note-icon-badge {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(200, 162, 74, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
            box-shadow: inset 0 0 6px rgba(0,0,0,0.1);
        }

        .note-author-info {
            line-height: 1.25;
            flex-grow: 1;
        }

        .note-author {
            font-family: var(--font-serif);
            font-size: 1.05rem;
            font-weight: 700;
            color: #062E25;
        }

        .note-date {
            font-size: 0.72rem;
            color: #777;
            font-family: var(--font-sans);
        }

        .note-body {
            font-family: var(--font-hand);
            font-size: 1.22rem;
            line-height: 1.35;
            color: #261E14;
            margin-bottom: 16px;
            word-break: break-word;
        }

        .note-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px dashed rgba(0,0,0,0.15);
            padding-top: 10px;
            font-size: 0.78rem;
        }

        .btn-like-note {
            background: none;
            border: none;
            color: #D81B60;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: transform 0.2s;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .btn-like-note:hover {
            transform: scale(1.15);
            background: rgba(216, 27, 96, 0.1);
        }

        /* ============================================================
           SECCIÓN VIDEOS EXCLUSIVOS (CINEMATIC THEATER)
           ============================================================ */
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 22px;
            margin-bottom: 40px;
        }

        .video-card {
            background: rgba(6, 46, 37, 0.75);
            border: 1.5px solid rgba(200, 162, 74, 0.45);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: transform 0.3s ease, border-color 0.3s;
            display: flex;
            flex-direction: column;
        }

        .video-card:hover {
            transform: translateY(-4px);
            border-color: var(--dorado-puro);
            box-shadow: 0 14px 40px rgba(0,0,0,0.65), 0 0 20px rgba(255,215,0,0.25);
        }

        .video-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #000;
            overflow: hidden;
        }

        .video-player {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .video-card-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
            justify-content: space-between;
        }

        .video-title {
            font-family: var(--font-serif);
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--dorado-brillante);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .video-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.7);
        }

        .btn-fullscreen-video {
            background: rgba(200, 162, 74, 0.15);
            border: 1px solid var(--dorado);
            color: var(--dorado-claro);
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 0.76rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
            justify-content: center;
        }

        .btn-fullscreen-video:hover {
            background: var(--dorado);
            color: #041B16;
        }

        /* ============================================================
           SECCIÓN FOTOGRAFÍAS POLAROID SCRAPBOOK (160 FOTOS)
           ============================================================ */
        .photos-masonry {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .polaroid-card {
            background: #FFFDF7;
            padding: 12px 12px 18px 12px;
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.5), 0 2px 6px rgba(0,0,0,0.25);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s;
            cursor: pointer;
            position: relative;
            user-select: none;
            display: flex;
            flex-direction: column;
        }

        /* Rotación sutil aleatoria para dar aspecto natural de libro/scrapbook */
        .polaroid-card:nth-child(4n+1) { transform: rotate(-1.5deg); }
        .polaroid-card:nth-child(4n+2) { transform: rotate(1.2deg); }
        .polaroid-card:nth-child(4n+3) { transform: rotate(-0.8deg); }
        .polaroid-card:nth-child(4n+4) { transform: rotate(1.8deg); }

        .polaroid-card:hover {
            transform: scale(1.06) rotate(0deg) !important;
            box-shadow: 0 16px 40px rgba(0,0,0,0.7), 0 0 20px rgba(255, 215, 0, 0.35);
            z-index: 10;
        }

        .polaroid-pin {
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #FFE58F, #C8A24A 60%, #684B12 100%);
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
            z-index: 2;
        }

        .polaroid-img-wrap {
            width: 100%;
            aspect-ratio: 4 / 5;
            background: #111;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .polaroid-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .polaroid-card:hover .polaroid-img-wrap img {
            transform: scale(1.05);
        }

        .polaroid-caption {
            font-family: var(--font-hand);
            font-size: 1.15rem;
            color: #222;
            line-height: 1.25;
            text-align: center;
            margin-top: 4px;
            padding: 0 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .polaroid-likes {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            color: #D81B60;
            font-weight: 700;
            margin-top: 4px;
        }

        /* ============================================================
           MODAL PARA ESCRIBIR MENSAJITO BONITO
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
            animation: fadeIn 0.25s ease;
        }

        .msg-modal-box {
            background: linear-gradient(145deg, #062E25, #004D38);
            border: 2px solid var(--dorado-puro);
            border-radius: 24px;
            max-width: 500px;
            width: 100%;
            padding: 28px 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.85), inset 0 0 40px rgba(200, 162, 74, 0.15);
            position: relative;
            max-height: 94vh;
            overflow-y: auto;
        }

        .modal-close-btn {
            position: absolute;
            top: 16px;
            right: 18px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: #FFF;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.2s;
        }

        .modal-close-btn:hover {
            background: var(--dorado);
            color: #041B16;
            transform: scale(1.1);
        }

        .modal-title {
            font-family: var(--font-script);
            font-size: 2.8rem;
            color: var(--dorado-puro);
            text-align: center;
            margin-bottom: 2px;
            line-height: 1;
        }

        .modal-subtitle {
            text-align: center;
            font-size: 0.8rem;
            color: var(--dorado-claro);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--dorado-brillante);
            margin-bottom: 6px;
        }

        .form-input, .form-textarea {
            width: 100%;
            background: rgba(255, 255, 255, 0.08);
            border: 1.5px solid rgba(200, 162, 74, 0.4);
            border-radius: 12px;
            padding: 12px 14px;
            color: #FFF;
            font-family: var(--font-sans);
            font-size: 0.92rem;
            outline: none;
            transition: all 0.25s;
        }

        .form-input:focus, .form-textarea:focus {
            border-color: var(--dorado-puro);
            background: rgba(255, 255, 255, 0.14);
            box-shadow: 0 0 12px rgba(255, 215, 0, 0.35);
        }

        /* Selector de Stickers / Iconos */
        .emoji-selector {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .emoji-option {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.35);
            border: 1.5px solid rgba(200, 162, 74, 0.4);
            font-size: 1.35rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
        }

        .emoji-option:hover {
            transform: scale(1.15);
            border-color: var(--dorado-puro);
        }

        .emoji-option.selected {
            background: var(--dorado);
            border-color: var(--dorado-puro);
            transform: scale(1.2);
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.6);
        }

        /* Selector de Colores de Nota */
        .color-selector {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 16px;
        }

        .color-option {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid rgba(255,255,255,0.4);
            transition: all 0.2s;
        }

        .color-option.selected {
            border-color: #FFF;
            transform: scale(1.25);
            box-shadow: 0 0 10px rgba(255,255,255,0.8);
        }

        /* Sugerencias rápidas de dedicatorias */
        .quick-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 14px;
        }

        .quick-chip {
            background: rgba(200, 162, 74, 0.15);
            border: 1px solid rgba(200, 162, 74, 0.35);
            color: var(--dorado-claro);
            font-size: 0.74rem;
            padding: 4px 10px;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quick-chip:hover {
            background: var(--dorado);
            color: #041B16;
            font-weight: 600;
        }

        .btn-submit-dedication {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #FFD700 0%, #C8A24A 100%);
            color: #041B16;
            border: none;
            border-radius: 30px;
            font-family: var(--font-serif);
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(200, 162, 74, 0.45);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit-dedication:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.6);
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
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(14px);
            z-index: 100000;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px;
            animation: fadeIn 0.2s ease;
            user-select: none;
        }

        .lightbox-close-btn {
            position: absolute;
            top: 18px;
            right: 20px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: #FFF;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            font-size: 1.3rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 100005;
        }

        .lightbox-close-btn:hover {
            background: var(--dorado);
            color: #041B16;
            transform: scale(1.1);
        }

        .lightbox-counter-badge {
            position: absolute;
            top: 22px;
            left: 24px;
            background: rgba(0,0,0,0.65);
            border: 1px solid rgba(200, 162, 74, 0.4);
            color: var(--dorado-brillante);
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 0.86rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            z-index: 100005;
        }

        .lightbox-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.65);
            border: 1.5px solid var(--dorado);
            color: #FFF;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            font-size: 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s;
            z-index: 100002;
            box-shadow: 0 4px 20px rgba(0,0,0,0.6);
        }

        .lightbox-nav-btn:hover {
            background: var(--dorado);
            color: #041B16;
            transform: translateY(-50%) scale(1.12);
        }

        .lightbox-prev-btn { left: 20px; }
        .lightbox-next-btn { right: 20px; }

        @media (max-width: 650px) {
            .lightbox-prev-btn { left: 8px; width: 40px; height: 40px; font-size: 1.05rem; }
            .lightbox-next-btn { right: 8px; width: 40px; height: 40px; font-size: 1.05rem; }
            .lightbox-counter-badge { top: 16px; left: 16px; font-size: 0.76rem; padding: 3px 12px; }
        }

        .lightbox-media-container {
            max-width: 90vw;
            max-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .lightbox-media-container img, .lightbox-media-container video {
            max-width: 90vw;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 12px;
            border: 2px solid var(--dorado);
            box-shadow: 0 15px 50px rgba(0,0,0,0.9);
        }

        .lightbox-info {
            margin-top: 14px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .lightbox-title {
            font-family: var(--font-serif);
            font-size: 1.2rem;
            color: var(--dorado-brillante);
            font-weight: 700;
        }

        .lightbox-text {
            font-size: 0.9rem;
            color: #EEE;
            font-style: italic;
            margin: 4px 0 12px 0;
        }

        .lightbox-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-lightbox-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-download-media {
            background: rgba(200, 162, 74, 0.2);
            border: 1.5px solid var(--dorado);
            color: var(--dorado-claro);
        }

        .btn-download-media:hover {
            background: var(--dorado);
            color: #041B16;
        }

        /* Footer elegante */
        .book-footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 25px;
            border-top: 1px solid rgba(200, 162, 74, 0.25);
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .book-footer a {
            color: var(--dorado-brillante);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <!-- Fondo de polvo de hadas / destellos mágicos -->
    <canvas id="magicCanvas"></canvas>

    <!-- Audio de fondo (Canción de la fiesta) -->
    <audio id="bgMusic" loop preload="auto">
        <source src="musica.mp3" type="audio/mpeg">
        <source src="musica.m4a" type="audio/mp4">
    </audio>

    <!-- Banner flotante si el navegador bloquea la reproducción automática -->
    <div id="unblockAudioBanner" class="audio-unblock-banner">
        <i class="fas fa-music"></i>
        <span>¡Toca aquí para encender la música de la fiesta! 🎶✨</span>
    </div>

    <div class="page-wrapper">

        <!-- Barra Superior de Navegación -->
        <header class="top-nav">
            <a href="index.php" class="btn-back-home">
                <i class="fas fa-arrow-left"></i> Volver a la Invitación
            </a>

            <!-- Vinilo Giratorio Interactivo -->
            <div class="music-widget" id="musicToggleWidget" title="Reproducir / Pausar Música">
                <div class="vinyl-disc" id="vinylDisc">
                    <div class="vinyl-center">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
                <div class="music-info">
                    <div class="music-title">
                        <span id="musicStateText">Música de la Fiesta</span>
                        <div class="equalizer-bars" id="eqBars">
                            <span class="eq-bar"></span>
                            <span class="eq-bar"></span>
                            <span class="eq-bar"></span>
                        </div>
                    </div>
                    <div class="music-subtitle">Mis XV Años · Angie Karolina</div>
                </div>
            </div>
        </header>

        <!-- Portada / Cabecera del Libro Mágico -->
        <section class="book-hero">
            <div class="corner-filigree corner-tl"></div>
            <div class="corner-filigree corner-tr"></div>
            <div class="corner-filigree corner-bl"></div>
            <div class="corner-filigree corner-br"></div>

            <div class="book-badge">
                <i class="fas fa-book-open"></i> Edición Oficial & Exclusiva
            </div>

            <h1 class="book-title-script">Angie Karolina</h1>
            <h2 class="book-title-sub">Libro Mágico de Recuerdos · Mis XV Años</h2>
            <div class="gold-divider"></div>

            <p class="book-desc">
                "Los momentos más hermosos de la vida se guardan para siempre en el corazón y en este libro de recuerdos. 
                Revive cada sonrisa, cada baile y déjame tu mensajito de cariño." ✨👑
            </p>

            <!-- Estadísticas en tiempo real -->
            <div class="stats-bar">
                <div class="stat-pill">
                    <i class="fas fa-camera-retro" style="color: var(--dorado-puro);"></i>
                    <span><strong>160</strong> Fotos de la Fiesta</span>
                </div>
                <div class="stat-pill">
                    <i class="fas fa-film" style="color: #FF6B8B;"></i>
                    <span><strong>19</strong> Videos Exclusivos</span>
                </div>
                <div class="stat-pill">
                    <i class="fas fa-heart" style="color: #EF4444;"></i>
                    <span><strong id="statDedicatoriasCount">4</strong> Mensajitos de Amor</span>
                </div>
            </div>
        </section>

        <!-- Barra de Filtros y Botón para Escribir Mensajito -->
        <nav class="nav-tabs-wrapper">
            <button class="tab-btn active" data-tab="tab-todo">
                <i class="fas fa-sparkles"></i> Todo el Libro
            </button>
            <button class="tab-btn" data-tab="tab-fotos">
                <i class="fas fa-images"></i> Fotos (160)
            </button>
            <button class="tab-btn" data-tab="tab-videos">
                <i class="fas fa-video"></i> Videos (19)
            </button>
            <button class="tab-btn" data-tab="tab-mensajes">
                <i class="fas fa-envelope-open-text"></i> Dedicatorias
            </button>
            <button class="tab-btn btn-write-message" id="btnOpenWriteModal">
                <i class="fas fa-pen-fancy"></i> ¡Dejar mi Mensajito!
            </button>
        </nav>

        <!-- ========================================================
             1. PESTAÑA: TODO EL LIBRO (VISTA COMPLETA SCRAPBOOK)
             ======================================================== -->
        <div id="tab-todo" class="tab-content active">
            
            <!-- Destacado de Dedicatorias -->
            <div class="guestbook-section-head">
                <div>
                    <h3 style="font-family: var(--font-serif); color: var(--dorado-brillante); font-size: 1.25rem;">
                        💌 Mensajitos & Dedicatorias de los Invitados
                    </h3>
                    <p style="font-size: 0.82rem; color: rgba(255,255,255,0.75);">
                        Dedicatorias llenas de bendición y amor escritas por familia y amigos
                    </p>
                </div>
                <button class="tab-btn btn-write-message" style="animation: none;" onclick="document.getElementById('btnOpenWriteModal').click();">
                    <i class="fas fa-feather-alt"></i> Escribir en el Libro
                </button>
            </div>

            <div class="guestbook-grid" id="guestbookGridTodo">
                <!-- Se llena dinámicamente -->
            </div>

            <!-- Destacado de Videos Exclusivos -->
            <div class="guestbook-section-head" style="margin-top: 35px;">
                <div>
                    <h3 style="font-family: var(--font-serif); color: var(--dorado-brillante); font-size: 1.25rem;">
                        🎬 Videos Exclusivos de la Fiesta (19 Momentos)
                    </h3>
                    <p style="font-size: 0.82rem; color: rgba(255,255,255,0.75);">
                        Los mejores clips de baile, entrada real y diversión en alta definición
                    </p>
                </div>
            </div>

            <div class="videos-grid" id="videosGridTodo">
                <!-- Se llena dinámicamente -->
            </div>

            <!-- Álbum Scrapbook de Fotografías -->
            <div class="guestbook-section-head" style="margin-top: 35px;">
                <div>
                    <h3 style="font-family: var(--font-serif); color: var(--dorado-brillante); font-size: 1.25rem;">
                        📸 Álbum de Fotos Scrapbook (160 Recuerdos Mágicos)
                    </h3>
                    <p style="font-size: 0.82rem; color: rgba(255,255,255,0.75);">
                        Toca cualquier foto para ampliarla a pantalla completa y descargarla en alta calidad
                    </p>
                </div>
            </div>

            <div class="photos-masonry" id="photosGridTodo">
                <!-- Se llena dinámicamente -->
            </div>
        </div>

        <!-- ========================================================
             2. PESTAÑA: SÓLO FOTOGRAFÍAS (160 FOTOS)
             ======================================================== -->
        <div id="tab-fotos" class="tab-content">
            <div class="guestbook-section-head">
                <div>
                    <h3 style="font-family: var(--font-serif); color: var(--dorado-brillante); font-size: 1.3rem;">
                        📸 Galería Completa de Fotos de los XV Años
                    </h3>
                    <p style="font-size: 0.85rem; color: rgba(255,255,255,0.8);">
                        160 fotos tomadas durante toda la noche en orden cronológico
                    </p>
                </div>
            </div>
            <div class="photos-masonry" id="photosGridOnly">
                <!-- Se llena dinámicamente -->
            </div>
        </div>

        <!-- ========================================================
             3. PESTAÑA: SÓLO VIDEOS (19 VIDEOS)
             ======================================================== -->
        <div id="tab-videos" class="tab-content">
            <div class="guestbook-section-head">
                <div>
                    <h3 style="font-family: var(--font-serif); color: var(--dorado-brillante); font-size: 1.3rem;">
                        🎬 Sala de Cine · 19 Videos de la Noche
                    </h3>
                    <p style="font-size: 0.85rem; color: rgba(255,255,255,0.8);">
                        Reproduce los videos directamente o tócalos para verlos en pantalla completa
                    </p>
                </div>
            </div>
            <div class="videos-grid" id="videosGridOnly">
                <!-- Se llena dinámicamente -->
            </div>
        </div>

        <!-- ========================================================
             4. PESTAÑA: DEDICATORIAS & MENSAJITOS
             ======================================================== -->
        <div id="tab-mensajes" class="tab-content">
            <div class="guestbook-section-head">
                <div>
                    <h3 style="font-family: var(--font-serif); color: var(--dorado-brillante); font-size: 1.3rem;">
                        💌 El Libro de Firmas & Deseos para Angie
                    </h3>
                    <p style="font-size: 0.85rem; color: rgba(255,255,255,0.8);">
                        Lee todas las dedicatorias y déjale tu bendición a la quinceañera
                    </p>
                </div>
                <button class="tab-btn btn-write-message" style="animation: none;" onclick="document.getElementById('btnOpenWriteModal').click();">
                    <i class="fas fa-pen-nib"></i> ¡Escribir mi Mensajito!
                </button>
            </div>
            <div class="guestbook-grid" id="guestbookGridOnly">
                <!-- Se llena dinámicamente -->
            </div>
        </div>

        <!-- Pie de página -->
        <footer class="book-footer">
            <p style="font-family: var(--font-script); font-size: 2.2rem; color: var(--dorado-puro); margin-bottom: 2px;">
                Angie Karolina
            </p>
            <p style="font-size: 0.82rem; letter-spacing: 1px; color: var(--dorado-claro);">
                MIS XV AÑOS · 3 DE OCTUBRE DE 2026
            </p>
            <p style="margin-top: 10px; font-size: 0.78rem;">
                Hecho con todo el amor del mundo para celebrar una noche inolvidable ✨💖
            </p>
        </footer>

    </div>

    <!-- ============================================================
         MODAL: ESCRIBIR MENSAJITO EN EL LIBRO
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
                        <div class="emoji-option" data-emoji="💖" title="Con mucho amor">💖</div>
                        <div class="emoji-option" data-emoji="✨" title="Momentos mágicos">✨</div>
                        <div class="emoji-option" data-emoji="🥂" title="¡Salud por Angie!">🥂</div>
                        <div class="emoji-option" data-emoji="🎂" title="Feliz Cumpleaños">🎂</div>
                        <div class="emoji-option" data-emoji="💃" title="¡A bailar!">💃</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Estilo de tu Tarjetita:</label>
                    <div class="color-selector" id="colorSelector">
                        <div class="color-option selected" data-color="gold" style="background: #FFE89E;" title="Dorado Real"></div>
                        <div class="color-option" data-color="emerald" style="background: #A7F3D0;" title="Verde Esmeralda"></div>
                        <div class="color-option" data-color="rose" style="background: #FBCFE8;" title="Rosa Princesa"></div>
                        <div class="color-option" data-color="cream" style="background: #FFFBEB;" title="Crema Clásico"></div>
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
                    <i class="fas fa-heart"></i> Publicar en el Libro
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
                <a href="#" id="btnDownloadMedia" class="btn-lightbox-action btn-download-media" download target="_blank">
                    <i class="fas fa-download"></i> Descargar
                </a>
            </div>
        </div>
    </div>

    <!-- Lógica JavaScript del Libro Mágico -->
    <script>
    (() => {
        // Detección inteligente de prefijo base según la ruta actual del navegador
        const isPublicSubdir = window.location.pathname.includes('/public/');
        const basePrefix = isPublicSubdir ? '../' : './';

        // 19 Videos cinematográficos oficiales de los XV Años
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

        // 160 Fotografías Polaroid oficiales
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

        // Dedicatorias iniciales de muestra
        const defaultMensajes = [
            {
                id: 1,
                nombre: 'Familia Avendaño Rivera',
                mensaje: 'Hija amada, verte cumplir tus 15 años es el regalo más hermoso de nuestras vidas. Que Dios ilumine tu camino siempre. ¡Te amamos infinitamente! 💖👑',
                icono: '👑',
                color: 'gold',
                likes: 34,
                fecha_formateada: '04/10/2026 20:45'
            },
            {
                id: 2,
                nombre: 'Tía Carmen & Primos',
                mensaje: '¡Felicidades Angie preciosa! Una noche inolvidable, te veías radiante como un cuento de hadas. ¡Que todos tus sueños se hagan realidad! ✨🥂',
                icono: '✨',
                color: 'emerald',
                likes: 22,
                fecha_formateada: '04/10/2026 21:10'
            },
            {
                id: 3,
                nombre: 'Tus Mejores Amigas del Cole',
                mensaje: '¡La mejor fiesta de 15 del mundo! Bailamos sin parar toda la noche. ¡Te amamos muchísimo mejor amiga! 💃💕🎂',
                icono: '💖',
                color: 'rose',
                likes: 29,
                fecha_formateada: '04/10/2026 21:30'
            },
            {
                id: 4,
                nombre: 'Padrinos de Angie',
                mensaje: 'Orgullosos de verte convertida en una señorita tan dulce, inteligente y llena de luz. Siempre estaremos para apoyarte. ¡Feliz cumpleaños! 🌟🥂',
                icono: '🥂',
                color: 'cream',
                likes: 18,
                fecha_formateada: '04/10/2026 21:50'
            }
        ];

        // Estado global de los datos (precargado instantáneamente para que NUNCA esté en blanco)
        let albumData = {
            mensajes: defaultMensajes,
            videos: defaultVideos,
            fotos: defaultPhotos
        };

        let currentMediaList = []; // Lista de fotos o videos activos en el visor
        let currentMediaIndex = 0;
        let selectedEmoji = '👑';
        let selectedColor = 'gold';

        // Elementos DOM
        const bgMusic = document.getElementById('bgMusic');
        const unblockAudioBanner = document.getElementById('unblockAudioBanner');
        const musicToggleWidget = document.getElementById('musicToggleWidget');
        const vinylDisc = document.getElementById('vinylDisc');
        const musicStateText = document.getElementById('musicStateText');
        const eqBars = document.getElementById('eqBars');

        const tabBtns = document.querySelectorAll('.tab-btn[data-tab]');
        const tabContents = document.querySelectorAll('.tab-content');

        const writeMessageModal = document.getElementById('writeMessageModal');
        const btnOpenWriteModal = document.getElementById('btnOpenWriteModal');
        const btnCloseWriteModal = document.getElementById('btnCloseWriteModal');
        const formNewDedication = document.getElementById('formNewDedication');
        const formStatusMsg = document.getElementById('formStatusMsg');
        const btnSubmitDedication = document.getElementById('btnSubmitDedication');

        const mediaLightbox = document.getElementById('mediaLightbox');
        const btnCloseLightbox = document.getElementById('btnCloseLightbox');
        const btnPrevMedia = document.getElementById('btnPrevMedia');
        const btnNextMedia = document.getElementById('btnNextMedia');
        const lightboxCounterBadge = document.getElementById('lightboxCounterBadge');
        const lightboxMediaContainer = document.getElementById('lightboxMediaContainer');
        const lightboxMediaTitle = document.getElementById('lightboxMediaTitle');
        const lightboxMediaText = document.getElementById('lightboxMediaText');
        const btnDownloadMedia = document.getElementById('btnDownloadMedia');

        // ============================================================
        // 1. REPRODUCTOR DE MÚSICA & AUTOPLAY INTELIGENTE
        // ============================================================
        let isMusicPlaying = false;

        function updateMusicUI(playing) {
            isMusicPlaying = playing;
            if (playing) {
                vinylDisc.classList.add('playing');
                eqBars.classList.add('playing');
                musicStateText.textContent = 'Sonando Música ✨';
                unblockAudioBanner.style.display = 'none';
            } else {
                vinylDisc.classList.remove('playing');
                eqBars.classList.remove('playing');
                musicStateText.textContent = 'Música Pausada';
            }
        }

        async function tryPlayMusic() {
            try {
                await bgMusic.play();
                updateMusicUI(true);
            } catch (err) {
                updateMusicUI(false);
                unblockAudioBanner.style.display = 'flex';
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
        unblockAudioBanner.addEventListener('click', () => {
            bgMusic.play().then(() => updateMusicUI(true)).catch(() => {});
        });

        window.addEventListener('click', function unlockOnce() {
            if (bgMusic.paused && !isMusicPlaying) {
                bgMusic.play().then(() => updateMusicUI(true)).catch(() => {});
            }
            window.removeEventListener('click', unlockOnce);
        }, { once: true });

        tryPlayMusic();

        // ============================================================
        // 2. SISTEMA DE PESTAÑAS (TABS)
        // ============================================================
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));

                btn.classList.add('active');
                const target = document.getElementById(btn.dataset.tab);
                if (target) target.classList.add('active');

                // Pausar cualquier video activo al cambiar de pestaña
                document.querySelectorAll('video').forEach(v => v.pause());
            });
        });

        // ============================================================
        // 3. CARGAR DATOS DESDE LA API (FOTOS, VIDEOS, MENSAJES)
        // ============================================================
        async function loadAlbumData() {
            // 3.1 Cargar mensajes y videos desde la API
            try {
                const resLibro = await fetch(basePrefix + 'api/libro.php?t=' + Date.now());
                if (resLibro.ok) {
                    const dataLibro = await resLibro.json();
                    if (dataLibro && dataLibro.success) {
                        if (Array.isArray(dataLibro.mensajes) && dataLibro.mensajes.length > 0) {
                            albumData.mensajes = dataLibro.mensajes;
                            renderMessages();
                            const statBadge = document.getElementById('statDedicatoriasCount');
                            if (statBadge) statBadge.textContent = albumData.mensajes.length;
                        }
                        if (Array.isArray(dataLibro.videos) && dataLibro.videos.length > 0) {
                            albumData.videos = dataLibro.videos;
                            renderVideos();
                        }
                    }
                }
            } catch (err) {
                console.warn('API libro offline, usando videos predefinidos:', err);
            }

            // 3.2 Cargar las 160 fotos desde la API
            try {
                const resFotos = await fetch(basePrefix + 'api/fotos.php?limit=500&order=asc&t=' + Date.now());
                if (resFotos.ok) {
                    const dataFotos = await resFotos.json();
                    if (dataFotos && dataFotos.success && Array.isArray(dataFotos.fotos) && dataFotos.fotos.length > 0) {
                        albumData.fotos = dataFotos.fotos;
                        renderPhotos();
                    }
                }
            } catch (err) {
                console.warn('API fotos offline, usando fotos locales:', err);
            }
        }

        // Renderizar dedicatorias
        function renderMessages() {
            const containers = [
                document.getElementById('guestbookGridTodo'),
                document.getElementById('guestbookGridOnly')
            ];

            const html = albumData.mensajes.map(m => `
                <div class="note-card ${m.color || 'gold'}" data-id="${m.id}">
                    <div class="washi-tape"></div>
                    <div>
                        <div class="note-header">
                            <div class="note-icon-badge">${m.icono || '✨'}</div>
                            <div class="note-author-info">
                                <div class="note-author">${m.nombre}</div>
                                <div class="note-date">${m.fecha_formateada || 'Mis XV'}</div>
                            </div>
                        </div>
                        <div class="note-body">"${m.mensaje}"</div>
                    </div>
                    <div class="note-footer">
                        <button type="button" class="btn-like-note" onclick="likeDedication(${m.id}, this)">
                            <i class="fas fa-heart"></i> <span class="like-count">${m.likes || 0}</span> Me encanta
                        </button>
                        <span style="opacity: 0.6; font-size: 0.72rem;">✨ Recuerdo de Angie</span>
                    </div>
                </div>
            `).join('');

            containers.forEach(c => {
                if (c) c.innerHTML = html;
            });
        }

        // Like a una dedicatoria con destellos
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
                    btn.querySelector('.like-count').textContent = data.likes;
                    confetti({
                        particleCount: 15,
                        spread: 50,
                        origin: { y: 0.8 },
                        colors: ['#FF69B4', '#FFD700', '#FF1493']
                    });
                }
            } catch(e) {}
        };

        // Renderizar videos (soporta ruta directa de asset con fallback a streaming PHP)
        function renderVideos() {
            const containers = [
                document.getElementById('videosGridTodo'),
                document.getElementById('videosGridOnly')
            ];

            const html = albumData.videos.map((v, idx) => {
                const directSrc = basePrefix + (v.direct_url || `assets/videos/${v.archivo}`);
                const apiSrc = basePrefix + (v.url || `api/video.php?v=${encodeURIComponent(v.archivo)}`);

                return `
                <div class="video-card">
                    <div class="video-wrapper">
                        <video class="video-player" preload="metadata" playsinline controls>
                            <source src="${directSrc}" type="video/mp4">
                            <source src="${apiSrc}" type="video/mp4">
                            Tu navegador no soporta video HTML5.
                        </video>
                    </div>
                    <div class="video-card-body">
                        <div class="video-title">
                            <i class="fas fa-play-circle" style="color: var(--dorado-puro);"></i>
                            ${v.titulo}
                        </div>
                        <div class="video-meta">
                            <span>🎬 Momento #${v.id}</span>
                            <span>💾 ${v.tamano}</span>
                        </div>
                        <button type="button" class="btn-fullscreen-video" onclick="openLightboxVideo(${idx})">
                            <i class="fas fa-expand"></i> Pantalla Completa & Cine
                        </button>
                    </div>
                </div>
                `;
            }).join('');

            containers.forEach(c => {
                if (c) c.innerHTML = html;
            });
        }

        // Renderizar fotos estilo Polaroid con doble fallback
        function renderPhotos() {
            const containers = [
                document.getElementById('photosGridTodo'),
                document.getElementById('photosGridOnly')
            ];

            const html = albumData.fotos.map((f, idx) => {
                const directSrc = basePrefix + (f.direct_url || `assets/fotos/${f.archivo}`);
                const apiSrc = basePrefix + (f.url || `api/foto.php?f=${encodeURIComponent(f.archivo)}`);

                return `
                <div class="polaroid-card" onclick="openLightboxPhoto(${idx})">
                    <div class="polaroid-pin"></div>
                    <div class="polaroid-img-wrap">
                        <img src="${directSrc}" 
                             onerror="if(!this.dataset.triedApi){this.dataset.triedApi=1; this.src='${apiSrc}';}" 
                             alt="${f.nombre_invitado}" loading="lazy">
                    </div>
                    <div class="polaroid-caption">${f.mensaje || 'Mis XV Años ✨'}</div>
                    <div class="polaroid-likes">
                        <i class="fas fa-heart"></i> ${f.likes || 0}
                    </div>
                </div>
                `;
            }).join('');

            containers.forEach(c => {
                if (c) c.innerHTML = html;
            });
        }

        // Render inicial inmediato para que la pantalla NUNCA esté en blanco
        renderMessages();
        renderVideos();
        renderPhotos();

        // ============================================================
        // 4. MODAL: ESCRIBIR NUEVO MENSAJITO
        // ============================================================
        btnOpenWriteModal.addEventListener('click', () => {
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

        // Selección de emoji
        document.querySelectorAll('.emoji-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.emoji-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                selectedEmoji = opt.dataset.emoji;
            });
        });

        // Selección de color
        document.querySelectorAll('.color-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.color-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                selectedColor = opt.dataset.color;
            });
        });

        // Chips rápidos
        document.querySelectorAll('.quick-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const txt = document.getElementById('dedicationMessage');
                txt.value = chip.dataset.text;
                txt.focus();
            });
        });

        // Enviar dedicatoria
        formNewDedication.addEventListener('submit', async (e) => {
            e.preventDefault();
            const nombre = document.getElementById('dedicationName').value.trim();
            const mensaje = document.getElementById('dedicationMessage').value.trim();

            if (!nombre || !mensaje) return;

            btnSubmitDedication.disabled = true;
            btnSubmitDedication.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando recuerdo...';
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
                        color: selectedColor
                    })
                });

                const data = await res.json();
                if (data && data.success) {
                    formStatusMsg.textContent = '✨ ¡Tu mensajito quedó grabado con éxito!';
                    formStatusMsg.style.color = '#A7F3D0';

                    // Lluvia de confeti triunfal
                    confetti({
                        particleCount: 120,
                        spread: 80,
                        origin: { y: 0.6 },
                        colors: ['#FFD700', '#C8A24A', '#E91E63', '#00E676', '#FFFFFF']
                    });

                    // Añadir inmediatamente a la lista local
                    if (data.mensaje_nuevo) {
                        albumData.mensajes.unshift(data.mensaje_nuevo);
                        renderMessages();
                        const statBadge = document.getElementById('statDedicatoriasCount');
                        if (statBadge) statBadge.textContent = albumData.mensajes.length;
                    }

                    setTimeout(() => {
                        writeMessageModal.style.display = 'none';
                        formNewDedication.reset();
                        btnSubmitDedication.disabled = false;
                        btnSubmitDedication.innerHTML = '<i class="fas fa-heart"></i> Publicar en el Libro';
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

        // ============================================================
        // 5. VISOR A PANTALLA COMPLETA (LIGHTBOX) PARA FOTOS Y VIDEOS
        // ============================================================
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

        // Teclado
        window.addEventListener('keydown', (e) => {
            if (mediaLightbox.style.display === 'flex') {
                if (e.key === 'ArrowRight') nextMedia();
                else if (e.key === 'ArrowLeft') prevMedia();
                else if (e.key === 'Escape') {
                    mediaLightbox.style.display = 'none';
                    lightboxMediaContainer.innerHTML = '';
                }
            }
        });

        // Gestos Swipe en celulares
        let startX = 0;
        mediaLightbox.addEventListener('touchstart', (e) => {
            if (e.changedTouches && e.changedTouches[0]) startX = e.changedTouches[0].screenX;
        }, { passive: true });

        mediaLightbox.addEventListener('touchend', (e) => {
            if (e.changedTouches && e.changedTouches[0]) {
                const diff = e.changedTouches[0].screenX - startX;
                if (Math.abs(diff) > 45) {
                    if (diff < 0) nextMedia();
                    else prevMedia();
                }
            }
        }, { passive: true });

        // Carga inicial
        loadAlbumData();

        // ============================================================
        // 6. CANVAS DE POLVO DE HADAS Y DESTELLOS MÁGICOS
        // ============================================================
        const canvas = document.getElementById('magicCanvas');
        const ctx = canvas.getContext('2d');
        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        });

        const particles = [];
        const particleCount = Math.min(45, Math.floor(window.innerWidth / 25));

        class Sparkle {
            constructor() {
                this.reset();
            }
            reset() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.size = Math.random() * 2.8 + 0.6;
                this.speedY = -(Math.random() * 0.4 + 0.15);
                this.speedX = (Math.random() - 0.5) * 0.35;
                this.opacity = Math.random() * 0.7 + 0.2;
                this.fade = Math.random() * 0.008 + 0.003;
                this.color = Math.random() > 0.3 ? '#FFD700' : '#FFFDF5';
            }
            update() {
                this.y += this.speedY;
                this.x += this.speedX;
                this.opacity += this.fade;
                if (this.opacity > 0.95 || this.opacity < 0.15) {
                    this.fade = -this.fade;
                }
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

        for (let i = 0; i < particleCount; i++) {
            particles.push(new Sparkle());
        }

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
