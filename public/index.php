<?php
/**
 * Invitación XV Años - Angie Karolina Avendaño Rivera
 * Página principal de la invitación digital
 */

// Detectar esquema y host para generar URL absoluta de imagen para WhatsApp y redes
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) 
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
    ? 'https://' : 'http://';

$host = $_SERVER['HTTP_HOST'] ?? 'kickoff-invitacionangie.0gxuxx.easypanel.host';
$baseUrl = rtrim($protocol . $host, '/');
$shareImageUrl = $baseUrl . '/angie-portada.jpg';
$pageUrl = $baseUrl . '/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mis XV Años - Angie Karolina Avendaño Rivera</title>
    <meta name="description" content="Invitación digital a la celebración de XV años de Angie Karolina Avendaño Rivera. 3 de octubre de 2026, Mérida, Venezuela.">
    <meta name="theme-color" content="#062E25">

    <!-- Open Graph / WhatsApp / Facebook / Telegram Preview -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($pageUrl) ?>">
    <meta property="og:title" content="Mis XV Años - Angie Karolina Avendaño Rivera">
    <meta property="og:description" content="¡Te invito a celebrar mis XV Años! Acompáñanos este 3 de Octubre de 2026 para una noche mágica e inolvidable.">
    <meta property="og:image" content="<?= htmlspecialchars($shareImageUrl) ?>">
    <meta property="og:image:secure_url" content="<?= htmlspecialchars($shareImageUrl) ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:alt" content="Angie Karolina Avendaño Rivera - Mis XV Años">
    <meta property="og:locale" content="es_LA">
    <meta property="og:site_name" content="Mis XV Años - Angie Karolina">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= htmlspecialchars($pageUrl) ?>">
    <meta name="twitter:title" content="Mis XV Años - Angie Karolina Avendaño Rivera">
    <meta name="twitter:description" content="¡Te invito a celebrar mis XV Años! Acompáñanos este 3 de Octubre de 2026 para una noche mágica e inolvidable.">
    <meta name="twitter:image" content="<?= htmlspecialchars($shareImageUrl) ?>">

    <!-- Favicon: Corona Real Dorada y Esmeralda -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='30' fill='%23062E25' stroke='%23C8A24A' stroke-width='2.5'/%3E%3Cpath d='M13 43 L17 24 L24 33 L32 15 L40 33 L47 24 L51 43 Z' fill='%23FFD700' stroke='%23FFF0BA' stroke-width='0.8'/%3E%3Cpath d='M13 43 Q32 47 51 43 L51 47 Q32 51 13 47 Z' fill='%23C8A24A'/%3E%3Ccircle cx='17' cy='23' r='2.8' fill='%23FFFDF5'/%3E%3Ccircle cx='24' cy='32' r='2.2' fill='%23FFFDF5'/%3E%3Ccircle cx='32' cy='14' r='3.6' fill='%23FFFDF5'/%3E%3Ccircle cx='40' cy='32' r='2.2' fill='%23FFFDF5'/%3E%3Ccircle cx='47' cy='23' r='2.8' fill='%23FFFDF5'/%3E%3Cpolygon points='32,25 36,32 32,39 28,32' fill='%23006B4F' stroke='%23FFF0BA' stroke-width='0.8'/%3E%3Ccircle cx='22' cy='45' r='1.8' fill='%23006B4F'/%3E%3Ccircle cx='32' cy='47' r='2.2' fill='%23006B4F'/%3E%3Ccircle cx='42' cy='45' r='1.8' fill='%23006B4F'/%3E%3C/svg%3E">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon.png">
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="favicon.png">

    <!-- Tipografías -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Generador de Códigos QR -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- Captura de Ticket a Imagen (Pase VIP Completo) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>



    <style>
        /* ============================================================
           VARIABLES Y RESET
           ============================================================ */
        :root {
            --verde-oscuro: #062E25;
            --verde-medio: #006B4F;
            --verde-claro: #2F8F68;
            --dorado: #C8A24A;
            --dorado-claro: #E7D49A;
            --crema: #FFF8EC;
            --marron: #1A1410;

            --font-script: 'Great Vibes', cursive;
            --font-serif: 'Playfair Display', serif;
            --font-sans: 'Montserrat', sans-serif;

            --card-max-width: 420px;
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: var(--font-sans);
            background: var(--verde-oscuro);
            color: var(--crema);
            -webkit-font-smoothing: antialiased;
        }

        /* ============================================================
           DECK DE TARJETAS DESLIZABLES
           ============================================================ */
        .deck-container {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .card-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                        opacity 0.6s ease;
            will-change: transform, opacity;
        }

        .card-slide.active {
            transform: translateX(0);
            opacity: 1;
            z-index: 10;
        }

        .card-slide.prev {
            transform: translateX(-100%);
            opacity: 0;
            z-index: 5;
        }

        .card-slide.next {
            transform: translateX(100%);
            opacity: 0;
            z-index: 5;
        }

        .card-slide.hidden {
            transform: translateX(100%);
            opacity: 0;
            z-index: 1;
            pointer-events: none;
        }

        /* ============================================================
           TARJETA BASE
           ============================================================ */
        .card {
            background: linear-gradient(145deg, rgba(6, 46, 37, 0.95), rgba(0, 107, 79, 0.9));
            border: 2px solid var(--dorado);
            border-radius: 20px;
            width: 100%;
            max-width: var(--card-max-width);
            max-height: calc(100vh - 40px);
            max-height: calc(100dvh - 40px);
            overflow-y: auto;
            overflow-x: hidden;
            padding: 35px 25px 110px 25px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5),
                        0 0 30px rgba(200, 162, 74, 0.15),
                        inset 0 1px 0 rgba(231, 212, 154, 0.1);
            scrollbar-width: thin;
            scrollbar-color: var(--dorado) transparent;
        }

        .card::-webkit-scrollbar {
            width: 4px;
        }

        .card::-webkit-scrollbar-track {
            background: transparent;
        }

        .card::-webkit-scrollbar-thumb {
            background: var(--dorado);
            border-radius: 2px;
        }

        /* Esquinas decorativas */
        .card::before,
        .card::after {
            content: '✦';
            position: absolute;
            color: var(--dorado);
            font-size: 18px;
            opacity: 0.6;
        }

        .card::before {
            top: 10px;
            left: 15px;
        }

        .card::after {
            bottom: 10px;
            right: 15px;
        }

        .corner-tl, .corner-tr, .corner-bl, .corner-br {
            position: absolute;
            width: 30px;
            height: 30px;
            border-color: var(--dorado);
            opacity: 0.4;
        }

        .corner-tl {
            top: 5px;
            left: 5px;
            border-top: 2px solid;
            border-left: 2px solid;
            border-radius: 5px 0 0 0;
        }

        .corner-tr {
            top: 5px;
            right: 5px;
            border-top: 2px solid;
            border-right: 2px solid;
            border-radius: 0 5px 0 0;
        }

        .corner-bl {
            bottom: 5px;
            left: 5px;
            border-bottom: 2px solid;
            border-left: 2px solid;
            border-radius: 0 0 0 5px;
        }

        .corner-br {
            bottom: 5px;
            right: 5px;
            border-bottom: 2px solid;
            border-right: 2px solid;
            border-radius: 0 0 5px 0;
        }

        /* ============================================================
           ÍCONO CIRCULAR DE TARJETA
           ============================================================ */
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 4px 15px rgba(200, 162, 74, 0.3);
        }

        .card-icon i {
            font-size: 24px;
            color: var(--verde-oscuro);
        }

        /* ============================================================
           TIPOGRAFÍA
           ============================================================ */
        .card-title-script {
            font-family: var(--font-script);
            font-size: 2.2rem;
            color: var(--dorado);
            text-align: center;
            margin-bottom: 5px;
            text-shadow: 0 2px 10px rgba(200, 162, 74, 0.3);
        }

        .card-title-serif {
            font-family: var(--font-serif);
            font-size: 1.1rem;
            color: var(--dorado-claro);
            text-align: center;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .card-text {
            font-family: var(--font-sans);
            font-size: 0.85rem;
            color: var(--crema);
            text-align: center;
            line-height: 1.7;
            font-weight: 300;
            opacity: 0.9;
        }

        /* Divisor dorado */
        .gold-divider {
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--dorado), transparent);
            margin: 15px auto;
        }

        /* ============================================================
           NAVEGACIÓN DEL DECK
           ============================================================ */
        .nav-arrows {
            position: fixed;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 16px;
            z-index: 120;
            pointer-events: auto;
        }

        .nav-arrow {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1.5px solid var(--dorado);
            background: rgba(6, 46, 37, 0.92);
            color: var(--dorado);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.3s ease;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.45);
            touch-action: manipulation;
        }

        .nav-arrow:hover {
            background: var(--dorado);
            color: var(--verde-oscuro);
            transform: scale(1.08);
        }

        .nav-arrow:disabled {
            opacity: 0.25;
            cursor: not-allowed;
            transform: none;
        }

        .nav-dots {
            position: fixed;
            bottom: 58px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 120;
            pointer-events: auto;
        }

        .nav-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(200, 162, 74, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .nav-dot.active {
            background: var(--dorado);
            transform: scale(1.3);
            box-shadow: 0 0 10px rgba(200, 162, 74, 0.5);
        }

        /* ============================================================
           TARJETA 1 — PORTADA
           ============================================================ */
        .cover-card {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 400px;
        }

        .cover-tiara {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 12px;
            animation: floatTiara 3.5s ease-in-out infinite;
        }

        .tiara-svg {
            width: 105px;
            height: auto;
            max-width: 100%;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.75)) drop-shadow(0 0 20px rgba(200, 162, 74, 0.45));
            transition: transform 0.3s ease;
        }

        @keyframes floatTiara {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-7px) scale(1.03); }
        }

        /* Marco elegante real para la foto de Angie en la Portada */
        .cover-photo-container {
            position: relative;
            margin: 4px auto 10px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .cover-photo-wrapper {
            position: relative;
            display: inline-block;
            border-radius: 95px 95px 24px 24px; /* Forma de arco real señorial */
            padding: 4.5px;
            background: linear-gradient(135deg, #FFFDF5 0%, #FFE680 20%, #C8A24A 45%, #8B6914 75%, #E2CA7F 100%);
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.7), 
                        0 0 30px rgba(200, 162, 74, 0.45),
                        inset 0 0 10px rgba(255, 253, 245, 0.5);
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .cover-photo-wrapper:hover {
            transform: translateY(-3px) scale(1.025);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.8), 0 0 40px rgba(200, 162, 74, 0.65);
        }

        .cover-photo-frame {
            position: relative;
            width: 170px;
            max-width: 52vw;
            aspect-ratio: 4 / 5;
            border-radius: 90px 90px 20px 20px;
            overflow: hidden;
            background: #062E25;
            border: 2px solid rgba(255, 253, 245, 0.35);
        }

        .cover-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 15%;
            display: block;
            transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .cover-photo-wrapper:hover .cover-photo-img {
            transform: scale(1.06);
        }

        .cover-photo-crown-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 5;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            font-size: 1.1rem;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5), 0 0 12px rgba(200, 162, 74, 0.6);
            border: 1.5px solid #FFFDF5;
        }

        .cover-photo-sheen {
            position: absolute;
            top: 0;
            left: -100%;
            width: 65%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent);
            transform: skewX(-22deg);
            animation: photoSheen 5s infinite;
            pointer-events: none;
        }

        .cover-presents {
            font-family: var(--font-sans);
            font-size: 0.7rem;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: var(--dorado-claro);
            opacity: 0.7;
            margin-bottom: 5px;
        }

        .cover-name {
            font-family: var(--font-script);
            font-size: 3rem;
            color: var(--dorado);
            line-height: 1.2;
            text-shadow: 0 3px 15px rgba(200, 162, 74, 0.4);
            margin-bottom: 10px;
        }

        .cover-xv {
            font-family: var(--font-serif);
            font-size: 4rem;
            color: var(--dorado);
            letter-spacing: 8px;
            font-weight: 700;
            text-shadow: 0 4px 20px rgba(200, 162, 74, 0.3);
            margin-bottom: 15px;
        }

        .cover-date {
            font-family: var(--font-sans);
            font-size: 0.8rem;
            letter-spacing: 3px;
            color: var(--dorado-claro);
            font-weight: 300;
        }

        .cover-swipe {
            margin-top: 30px;
            font-size: 0.7rem;
            color: var(--dorado-claro);
            opacity: 0.5;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.8; }
        }

        /* Partículas doradas */
        .particles-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: var(--dorado);
            border-radius: 50%;
            animation: sparkle var(--duration, 3s) var(--delay, 0s) infinite;
            opacity: 0;
        }

        @keyframes sparkle {
            0% { opacity: 0; transform: translateY(0) scale(0); }
            50% { opacity: 1; transform: translateY(-30px) scale(1); }
            100% { opacity: 0; transform: translateY(-60px) scale(0); }
        }

        /* ============================================================
           TARJETA 2 — CUENTA REGRESIVA
           ============================================================ */
        .countdown-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 20px 0;
        }

        .countdown-item {
            text-align: center;
            padding: 12px 5px;
            background: rgba(200, 162, 74, 0.1);
            border: 1px solid rgba(200, 162, 74, 0.2);
            border-radius: 10px;
        }

        .countdown-number {
            font-family: var(--font-serif);
            font-size: 1.8rem;
            color: var(--dorado);
            font-weight: 700;
            line-height: 1;
        }

        .countdown-label {
            font-family: var(--font-sans);
            font-size: 0.6rem;
            color: var(--dorado-claro);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        .calendar-btn-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .calendar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            max-width: 280px;
            padding: 13px 20px;
            background: linear-gradient(135deg, rgba(200, 162, 74, 0.22), rgba(231, 212, 154, 0.1));
            border: 1.5px solid var(--dorado);
            border-radius: 50px;
            color: var(--dorado-claro);
            font-family: var(--font-sans);
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.25, 1, 0.5, 1);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35), 0 0 15px rgba(200, 162, 74, 0.2);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .calendar-btn:hover {
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.45), 0 0 25px rgba(200, 162, 74, 0.45);
        }

        .calendar-btn i {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .calendar-btn:hover i.fa-bell {
            animation: ringBell 0.6s ease;
        }

        @keyframes ringBell {
            0%, 100% { transform: rotate(0); }
            20%, 60% { transform: rotate(15deg); }
            40%, 80% { transform: rotate(-15deg); }
        }

        /* ============================================================
           TARJETA: ÉRASE UNA VEZ (EL CUENTO DE HADAS DE VALIENTE)
           ============================================================ */
        .fairytale-story-box {
            text-align: center;
            padding: 4px 6px;
            max-width: 440px;
            margin: 0 auto;
        }

        .story-paragraph {
            font-family: var(--font-sans);
            font-size: 0.88rem;
            line-height: 1.65;
            color: var(--crema);
            opacity: 0.95;
            margin-bottom: 12px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }

        .story-drop-cap {
            float: left;
            font-family: var(--font-serif);
            font-size: 2.8rem;
            line-height: 0.8;
            padding-top: 4px;
            padding-right: 8px;
            padding-bottom: 2px;
            color: var(--dorado-claro);
            font-weight: 700;
            text-shadow: 0 0 12px rgba(200, 162, 74, 0.7);
        }

        .story-highlight {
            background: linear-gradient(135deg, rgba(200, 162, 74, 0.15), rgba(6, 46, 37, 0.4));
            border-left: 2px solid var(--dorado);
            border-right: 2px solid var(--dorado);
            border-radius: 10px;
            padding: 10px 14px;
            margin: 14px 0;
            font-family: var(--font-serif);
            font-size: 0.92rem;
            font-style: italic;
            color: #FFFDF5;
            line-height: 1.6;
            box-shadow: inset 0 0 15px rgba(200, 162, 74, 0.1);
        }

        .story-conclusion {
            font-family: var(--font-sans);
            font-size: 0.84rem;
            color: var(--dorado-claro);
            line-height: 1.55;
            margin-top: 8px;
            font-weight: 500;
        }

        .story-magic-spark {
            display: inline-block;
            color: var(--dorado);
            font-size: 1.25rem;
            margin-bottom: 4px;
            animation: pulseGlow 2.5s infinite ease-in-out;
        }

        /* ============================================================
           TARJETA 3 — INVITACIÓN FORMAL
           ============================================================ */
        .formal-text {
            font-family: var(--font-serif);
            font-style: italic;
            font-size: 0.95rem;
            color: var(--crema);
            text-align: center;
            line-height: 1.8;
            opacity: 0.9;
        }

        .formal-parents {
            font-family: var(--font-serif);
            font-size: 0.85rem;
            color: var(--dorado-claro);
            text-align: center;
            margin: 10px 0;
            line-height: 1.6;
        }

        /* ============================================================
           TARJETA 4 — LUGAR Y FECHA
           ============================================================ */
        .info-block {
            text-align: center;
            margin: 15px 0;
            padding: 15px;
            background: rgba(200, 162, 74, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(200, 162, 74, 0.15);
        }

        .info-label {
            font-family: var(--font-sans);
            font-size: 0.65rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--dorado);
            margin-bottom: 8px;
        }

        .info-value {
            font-family: var(--font-serif);
            font-size: 1rem;
            color: var(--crema);
            line-height: 1.5;
        }

        .map-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            border: none;
            border-radius: 25px;
            font-family: var(--font-sans);
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 1px;
        }

        .map-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(200, 162, 74, 0.4);
        }

        /* ============================================================
           TARJETA 5 — DRESS CODE
           ============================================================ */
        .dresscode-colors {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .color-swatch {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--dorado);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .color-swatch:hover {
            transform: scale(1.2);
        }

        .dresscode-note {
            font-family: var(--font-sans);
            font-size: 0.75rem;
            color: var(--dorado-claro);
            text-align: center;
            font-style: italic;
            margin-top: 10px;
            opacity: 0.8;
        }

        /* ============================================================
           TARJETA 6 — ITINERARIO
           ============================================================ */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--dorado), transparent);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 18px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--dorado);
            border: 2px solid var(--verde-oscuro);
        }

        .timeline-time {
            font-family: var(--font-sans);
            font-size: 0.7rem;
            color: var(--dorado);
            font-weight: 600;
            letter-spacing: 1px;
        }

        .timeline-event {
            font-family: var(--font-serif);
            font-size: 0.9rem;
            color: var(--crema);
            margin-top: 3px;
        }

        /* ============================================================
           TARJETA 7 — TRIVIA
           ============================================================ */
        .trivia-question {
            font-family: var(--font-serif);
            font-size: 1rem;
            color: var(--crema);
            text-align: center;
            margin-bottom: 15px;
        }

        .trivia-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .trivia-option {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            padding: 10px 14px;
            border: 1px solid rgba(200, 162, 74, 0.35);
            border-radius: 12px;
            background: rgba(200, 162, 74, 0.06);
            color: var(--crema);
            font-family: var(--font-sans);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: left;
            width: 100%;
            box-sizing: border-box;
            user-select: none;
        }

        .trivia-option .opt-letter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(200, 162, 74, 0.15);
            border: 1px solid var(--dorado);
            color: var(--dorado-claro);
            font-size: 0.75rem;
            font-weight: 600;
            flex-shrink: 0;
            transition: all 0.25s ease;
        }

        .trivia-option .opt-text {
            flex-grow: 1;
            line-height: 1.3;
        }

        .trivia-option .opt-icon {
            font-size: 0.95rem;
            flex-shrink: 0;
            transition: all 0.25s ease;
        }

        .trivia-option:hover:not(:disabled) {
            border-color: var(--dorado);
            background: rgba(200, 162, 74, 0.2);
            transform: translateY(-1px);
        }

        .trivia-option.correct {
            border-color: #2ECC71 !important;
            background: rgba(46, 204, 113, 0.25) !important;
            color: #FFFFFF !important;
            box-shadow: 0 0 12px rgba(46, 204, 113, 0.3);
        }

        .trivia-option.correct .opt-letter {
            background: #2ECC71;
            border-color: #2ECC71;
            color: #062E25;
        }

        .trivia-option.correct .opt-icon {
            color: #2ECC71;
        }

        .trivia-option.incorrect {
            border-color: #E74C3C !important;
            background: rgba(231, 76, 60, 0.22) !important;
            color: #FDEDEC !important;
            opacity: 0.85;
        }

        .trivia-option.incorrect .opt-letter {
            background: #E74C3C;
            border-color: #E74C3C;
            color: #FFFFFF;
        }

        .trivia-option.incorrect .opt-icon {
            color: #E74C3C;
        }

        .trivia-result {
            text-align: center;
            margin-top: 14px;
            padding: 8px 14px;
            border-radius: 8px;
            font-family: var(--font-sans);
            font-size: 0.82rem;
            display: none;
            line-height: 1.4;
        }

        .trivia-result.correct {
            background: rgba(46, 204, 113, 0.15);
            border: 1px solid rgba(46, 204, 113, 0.4);
            color: #A3E4D7;
        }

        .trivia-result.incorrect {
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.4);
            color: #FADBD8;
        }

        .trivia-nav {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }

        .trivia-nav-btn {
            padding: 8px 20px;
            border: 1px solid var(--dorado);
            border-radius: 20px;
            background: transparent;
            color: var(--dorado);
            font-family: var(--font-sans);
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .trivia-nav-btn:hover {
            background: var(--dorado);
            color: var(--verde-oscuro);
        }

        .trivia-nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .trivia-counter {
            text-align: center;
            font-family: var(--font-sans);
            font-size: 0.7rem;
            color: var(--dorado-claro);
            opacity: 0.6;
            margin-bottom: 10px;
        }

        /* ============================================================
           TARJETA 8 — PLAYLIST
           ============================================================ */
        .playlist-form {
            margin-top: 15px;
        }

        .playlist-input-group {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .playlist-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid rgba(200, 162, 74, 0.3);
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.2);
            color: var(--crema);
            font-family: var(--font-sans);
            font-size: 0.8rem;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .playlist-input:focus {
            border-color: var(--dorado);
        }

        .playlist-input::placeholder {
            color: rgba(255, 248, 236, 0.3);
        }

        .playlist-add-btn {
            padding: 12px 16px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .playlist-add-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 3px 15px rgba(200, 162, 74, 0.4);
        }

        .playlist-songs {
            margin-top: 15px;
            max-height: 120px;
            overflow-y: auto;
        }

        .playlist-song {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(200, 162, 74, 0.1);
            font-family: var(--font-sans);
            font-size: 0.8rem;
        }

        .playlist-song i {
            color: var(--dorado);
            font-size: 0.7rem;
        }

        /* ============================================================
           TARJETA: PRESENTE & LLUVIA DE SOBRES
           ============================================================ */
        .gift-envelope-box {
            position: relative;
            background: linear-gradient(145deg, rgba(0, 107, 79, 0.28), rgba(6, 46, 37, 0.75));
            border: 1.5px solid rgba(200, 162, 74, 0.45);
            border-radius: 20px;
            padding: 22px 18px;
            margin: 15px 0 12px 0;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), inset 0 0 25px rgba(200, 162, 74, 0.12);
        }

        .gift-icon-bubble {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.55rem;
            margin: 0 auto 12px auto;
            box-shadow: 0 6px 20px rgba(200, 162, 74, 0.45);
            animation: giftFloat 3s infinite alternate ease-in-out;
        }

        @keyframes giftFloat {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(-4px) scale(1.05); box-shadow: 0 10px 25px rgba(200, 162, 74, 0.6); }
        }

        .gift-reception-note {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(200, 162, 74, 0.12);
            border: 1px dashed var(--dorado);
            border-radius: 14px;
            padding: 12px 14px;
            margin-top: 14px;
            text-align: left;
            font-size: 0.8rem;
            line-height: 1.45;
            color: var(--dorado-claro);
        }

        .gift-reception-note i {
            font-size: 1.35rem;
            color: var(--dorado);
            flex-shrink: 0;
        }

        /* ============================================================
           TARJETA 10 — RSVP (CONFIRMA TU ASISTENCIA)
           ============================================================ */
        .rsvp-form-container {
            width: 100%;
        }

        .rsvp-input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid rgba(200, 162, 74, 0.3);
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.2);
            color: var(--crema);
            font-family: var(--font-sans);
            font-size: 0.85rem;
            outline: none;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .rsvp-input:focus {
            border-color: var(--dorado);
            box-shadow: 0 0 15px rgba(200, 162, 74, 0.15);
        }

        .rsvp-input::placeholder {
            color: rgba(255, 248, 236, 0.35);
        }

        .rsvp-label {
            font-family: var(--font-sans);
            font-size: 0.75rem;
            color: var(--dorado-claro);
            letter-spacing: 2px;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 10px;
        }

        .rsvp-attendance-btns {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .rsvp-attend-btn {
            flex: 1;
            padding: 12px 10px;
            border: 2px solid rgba(200, 162, 74, 0.3);
            border-radius: 12px;
            background: rgba(200, 162, 74, 0.05);
            color: var(--crema);
            font-family: var(--font-sans);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .rsvp-attend-btn:hover {
            border-color: var(--dorado);
            background: rgba(200, 162, 74, 0.15);
        }

        .rsvp-attend-btn.selected {
            border-color: var(--dorado);
            background: linear-gradient(135deg, rgba(200, 162, 74, 0.2), rgba(231, 212, 154, 0.15));
            color: var(--dorado);
            box-shadow: 0 0 20px rgba(200, 162, 74, 0.2);
        }

        .rsvp-attend-btn i {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        /* Acompañantes */
        .rsvp-companions-section {
            display: none;
            margin-bottom: 15px;
        }

        .rsvp-companions-section.visible {
            display: block;
            animation: fadeInUp 0.4s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .rsvp-companion-entry {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
            animation: fadeInUp 0.3s ease;
        }

        .rsvp-companion-entry input {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid rgba(200, 162, 74, 0.25);
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.15);
            color: var(--crema);
            font-family: var(--font-sans);
            font-size: 0.8rem;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .rsvp-companion-entry input:focus {
            border-color: var(--dorado);
        }

        .rsvp-companion-entry input::placeholder {
            color: rgba(255, 248, 236, 0.3);
        }

        .rsvp-remove-companion {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(200, 162, 74, 0.2);
            background: rgba(139, 69, 19, 0.15);
            color: var(--dorado-claro);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .rsvp-remove-companion:hover {
            background: rgba(139, 69, 19, 0.35);
            border-color: #8B4513;
        }

        .rsvp-add-companion-btn {
            width: 100%;
            padding: 10px;
            border: 1px dashed rgba(200, 162, 74, 0.3);
            border-radius: 10px;
            background: transparent;
            color: var(--dorado-claro);
            font-family: var(--font-sans);
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .rsvp-add-companion-btn:hover {
            border-color: var(--dorado);
            background: rgba(200, 162, 74, 0.05);
            color: var(--dorado);
        }

        .rsvp-add-companion-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .rsvp-submit-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            font-family: var(--font-sans);
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .rsvp-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(200, 162, 74, 0.4);
        }

        .rsvp-submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .rsvp-submit-btn .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--verde-oscuro);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Mensaje de error */
        .rsvp-error {
            display: none;
            padding: 10px 14px;
            border: 1px solid rgba(139, 69, 19, 0.5);
            border-radius: 8px;
            background: rgba(139, 69, 19, 0.15);
            color: var(--dorado-claro);
            font-family: var(--font-sans);
            font-size: 0.8rem;
            text-align: center;
            margin-bottom: 12px;
            animation: fadeInUp 0.3s ease;
        }

        .rsvp-error.visible {
            display: block;
        }

        /* Mensaje de éxito */
        .rsvp-success {
            display: none;
            text-align: center;
            padding: 20px 0;
        }

        .rsvp-success.visible {
            display: block;
            animation: fadeInUp 0.5s ease;
        }

        .rsvp-success-icon {
            font-size: 3rem;
            color: var(--dorado);
            margin-bottom: 15px;
            animation: successPulse 1.5s ease infinite;
        }

        @keyframes successPulse {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 5px rgba(200, 162, 74, 0.3)); }
            50% { transform: scale(1.1); filter: drop-shadow(0 0 20px rgba(200, 162, 74, 0.6)); }
        }

        .rsvp-success h3 {
            font-family: var(--font-script);
            font-size: 1.8rem;
            color: var(--dorado);
            margin-bottom: 10px;
        }

        .rsvp-success p {
            font-family: var(--font-sans);
            font-size: 0.85rem;
            color: var(--crema);
            opacity: 0.9;
            line-height: 1.6;
        }

        /* ============================================================
           PASE DE ENTRADA VIP CON CÓDIGO QR
           ============================================================ */
        .vip-ticket {
            background: linear-gradient(145deg, rgba(6, 46, 37, 0.98), rgba(0, 107, 79, 0.92));
            border: 2px solid var(--dorado);
            border-radius: 18px;
            padding: 22px 18px;
            text-align: center;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.55), inset 0 0 25px rgba(200, 162, 74, 0.2);
            position: relative;
            overflow: hidden;
            margin-top: 5px;
            animation: ticketZoomIn 0.6s cubic-bezier(0.25, 1, 0.5, 1);
        }

        @keyframes ticketZoomIn {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .ticket-badge {
            display: inline-block;
            padding: 5px 16px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            font-family: var(--font-sans);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .ticket-tiara {
            font-size: 1.6rem;
            margin-bottom: 2px;
        }

        .ticket-title {
            font-family: var(--font-serif);
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dorado);
            letter-spacing: 2px;
        }

        .ticket-subtitle {
            font-family: var(--font-script);
            font-size: 1.8rem;
            color: var(--crema);
            margin-top: -3px;
        }

        .ticket-qr-container {
            margin: 14px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        #ticketQrWrapper {
            padding: 10px;
            background: #FFFDF5;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4), 0 0 16px rgba(200, 162, 74, 0.4);
            display: inline-block;
            min-width: 170px;
            min-height: 170px;
        }

        #ticketQrWrapper img, #ticketQrWrapper canvas {
            display: block;
            margin: 0 auto;
            border-radius: 6px;
        }

        .ticket-qr-code-txt {
            font-family: 'Courier New', monospace;
            font-size: 0.76rem;
            font-weight: bold;
            letter-spacing: 1.5px;
            color: var(--dorado-claro);
            margin-top: 8px;
        }

        .ticket-info {
            background: rgba(0, 0, 0, 0.28);
            border: 1px solid rgba(200, 162, 74, 0.3);
            border-radius: 12px;
            padding: 12px 14px;
            margin: 14px 0;
            text-align: left;
        }

        .t-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 5px 0;
            border-bottom: 1px solid rgba(200, 162, 74, 0.12);
            font-size: 0.78rem;
        }

        .t-row:last-child {
            border-bottom: none;
        }

        .t-lbl {
            color: var(--dorado-claro);
            font-family: var(--font-sans);
            font-weight: 500;
            flex-shrink: 0;
            margin-right: 8px;
        }

        .t-data {
            color: var(--crema);
            font-family: var(--font-sans);
            text-align: right;
            word-break: break-word;
        }

        .t-passes {
            color: var(--dorado);
            font-weight: 700;
        }

        /* Sección de Boletos para la Rifa de Regalos */
        .ticket-raffle-section {
            background: linear-gradient(135deg, rgba(200, 162, 74, 0.12), rgba(6, 46, 37, 0.65));
            border: 1.5px dashed var(--dorado);
            border-radius: 12px;
            padding: 12px 14px;
            margin: 12px 0 14px 0;
            text-align: center;
        }

        .ticket-raffle-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--dorado);
            font-family: var(--font-sans);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .ticket-raffle-badges {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .raffle-ticket-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(6, 46, 37, 0.9);
            border: 1px solid rgba(200, 162, 74, 0.4);
            border-radius: 8px;
            padding: 8px 12px;
            font-family: var(--font-sans);
        }

        .raffle-ticket-person {
            font-size: 0.78rem;
            color: var(--crema);
            display: flex;
            align-items: center;
            gap: 6px;
            max-width: 60%;
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .raffle-ticket-person i {
            color: var(--dorado-claro);
            font-size: 0.75rem;
        }

        .raffle-ticket-number {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            padding: 4px 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: 800;
            font-size: 0.85rem;
            letter-spacing: 1px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .raffle-ticket-note {
            font-size: 0.7rem;
            color: var(--dorado-claro);
            opacity: 0.9;
            margin-top: 8px;
            line-height: 1.35;
            font-style: italic;
        }

        .ticket-instruction {
            font-family: var(--font-sans);
            font-size: 0.72rem;
            color: var(--dorado-claro);
            opacity: 0.85;
            margin: 10px 0;
            line-height: 1.4;
        }

        .btn-ticket-download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 18px;
            border: none;
            border-radius: 30px;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            font-family: var(--font-sans);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(200, 162, 74, 0.35);
            transition: all 0.3s ease;
        }

        .btn-ticket-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(200, 162, 74, 0.55);
        }

        /* Destello dorado de confirmación */
        .gold-flash {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(200, 162, 74, 0.3), transparent 70%);
            pointer-events: none;
            z-index: 1000;
            opacity: 0;
            animation: goldFlash 1.2s ease forwards;
        }

        @keyframes goldFlash {
            0% { opacity: 0; }
            30% { opacity: 1; }
            100% { opacity: 0; }
        }

        /* ============================================================
           TARJETA 10 — GRACIAS
           ============================================================ */
        .thanks-heart {
            font-size: 2.5rem;
            color: var(--dorado);
            text-align: center;
            margin-bottom: 10px;
            animation: heartBeat 2s ease-in-out infinite;
        }

        /* Foto final de Angie en Tarjeta 10 */
        .angie-photo-wrapper {
            position: relative;
            display: inline-block;
            margin: 6px auto 10px auto;
            border-radius: 20px;
            padding: 4px;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro), #8B6914, var(--dorado));
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.65), 0 0 25px rgba(200, 162, 74, 0.45);
            transition: all 0.35s ease;
        }

        .angie-photo-wrapper:hover {
            transform: scale(1.025);
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.75), 0 0 35px rgba(200, 162, 74, 0.65);
        }

        .angie-photo-frame {
            position: relative;
            width: 200px;
            max-width: 62vw;
            aspect-ratio: 4 / 5;
            border-radius: 16px;
            overflow: hidden;
            background: #062E25;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .angie-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 20%;
            display: block;
            transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .angie-photo-wrapper:hover .angie-photo-img {
            transform: scale(1.06);
        }

        .angie-photo-sheen {
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.28), transparent);
            transform: skewX(-25deg);
            pointer-events: none;
            animation: photoSheen 5s infinite;
        }

        @keyframes photoSheen {
            0%, 70% { left: -100%; }
            100% { left: 200%; }
        }

        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            15% { transform: scale(1.15); }
            30% { transform: scale(1); }
            45% { transform: scale(1.1); }
            60% { transform: scale(1); }
        }

        /* ============================================================
           AUDIO TOGGLE & MUSICA DE FONDO
           ============================================================ */
        .audio-toggle {
            position: fixed;
            top: 18px;
            right: 18px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 1.5px solid var(--dorado);
            background: rgba(6, 46, 37, 0.88);
            color: var(--dorado);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 200;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .audio-toggle:not(.playing) {
            animation: audioInvite 2.6s infinite ease-in-out;
        }

        @keyframes audioInvite {
            0%, 100% {
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 18px rgba(200, 162, 74, 0.75), 0 0 0 4px rgba(200, 162, 74, 0.25);
                transform: scale(1.06);
            }
        }

        .audio-toggle:hover {
            background: var(--dorado);
            color: var(--verde-oscuro);
            transform: scale(1.08);
            box-shadow: 0 0 20px rgba(200, 162, 74, 0.6);
        }

        .audio-toggle.playing {
            border-color: var(--dorado-claro);
            background: rgba(6, 46, 37, 0.95);
            color: var(--dorado-claro);
            animation: musicPulse 2.4s infinite;
        }

        @keyframes musicPulse {
            0% { box-shadow: 0 0 0 0 rgba(200, 162, 74, 0.6), 0 0 15px rgba(200, 162, 74, 0.4); }
            70% { box-shadow: 0 0 0 12px rgba(200, 162, 74, 0), 0 0 22px rgba(200, 162, 74, 0.2); }
            100% { box-shadow: 0 0 0 0 rgba(200, 162, 74, 0), 0 0 15px rgba(200, 162, 74, 0.4); }
        }

        .audio-toggle.playing i {
            animation: spinDisc 4s linear infinite;
        }

        @keyframes spinDisc {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Tooltip informativo de música */
        .audio-tooltip {
            position: fixed;
            top: 23px;
            right: 70px;
            background: rgba(6, 46, 37, 0.94);
            border: 1px solid var(--dorado);
            color: var(--dorado-claro);
            font-family: var(--font-sans);
            font-size: 0.72rem;
            padding: 6px 14px;
            border-radius: 20px;
            pointer-events: none;
            opacity: 0;
            transform: translateX(10px);
            transition: all 0.4s ease;
            white-space: nowrap;
            max-width: calc(100vw - 90px);
            text-overflow: ellipsis;
            overflow: hidden;
            z-index: 199;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.4);
        }

        .audio-tooltip.visible {
            opacity: 1;
            transform: translateX(0);
        }

        /* Botón discreto de acceso a Administración */
        .admin-link-btn {
            position: fixed;
            top: 18px;
            left: 18px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(6, 46, 37, 0.7);
            border: 1px solid rgba(200, 162, 74, 0.35);
            color: var(--dorado);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            text-decoration: none;
            opacity: 0.35;
            transition: all 0.3s ease;
            z-index: 200;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .admin-link-btn:hover {
            opacity: 1;
            border-color: var(--dorado);
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(200, 162, 74, 0.5);
            color: #FFF;
        }

        /* Acompañantes Header */
        .rsvp-companions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px 12px;
            margin-bottom: 8px;
            width: 100%;
        }

        #rsvp-passes-badge {
            color: var(--dorado);
            font-size: 0.74rem;
            font-weight: 600;
            background: rgba(200, 162, 74, 0.15);
            border: 1px solid rgba(200, 162, 74, 0.35);
            padding: 3px 10px;
            border-radius: 12px;
            white-space: nowrap;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 480px) {
            .card {
                padding: 28px 18px 105px 18px;
            }

            .nav-arrows {
                bottom: 12px;
                gap: 12px;
            }

            .nav-arrow {
                width: 38px;
                height: 38px;
                font-size: 14px;
            }

            .nav-dots {
                bottom: 54px;
                gap: 5px;
            }

            .audio-toggle {
                top: 14px;
                right: 14px;
                width: 44px;
                height: 44px;
                z-index: 300;
            }

            .audio-tooltip {
                top: 18px;
                right: 64px;
                max-width: calc(100vw - 80px);
                font-size: 0.68rem;
                padding: 5px 11px;
                z-index: 299;
            }

            .admin-link-btn {
                top: 14px;
                left: 14px;
                width: 36px;
                height: 36px;
                z-index: 300;
            }
        }

        @media (max-width: 380px) {
            .card {
                padding: 24px 14px 100px 14px;
            }

            .cover-name {
                font-size: 2.4rem;
            }

            .cover-xv {
                font-size: 3rem;
            }

            .card-title-script {
                font-size: 1.8rem;
            }

            .countdown-number {
                font-size: 1.5rem;
            }
        }

        @media (min-width: 768px) {
            .card {
                max-width: 480px;
                padding: 45px 35px;
            }
        }

        /* ============================================================
           ANIMACIÓN DE ENTRADA DE TARJETAS
           ============================================================ */
        .card.animate-in .card-icon,
        .card.animate-in .card-title-script,
        .card.animate-in .card-title-serif,
        .card.animate-in .gold-divider,
        .card.animate-in .card-text,
        .card.animate-in .info-block,
        .card.animate-in .timeline-item {
            opacity: 0;
            transform: translateY(20px);
            animation: cardContentIn 0.6s ease forwards;
        }

        .card.animate-in .card-icon { animation-delay: 0.1s; }
        .card.animate-in .card-title-script { animation-delay: 0.2s; }
        .card.animate-in .card-title-serif { animation-delay: 0.3s; }
        .card.animate-in .gold-divider { animation-delay: 0.35s; }
        .card.animate-in .card-text { animation-delay: 0.4s; }
        .card.animate-in .info-block:nth-child(1) { animation-delay: 0.45s; }
        .card.animate-in .info-block:nth-child(2) { animation-delay: 0.55s; }

        @keyframes cardContentIn {
            to { opacity: 1; transform: translateY(0); }
        }

        /* ============================================================
           CONFETTI / SPARKLE BURST
           ============================================================ */
        .sparkle-burst {
            position: fixed;
            pointer-events: none;
            z-index: 999;
        }

        .sparkle-particle {
            position: absolute;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            animation: sparkleBurst 1s ease forwards;
        }

        @keyframes sparkleBurst {
            0% { opacity: 1; transform: translate(0, 0) scale(1); }
            100% { opacity: 0; transform: translate(var(--tx), var(--ty)) scale(0); }
        }

        /* ============================================================
           MARIPOSAS DORADAS VOLANDO (GOLDEN BUTTERFLIES)
           ============================================================ */
        .butterflies-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 15;
            overflow: hidden;
        }

        .gold-butterfly {
            position: absolute;
            top: 0;
            left: 0;
            width: var(--b-size, 32px);
            height: var(--b-size, 32px);
            pointer-events: none;
            transform-style: preserve-3d;
            perspective: 600px;
            filter: drop-shadow(0 0 6px rgba(255, 215, 0, 0.75)) drop-shadow(0 0 12px rgba(200, 162, 74, 0.5));
            will-change: transform;
            user-select: none;
            opacity: 0.88;
        }

        .butterfly-inner {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform-style: preserve-3d;
        }

        .butterfly-wing {
            width: 48%;
            height: 100%;
            transform-style: preserve-3d;
        }

        .butterfly-wing.left {
            transform-origin: right center;
            animation: flapLeft var(--flap-speed, 0.22s) ease-in-out infinite alternate;
        }

        .butterfly-wing.right {
            transform-origin: left center;
            transform: scaleX(-1);
            animation: flapRight var(--flap-speed, 0.22s) ease-in-out infinite alternate;
        }

        @keyframes flapLeft {
            0% { transform: rotateY(0deg) rotateZ(0deg); }
            100% { transform: rotateY(68deg) rotateZ(5deg); }
        }

        @keyframes flapRight {
            0% { transform: scaleX(-1) rotateY(0deg) rotateZ(0deg); }
            100% { transform: scaleX(-1) rotateY(68deg) rotateZ(5deg); }
        }

        .butterfly-center {
            width: 8%;
            height: 90%;
            position: relative;
            z-index: 2;
        }

        .butterfly-sparkle {
            position: absolute;
            width: 5px;
            height: 5px;
            background: #FFFDF5;
            border-radius: 50%;
            box-shadow: 0 0 8px #FFE58F, 0 0 14px #C8A24A;
            pointer-events: none;
            z-index: 24;
            animation: bSparkleFade 1.1s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }

        @keyframes bSparkleFade {
            0% { opacity: 0.95; transform: scale(1); }
            100% { opacity: 0; transform: scale(0.2) translateY(18px); }
        }
        /* ============================================================
           TARJETA 12: GALERÍA DE FOTOS EN VIVO (LIVE PARTY ALBUM)
           ============================================================ */
        .live-photos-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            margin-top: 4px;
        }

        .btn-camera-upload {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            max-width: 320px;
            padding: 13px 20px;
            background: linear-gradient(135deg, #FFD700 0%, #C8A24A 60%, #8B6914 100%);
            color: #041B16;
            font-family: var(--font-serif);
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: none;
            border-radius: 30px;
            box-shadow: 0 6px 20px rgba(200, 162, 74, 0.45), inset 0 1px 2px rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
        }

        .btn-camera-upload:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.6);
        }

        .btn-camera-upload:active {
            transform: translateY(1px);
        }

        .live-tv-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.76rem;
            color: var(--dorado-claro);
            text-decoration: none;
            padding: 4px 12px;
            background: rgba(200, 162, 74, 0.12);
            border: 1px solid rgba(200, 162, 74, 0.3);
            border-radius: 20px;
            transition: all 0.3s;
        }

        .live-tv-link:hover {
            background: rgba(200, 162, 74, 0.25);
            color: #FFF;
        }

        .live-gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            width: 100%;
            max-height: 250px;
            overflow-y: auto;
            padding: 6px;
            border-radius: 14px;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(200, 162, 74, 0.2);
            scrollbar-width: thin;
            scrollbar-color: var(--dorado) transparent;
        }

        .live-gallery-item {
            position: relative;
            aspect-ratio: 1 / 1;
            border-radius: 10px;
            overflow: hidden;
            border: 1.5px solid rgba(200, 162, 74, 0.35);
            background: #000;
            cursor: pointer;
            transition: transform 0.2s, border-color 0.2s;
        }

        .live-gallery-item:hover {
            transform: scale(1.04);
            border-color: var(--dorado-brillante);
            z-index: 2;
        }

        .live-gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .live-item-badge {
            position: absolute;
            bottom: 3px;
            right: 4px;
            background: rgba(0, 0, 0, 0.65);
            border-radius: 10px;
            padding: 1px 6px;
            font-size: 0.62rem;
            color: #FFD700;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .live-gallery-empty {
            grid-column: 1 / -1;
            padding: 30px 10px;
            text-align: center;
            color: var(--dorado-claro);
            opacity: 0.8;
            font-size: 0.84rem;
        }

        .live-gallery-empty i {
            font-size: 2rem;
            color: var(--dorado);
            display: block;
            margin-bottom: 8px;
            opacity: 0.7;
        }

        /* Modal Subir Foto */
        .photo-upload-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            padding: 16px;
            box-sizing: border-box;
            animation: fadeIn 0.3s ease;
        }

        .photo-upload-box {
            background: linear-gradient(145deg, #062E25, #004D38);
            border: 2px solid var(--dorado);
            border-radius: 20px;
            max-width: 420px;
            width: 100%;
            padding: 22px 18px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.8), inset 0 0 30px rgba(200, 162, 74, 0.15);
            text-align: center;
            position: relative;
            animation: popIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-height: 94vh;
            overflow-y: auto;
        }

        .photo-preview-wrap {
            width: 100%;
            max-height: 220px;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            border: 1px solid rgba(200, 162, 74, 0.4);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-preview-wrap img {
            max-width: 100%;
            max-height: 220px;
            object-fit: contain;
        }

        .upload-input {
            width: 100%;
            box-sizing: border-box;
            background: rgba(0, 0, 0, 0.35);
            border: 1.5px solid rgba(200, 162, 74, 0.35);
            border-radius: 10px;
            padding: 10px 14px;
            color: #FFF;
            font-family: var(--font-sans);
            font-size: 0.88rem;
            margin-bottom: 10px;
            outline: none;
        }

        .upload-input:focus {
            border-color: var(--dorado);
            box-shadow: 0 0 10px rgba(200, 162, 74, 0.3);
        }

        /* Lightbox Visor de Foto */
        .photo-lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.92);
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100000;
            padding: 16px;
            box-sizing: border-box;
            animation: fadeIn 0.25s ease;
        }

        .lightbox-close {
            position: absolute;
            top: 18px;
            right: 20px;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: #FFF;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .lightbox-close:hover {
            background: var(--dorado);
            color: #062E25;
            transform: scale(1.1);
        }

        .lightbox-img {
            max-width: 90vw;
            max-height: 65vh;
            object-fit: contain;
            border-radius: 14px;
            border: 2px solid var(--dorado);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.9);
        }

        .lightbox-details {
            margin-top: 14px;
            text-align: center;
            max-width: 480px;
            width: 100%;
        }

        .lightbox-author {
            font-family: var(--font-serif);
            font-size: 1.15rem;
            color: var(--dorado-brillante);
            font-weight: 700;
        }

        .lightbox-msg {
            font-size: 0.9rem;
            color: #EEE;
            font-style: italic;
            margin: 4px 0 10px 0;
        }

        .btn-like-photo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(220, 38, 38, 0.25);
            border: 1.5px solid #ef4444;
            color: #fca5a5;
            padding: 8px 18px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-like-photo:hover {
            background: #ef4444;
            color: #FFF;
            transform: scale(1.05);
        }

        .btn-like-photo.liked {
            background: #ef4444;
            color: #FFF;
        }

        .btn-download-lightbox {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(200, 162, 74, 0.2);
            border: 1.5px solid var(--dorado);
            color: var(--dorado-claro);
            padding: 8px 18px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.88rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-download-lightbox:hover {
            background: var(--dorado);
            color: #062E25;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <!-- Mariposas doradas volando mágicamente -->
    <div class="butterflies-container" id="butterfliesContainer">
        <svg style="width:0;height:0;position:absolute;visibility:hidden;" aria-hidden="true">
            <defs>
                <linearGradient id="globalGoldWing" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FFFDF5" />
                    <stop offset="25%" stop-color="#FFE680" />
                    <stop offset="60%" stop-color="#FFC107" />
                    <stop offset="85%" stop-color="#D4AF37" />
                    <stop offset="100%" stop-color="#996515" />
                </linearGradient>
            </defs>
        </svg>
    </div>

    <!-- Partículas doradas de fondo -->
    <div class="particles-container" id="particles"></div>

    <!-- Contenedor principal del deck -->
    <div class="deck-container" id="deckContainer">

        <!-- ============================================
             TARJETA 1: PORTADA
             ============================================ -->
        <div class="card-slide active" data-slide="0">
            <div class="card cover-card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="cover-tiara" style="margin-bottom: -8px; z-index: 6;">
                    <svg viewBox="0 0 140 85" class="tiara-svg" style="width: 88px;" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="tiaraGold" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#FFFDF5" />
                                <stop offset="25%" stop-color="#F7E7B4" />
                                <stop offset="50%" stop-color="#C8A24A" />
                                <stop offset="75%" stop-color="#E2CA7F" />
                                <stop offset="100%" stop-color="#8B6914" />
                            </linearGradient>
                            <linearGradient id="tiaraDiamond" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#FFFFFF" />
                                <stop offset="50%" stop-color="#F0F8FF" />
                                <stop offset="100%" stop-color="#D4E6F1" />
                            </linearGradient>
                        </defs>

                        <!-- Banda arqueada base -->
                        <path d="M 20 76 Q 70 82 120 76" stroke="url(#tiaraGold)" stroke-width="2.5" fill="none" stroke-linecap="round" />
                        <path d="M 24 72 Q 70 78 116 72" stroke="url(#tiaraGold)" stroke-width="1.2" fill="none" stroke-linecap="round" opacity="0.8" />

                        <!-- Perlas / Gemas en la banda base -->
                        <circle cx="30" cy="74.5" r="1.8" fill="url(#tiaraDiamond)" />
                        <circle cx="43" cy="76" r="1.8" fill="url(#tiaraDiamond)" />
                        <circle cx="56" cy="77" r="1.8" fill="url(#tiaraDiamond)" />
                        <circle cx="70" cy="77.5" r="2.2" fill="url(#tiaraDiamond)" />
                        <circle cx="84" cy="77" r="1.8" fill="url(#tiaraDiamond)" />
                        <circle cx="97" cy="76" r="1.8" fill="url(#tiaraDiamond)" />
                        <circle cx="110" cy="74.5" r="1.8" fill="url(#tiaraDiamond)" />

                        <!-- Arco central majestuoso -->
                        <path d="M 70 77 C 62 60 52 40 70 12 C 88 40 78 60 70 77 Z" fill="none" stroke="url(#tiaraGold)" stroke-width="2" />
                        <path d="M 70 77 C 65 62 60 48 70 28 C 80 48 75 62 70 77 Z" fill="none" stroke="url(#tiaraGold)" stroke-width="1.2" opacity="0.75" />

                        <!-- Arcos laterales -->
                        <path d="M 50 75 C 44 56 36 42 46 25 C 56 42 62 58 54 75" fill="none" stroke="url(#tiaraGold)" stroke-width="1.8" />
                        <path d="M 90 75 C 96 56 104 42 94 25 C 84 42 78 58 86 75" fill="none" stroke="url(#tiaraGold)" stroke-width="1.8" />

                        <!-- Filigranas interiores -->
                        <path d="M 70 65 C 64 54 60 46 70 38 C 80 46 76 54 70 65" fill="none" stroke="url(#tiaraGold)" stroke-width="1.2" />
                        <path d="M 46 68 C 38 58 35 48 42 38 C 48 48 48 58 46 68" fill="none" stroke="url(#tiaraGold)" stroke-width="1.2" />
                        <path d="M 94 68 C 102 58 105 48 98 38 C 92 48 92 58 94 68" fill="none" stroke="url(#tiaraGold)" stroke-width="1.2" />

                        <!-- Espirales exteriores -->
                        <path d="M 28 73 C 20 62 14 52 24 40 C 32 50 36 62 30 73" fill="none" stroke="url(#tiaraGold)" stroke-width="1.5" />
                        <path d="M 112 73 C 120 62 126 52 116 40 C 108 50 104 62 110 73" fill="none" stroke="url(#tiaraGold)" stroke-width="1.5" />

                        <!-- Joyas centrales en gota -->
                        <path d="M 70 36 C 66 42 66 48 70 52 C 74 48 74 42 70 36 Z" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.8" />
                        <path d="M 46 44 C 43 48 43 53 46 56 C 49 53 49 48 46 44 Z" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.7" />
                        <path d="M 94 44 C 91 48 91 53 94 56 C 97 53 97 48 94 44 Z" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.7" />

                        <!-- Cristales en las cúspides y estrella brillante -->
                        <circle cx="70" cy="11" r="3.5" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="1" />
                        <path d="M 70 3 L 71.5 9 L 77.5 10.5 L 71.5 12 L 70 18 L 68.5 12 L 62.5 10.5 L 68.5 9 Z" fill="#FFFDF5" opacity="0.95" />

                        <circle cx="46" cy="24" r="2.8" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.8" />
                        <circle cx="94" cy="24" r="2.8" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.8" />
                        <circle cx="24" cy="39" r="2.2" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.8" />
                        <circle cx="116" cy="39" r="2.2" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.8" />

                        <!-- Destellos finos en los picos laterales -->
                        <path d="M 46 19 L 47 23 L 51 24 L 47 25 L 46 29 L 45 25 L 41 24 L 45 23 Z" fill="#FFFFFF" opacity="0.85" />
                        <path d="M 94 19 L 95 23 L 99 24 L 95 25 L 94 29 L 93 25 L 89 24 L 93 23 Z" fill="#FFFFFF" opacity="0.85" />
                    </svg>
                </div>

                <!-- Marco elegante real con la foto de Angie -->
                <div class="cover-photo-container">
                    <div class="cover-photo-wrapper">
                        <div class="cover-photo-frame">
                            <img src="angie-portada.jpg" onerror="if(!this.dataset.retry){this.dataset.retry=1;this.src='public/angie-portada.jpg';}" alt="Angie Karolina - Mis XV Años" class="cover-photo-img" loading="eager">
                            <div class="cover-photo-sheen"></div>
                        </div>
                    </div>
                </div>

                <div class="cover-presents" style="margin-top: 4px;">TE INVITO A CELEBRAR</div>
                <div class="card-title-script cover-name" style="font-size: 2.7rem; margin-bottom: 4px;">Angie Karolina</div>
                <div class="gold-divider" style="margin: 6px auto 10px auto;"></div>
                <div class="cover-xv" style="font-size: 2.6rem; letter-spacing: 6px; margin-bottom: 8px;">XV AÑOS</div>
                <div class="cover-date" style="letter-spacing: 2.5px;">3 · OCTUBRE · 2026</div>
                <div class="cover-swipe" style="margin-top: 14px;">
                    <i class="fas fa-chevron-down"></i><br>
                    Desliza para ver más
                </div>
            </div>
        </div>

        <!-- ============================================
             TARJETA 2: ÉRASE UNA VEZ (EL CUENTO DE MI VIDA)
             ============================================ -->
        <div class="card-slide hidden" data-slide="1">
            <div class="card fairytale-card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon" style="margin-bottom: 2px;">
                    <i class="fas fa-book-open story-magic-spark"></i>
                </div>
                <div class="card-title-script" style="font-size: 2.3rem; margin-bottom: 0px;">Érase una vez...</div>
                <div class="card-title-serif" style="font-size: 0.72rem; letter-spacing: 2px; margin-bottom: 8px;">EL COMIENZO DE MI HISTORIA</div>
                <div class="gold-divider" style="margin-bottom: 12px;"></div>

                <div class="fairytale-story-box">
                    <p class="story-paragraph">
                        <span class="story-drop-cap">D</span>icen que el destino está entrelazado en cada paso que damos, y que solo hace falta tener un corazón valiente para descubrir la magia de nuestra propia historia...
                    </p>
                    <p class="story-paragraph">
                        Hace quince años comenzó el viaje más hermoso: la llegada de una pequeña niña que llenó de luz, risas y ternura la vida de sus amados padres, <strong>Edwin y Lady</strong>.
                    </p>
                    <p class="story-paragraph">
                        Crecí rodeada de amor, soñando despierta y aprendiendo que la mayor aventura es ser auténtica y libre.
                    </p>
                    <div class="story-highlight">
                        ✨ Hoy, con gratitud y emoción, dejo atrás mi niñez para abrazar con valentía mis <strong>XV Años</strong> y desplegar mis alas hacia el futuro.
                    </div>
                    <p class="story-conclusion">
                        Y como en todo gran cuento de hadas, este sueño no estaría completo sin ti... ¡Acompáñame a celebrar!
                    </p>
                </div>
            </div>
        </div>

        <!-- ============================================
             TARJETA 3: CUENTA REGRESIVA
             ============================================ -->
        <div class="card-slide hidden" data-slide="2">
            <div class="card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="card-title-script">Cuenta Regresiva</div>
                <div class="card-title-serif">FALTAN</div>
                <div class="gold-divider"></div>

                <div class="countdown-grid" id="countdown">
                    <div class="countdown-item">
                        <div class="countdown-number" id="cd-days">--</div>
                        <div class="countdown-label">Días</div>
                    </div>
                    <div class="countdown-item">
                        <div class="countdown-number" id="cd-hours">--</div>
                        <div class="countdown-label">Horas</div>
                    </div>
                    <div class="countdown-item">
                        <div class="countdown-number" id="cd-minutes">--</div>
                        <div class="countdown-label">Minutos</div>
                    </div>
                    <div class="countdown-item">
                        <div class="countdown-number" id="cd-seconds">--</div>
                        <div class="countdown-label">Segundos</div>
                    </div>
                </div>

                <p class="card-text">para la noche más especial ✨</p>

                <div class="calendar-btn-container">
                    <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=Mis+XV+A%C3%B1os+-+Angie+Karolina&dates=20261004T000000Z%2F20261004T060000Z&details=%C2%A1Celebremos+juntos+los+XV+A%C3%B1os+de+Angie+Karolina!+Una+noche+m%C3%A1gica+llena+de+alegr%C3%ADa+y+momentos+inolvidables.&location=Colegio+de+M%C3%A9dicos+del+Estado+M%C3%A9rida%2C+Av.+Urdaneta%2C+M%C3%A9rida%2C+Venezuela" target="_blank" rel="noopener noreferrer" class="calendar-btn" id="btnGoogleCalendar">
                        <i class="fab fa-google"></i>
                        <span>Agendar en Google Calendar</span>
                        <i class="fas fa-bell"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- ============================================
             TARJETA 4: INVITACIÓN FORMAL
             ============================================ -->
        <div class="card-slide hidden" data-slide="3">
            <div class="card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div class="card-title-script">Invitación</div>
                <div class="gold-divider"></div>

                <p class="formal-text">
                    Con la bendición de Dios y el cariño de mi familia,
                    tengo el honor de invitarte a celebrar conmigo
                    esta fecha tan especial en mi vida.
                </p>

                <div class="gold-divider"></div>

                <p class="formal-parents">
                    Mis padres<br>
                    <strong style="color: var(--dorado); font-size: 0.95rem;">Edwin Johan Avendaño</strong> <span style="font-size: 0.8rem; opacity: 0.85;">(Mi papá)</span><br>
                    <strong style="color: var(--dorado); font-size: 0.95rem;">Lady Rivera</strong> <span style="font-size: 0.8rem; opacity: 0.85;">(Mi mamá)</span><br>
                    <span style="display: inline-block; margin-top: 5px;">te extienden esta cordial invitación</span>
                </p>

                <div class="gold-divider"></div>

                <p class="card-text">
                    Será una noche llena de alegría, baile y momentos
                    inolvidables que quiero compartir contigo.
                </p>
            </div>
        </div>

        <!-- ============================================
             TARJETA 5: LUGAR Y FECHA
             ============================================ -->
        <div class="card-slide hidden" data-slide="4">
            <div class="card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="card-title-script">Lugar y Fecha</div>
                <div class="gold-divider"></div>

                <div class="info-block">
                    <div class="info-label">📅 Gran Celebración de XV Años</div>
                    <div class="info-value">Sábado, 3 de Octubre de 2026</div>
                    <div style="font-size: 0.76rem; color: var(--dorado-claro); opacity: 0.9; margin-top: 3px; font-style: italic;">
                        (Cumpleaños de Angie: 23 de Septiembre 🎂)
                    </div>
                </div>

                <div class="info-block">
                    <div class="info-label">🕗 Hora</div>
                    <div class="info-value">8:00 PM</div>
                </div>

                <div class="info-block">
                    <div class="info-label">📍 Lugar de la Recepción</div>
                    <div class="info-value">Colegio de Médicos del Estado Mérida</div>
                    <div class="info-address">Av. Urdaneta, Mérida, Venezuela</div>
                </div>

                <div class="map-btn-container">
                    <a href="https://maps.google.com/?q=Colegio+de+Medicos+del+Estado+Merida+Av+Urdaneta+Merida+Venezuela" target="_blank" rel="noopener noreferrer" class="map-btn">
                        <i class="fas fa-location-arrow"></i>
                        <span>Abrir en Google Maps</span>
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- ============================================
             TARJETA 6: DRESS CODE
             ============================================ -->
        <div class="card-slide hidden" data-slide="5">
            <div class="card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-tshirt"></i>
                </div>
                <div class="card-title-script">Dress Code</div>
                <div class="card-title-serif">ELEGANTE</div>
                <div class="gold-divider"></div>

                <p class="card-text">
                    Te invitamos a vestirte elegante para esta noche especial.
                    Aquí tienes la paleta de colores sugerida:
                </p>

                <div class="dresscode-colors">
                    <div class="color-swatch" style="background: #C8A24A;" title="Dorado"></div>
                    <div class="color-swatch" style="background: #E7D49A;" title="Dorado Claro"></div>
                    <div class="color-swatch" style="background: #F5E6D3;" title="Champagne"></div>
                    <div class="color-swatch" style="background: #FFF8EC;" title="Crema"></div>
                    <div class="color-swatch" style="background: #FFFFFF;" title="Blanco"></div>
                    <div class="color-swatch" style="background: #1A1410;" title="Negro / Oscuro"></div>
                </div>

                <p class="dresscode-note">
                    ✨ Evita el color verde, por favor ✨
                </p>
            </div>
        </div>

        <!-- ============================================
             TARJETA 7: ITINERARIO
             ============================================ -->
        <div class="card-slide hidden" data-slide="6">
            <div class="card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-title-script">Itinerario</div>
                <div class="card-title-serif">PROGRAMA DE LA NOCHE</div>
                <div class="gold-divider"></div>

                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-time">8:00 PM</div>
                        <div class="timeline-event">Recepción de invitados</div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-time">8:30 PM</div>
                        <div class="timeline-event">Ceremonia de entrada</div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-time">9:00 PM</div>
                        <div class="timeline-event">Vals con mi padre</div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-time">9:30 PM</div>
                        <div class="timeline-event">Brindis y pasapalos</div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-time">10:30 PM</div>
                        <div class="timeline-event">¡A bailar toda la noche! 🎶</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================
             TARJETA 8: TRIVIA
             ============================================ -->
        <div class="card-slide hidden" data-slide="7">
            <div class="card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="card-title-script">¿Cuánto me conoces?</div>
                <div class="card-title-serif">TRIVIA</div>
                <div class="gold-divider"></div>

                <div id="trivia-container">
                    <div class="trivia-counter" id="trivia-counter">Pregunta 1 de 5</div>
                    <div class="trivia-question" id="trivia-question"></div>
                    <div class="trivia-options" id="trivia-options"></div>
                    <div class="trivia-result" id="trivia-result"></div>
                    <div class="trivia-nav" id="trivia-nav">
                        <button class="trivia-nav-btn" id="trivia-prev" disabled>
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                        <button class="trivia-nav-btn" id="trivia-next">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================
             TARJETA 9: PLAYLIST
             ============================================ -->
        <div class="card-slide hidden" data-slide="8">
            <div class="card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-music"></i>
                </div>
                <div class="card-title-script">Playlist</div>
                <div class="card-title-serif">SUGIERE UNA CANCIÓN</div>
                <div class="gold-divider"></div>

                <p class="card-text">
                    ¿Qué canción no puede faltar en la fiesta?
                    Ayúdame a crear la playlist perfecta 🎵
                </p>

                <div class="playlist-form">
                    <div class="playlist-input-group">
                        <input type="text" class="playlist-input" id="playlist-song-input" placeholder="Nombre de la canción y artista...">
                        <button class="playlist-add-btn" id="playlist-add-btn">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="playlist-songs" id="playlist-songs"></div>
            </div>
        </div>

        <!-- ============================================
             TARJETA 10: PRESENTE & LLUVIA DE SOBRES
             ============================================ -->
        <div class="card-slide hidden" data-slide="9">
            <div class="card" style="padding: 30px 22px;">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="card-title-script" style="font-size: 2.3rem;">Presente & Regalo</div>
                <div class="card-title-serif" style="font-size: 0.75rem; letter-spacing: 2px;">LLUVIA DE SOBRES</div>
                <div class="gold-divider" style="margin: 10px auto 14px auto;"></div>

                <p class="card-text" style="margin-bottom: 12px; font-size: 0.88rem; line-height: 1.5;">
                    El regalo más valioso para mis quince años es tu compañía, tus bendiciones y compartir juntos esta noche tan soñada. ✨
                </p>

                <!-- Cuadro elegante de lluvia de sobres -->
                <div class="gift-envelope-box">
                    <div class="gift-icon-bubble">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <div style="font-family: var(--font-serif); font-size: 1.05rem; color: var(--dorado); font-weight: 600; margin-bottom: 6px; letter-spacing: 1px;">
                        Lluvia de Sobres ✉️
                    </div>
                    <p style="font-size: 0.84rem; color: var(--crema); line-height: 1.55; margin: 0;">
                        Si es de tu agrado hacerme un presente, te agradecería con todo mi corazón que sea <strong>en efectivo</strong>, el cual será de gran ayuda para cumplir mis metas y proyectos en esta nueva etapa.
                    </p>
                </div>

                <!-- Nota del buzón en la fiesta -->
                <div class="gift-reception-note">
                    <i class="fas fa-box-open"></i>
                    <span>En la recepción del salón contaremos con un buzón especial donde podrás depositar tu sobre con tus mejores deseos.</span>
                </div>
            </div>
        </div>

        <!-- ============================================
             TARJETA 11: RSVP — CONFIRMA TU ASISTENCIA
             ============================================ -->
        <div class="card-slide hidden" data-slide="10">
            <div class="card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="card-title-script">Confirma tu Asistencia</div>
                <div class="card-title-serif">RSVP</div>
                <div class="gold-divider"></div>

                <!-- Formulario RSVP -->
                <div class="rsvp-form-container" id="rsvp-form">
                    <div class="rsvp-error" id="rsvp-error"></div>

                    <div class="rsvp-label">Tu nombre completo</div>
                    <input type="text" class="rsvp-input" id="rsvp-nombre" 
                           placeholder="Nombre y Apellido" 
                           maxlength="150" autocomplete="name">

                    <div class="rsvp-label">¿Asistirás?</div>
                    <div class="rsvp-attendance-btns">
                        <button class="rsvp-attend-btn" id="rsvp-yes" data-attend="true">
                            <i class="fas fa-check-circle"></i>
                            Sí asistiré
                        </button>
                        <button class="rsvp-attend-btn" id="rsvp-no" data-attend="false">
                            <i class="fas fa-times-circle"></i>
                            No podré asistir
                        </button>
                    </div>

                    <!-- Sección de acompañantes (visible solo si confirma que sí asistirá) -->
                    <div class="rsvp-companions-section" id="rsvp-companions-section">
                        <div class="rsvp-companions-header">
                            <span class="rsvp-label" style="margin-bottom: 0;">Acompañantes</span>
                            <span id="rsvp-passes-badge">1 persona (Solo tú)</span>
                        </div>
                        <p style="font-size: 0.73rem; color: rgba(255, 248, 236, 0.7); margin-bottom: 12px; text-align: center; line-height: 1.4;">
                            ¿Vienes con alguien más? Agrega a tus acompañantes para que queden incluidos en tu pase QR:
                        </p>
                        <div id="rsvp-companions-list"></div>
                        <button type="button" class="rsvp-add-companion-btn" id="rsvp-add-companion">
                            <i class="fas fa-user-plus"></i> + Agregar Nombre de Acompañante
                        </button>
                    </div>

                    <button class="rsvp-submit-btn" id="rsvp-submit" disabled>
                        Confirmar Asistencia
                    </button>
                </div>

                <!-- Mensaje de éxito / Pase VIP QR -->
                <div class="rsvp-success" id="rsvp-success">
                    <!-- Pase de entrada VIP con QR si asistirá -->
                    <div class="vip-ticket" id="vipTicket" style="display: none;">
                        <div class="ticket-badge">✨ PASE DE ENTRADA VIP ✨</div>
                        <div class="ticket-tiara">👑</div>
                        <div class="ticket-title">MIS XV AÑOS</div>
                        <div class="ticket-subtitle">Angie Karolina</div>
                        <div class="gold-divider" style="margin: 8px auto 14px auto;"></div>

                        <!-- Contenedor del QR -->
                        <div class="ticket-qr-container">
                            <div id="ticketQrWrapper"></div>
                            <div class="ticket-qr-code-txt" id="ticketQrCodeText"></div>
                        </div>

                        <!-- Detalles del Pase -->
                        <div class="ticket-info">
                            <div class="t-row">
                                <span class="t-lbl">Titular:</span>
                                <span class="t-data" id="ticketGuestName">--</span>
                            </div>
                            <div class="t-row" id="ticketCompanionsRow" style="display: none;">
                                <span class="t-lbl">Acompañantes:</span>
                                <span class="t-data" id="ticketCompanionsNames">--</span>
                            </div>
                            <div class="t-row">
                                <span class="t-lbl">Total Pases:</span>
                                <span class="t-data t-passes" id="ticketTotalPasses">1 Persona</span>
                            </div>
                            <div class="t-row">
                                <span class="t-lbl">Fecha y Hora:</span>
                                <span class="t-data">3 · Oct · 2026 · 8:00 PM</span>
                            </div>
                            <div class="t-row">
                                <span class="t-lbl">Lugar:</span>
                                <span class="t-data">Colegio de Médicos, Mérida</span>
                            </div>
                        </div>

                        <!-- Boletos para la Rifa de Regalos -->
                        <div class="ticket-raffle-section" id="ticketRaffleSection" style="display: none;">
                            <div class="ticket-raffle-header">
                                <i class="fas fa-gift"></i>
                                <span>Tus Boletos para la Rifa de Regalos</span>
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="ticket-raffle-badges" id="ticketRaffleBadges"></div>
                            <div class="raffle-ticket-note">
                                🎁 ¡Conserva estos números! Cada uno participa en la gran rifa de regalos durante la fiesta.
                            </div>
                        </div>

                        <div class="ticket-instruction">
                            <i class="fas fa-qrcode"></i> Presenta este código QR en la entrada para acceder a la fiesta
                        </div>

                        <button type="button" class="btn-ticket-download" id="btnDownloadTicket">
                            <i class="fas fa-ticket-alt"></i> Descargar Pase VIP Completo
                        </button>
                    </div>

                    <!-- Mensaje si confirmó que no asiste -->
                    <div id="rsvp-declined-box" style="display: none; text-align: center; padding: 15px 0;">
                        <div class="rsvp-success-icon">💛</div>
                        <h3 id="rsvp-declined-title" style="font-family: var(--font-script); font-size: 2rem; color: var(--dorado); margin-bottom: 10px;">¡Gracias por avisar!</h3>
                        <p id="rsvp-declined-message" style="font-family: var(--font-sans); font-size: 0.85rem; color: var(--crema); opacity: 0.9; line-height: 1.6;"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================
             TARJETA 12: FOTOS DE LA FIESTA EN VIVO
             ============================================ -->
        <div class="card-slide hidden" data-slide="11">
            <div class="card" style="padding: 22px 18px;">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon" style="margin-bottom: 6px;">
                    <i class="fas fa-camera-retro"></i>
                </div>
                <div class="card-title-script" style="font-size: 2.2rem; margin-bottom: 2px;">Momentos Mágicos</div>
                <div class="card-title-serif" style="font-size: 0.72rem; letter-spacing: 2px;">FOTOS DE LA FIESTA EN VIVO</div>
                <div class="gold-divider" style="margin: 8px auto 12px auto;"></div>

                <p class="card-text" style="font-size: 0.84rem; line-height: 1.45; margin-bottom: 12px;">
                    ¡Sé parte del álbum oficial de mi fiesta! Captura momentos especiales desde tu celular y compártelos en tiempo real. ✨
                </p>

                <div class="live-photos-container">
                    <button type="button" class="btn-camera-upload" id="btnOpenPhotoUpload">
                        <i class="fas fa-camera"></i> Subir Foto en Vivo
                    </button>

                    <a href="en-vivo.php" target="_blank" class="live-tv-link">
                        <i class="fas fa-tv"></i> Ver Pantalla en Vivo (Modo Proyector)
                    </a>

                    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-top: 6px; padding: 0 4px;">
                        <span style="font-size: 0.75rem; color: var(--dorado-claro); font-weight: 600;">
                            📸 Galería Comunitaria (<span id="liveGalleryCount">0</span>)
                        </span>
                        <button type="button" id="btnRefreshGallery" style="background: none; border: none; color: var(--dorado); cursor: pointer; font-size: 0.78rem; display: flex; align-items: center; gap: 4px;">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>

                    <!-- Mosaico de fotos en vivo -->
                    <div class="live-gallery-grid" id="liveGalleryGrid">
                        <div class="live-gallery-empty" id="liveGalleryEmpty">
                            <i class="fas fa-images"></i>
                            Aún no hay fotos. ¡Toca el botón arriba y sé el primero en subir un recuerdo!
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================
             TARJETA 13: GRACIAS & FOTO DE ANGIE
             ============================================ -->
        <div class="card-slide hidden" data-slide="12">
            <div class="card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px 16px;">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-title-script" style="font-size: 2.3rem; margin-bottom: 2px;">¡Te Espero!</div>
                <div class="card-title-serif" style="font-size: 0.72rem; letter-spacing: 2px; margin-bottom: 8px;">MIS XV AÑOS</div>

                <!-- Marco dorado con la foto de Angie -->
                <div class="angie-photo-wrapper">
                    <div class="angie-photo-frame">
                        <img src="angie-xv.jpg" onerror="if(!this.dataset.retry){this.dataset.retry=1;this.src='public/angie-xv.jpg';}" alt="Angie Karolina — Mis XV Años" class="angie-photo-img" loading="eager">
                        <div class="angie-photo-sheen"></div>
                    </div>
                </div>

                <div class="gold-divider" style="margin: 10px auto 8px auto;"></div>

                <p class="card-text" style="margin: 0; font-size: 0.86rem; line-height: 1.4;">
                    Tu presencia es el mejor regalo.<br>
                    ¡Celebremos juntos esta noche mágica! ✨
                </p>

                <p class="card-text" style="font-size: 0.75rem; margin-top: 8px; opacity: 0.85;">
                    Con todo mi cariño,<br>
                    <span style="font-family: var(--font-script); font-size: 1.75rem; color: var(--dorado); display: inline-block; margin-top: 1px;">
                        Angie Karolina
                    </span>
                </p>
            </div>
        </div>

    </div>

    <!-- Navegación -->
    <div class="nav-dots" id="navDots"></div>
    <div class="nav-arrows">
        <button class="nav-arrow" id="prevBtn" disabled>
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="nav-arrow" id="nextBtn">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <!-- Audio toggle con tooltip y reproductor YouTube -->
    <button class="audio-toggle" id="audioToggle" title="Música: Valiente - Con toda libertad" aria-label="Música de fondo">
        <i class="fas fa-volume-mute" id="audioIcon"></i>
    </button>
    <div class="audio-tooltip" id="audioTooltip">🎵 Toca para activar música</div>

    <!-- Acceso discreto para administración de invitados -->
    <a href="/admin/" target="_blank" class="admin-link-btn" title="Panel de Administración" aria-label="Panel de Administración">
        <i class="fas fa-lock"></i>
    </a>

    <!-- Reproductor de Audio HTML5 Nativo (100% compatible con iOS y Android) -->
    <audio id="bgMusic" loop preload="auto" playsinline webkit-playsinline>
        <source src="musica.mp3" type="audio/mpeg">
        <source src="public/musica.mp3" type="audio/mpeg">
        <source src="musica.m4a" type="audio/mp4">
        <source src="public/musica.m4a" type="audio/mp4">
    </audio>

    <!-- Modal de Subida de Fotos en Vivo -->
    <div id="photoUploadModal" class="photo-upload-modal" style="display: none;">
        <div class="photo-upload-box">
            <button type="button" class="lightbox-close" id="btnCloseUploadModal" style="top: 12px; right: 12px; width: 32px; height: 32px; font-size: 1rem;">
                <i class="fas fa-times"></i>
            </button>

            <div style="font-family: var(--font-serif); font-size: 1.3rem; color: var(--dorado-brillante); font-weight: 700; margin-bottom: 2px;">
                📸 Compartir Recuerdo
            </div>
            <div style="font-size: 0.78rem; color: var(--dorado-claro); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 14px;">
                XV Años Angie Karolina
            </div>

            <input type="file" id="filePhotoInput" accept="image/*" capture="environment" style="display: none;">

            <div class="photo-preview-wrap" id="uploadPreviewWrap" style="display: none;">
                <img id="imgUploadPreview" src="" alt="Vista previa de foto">
            </div>

            <input type="text" class="upload-input" id="uploadGuestName" placeholder="Tu nombre y apellido" maxlength="100">
            <textarea class="upload-input" id="uploadGuestMsg" placeholder="Dedicatoria o mensaje para Angie (opcional)..." rows="2" maxlength="300" style="resize: none;"></textarea>

            <div id="uploadStatusMsg" style="font-size: 0.8rem; margin-bottom: 10px; min-height: 18px;"></div>

            <button type="button" class="btn-camera-upload" id="btnSubmitPhoto" style="width: 100%; max-width: 100%; padding: 12px;">
                <i class="fas fa-paper-plane"></i> Publicar en el Álbum
            </button>
        </div>
    </div>

    <!-- Visor de Foto a Pantalla Completa (Lightbox) -->
    <div id="photoLightbox" class="photo-lightbox" style="display: none;">
        <button type="button" class="lightbox-close" id="btnCloseLightbox">
            <i class="fas fa-times"></i>
        </button>

        <img id="lightboxImg" class="lightbox-img" src="" alt="Foto en vivo ampliada">

        <div class="lightbox-details">
            <div class="lightbox-author" id="lightboxAuthor">Invitado</div>
            <div class="lightbox-msg" id="lightboxMsg">"¡Felicidades Angie!"</div>
            <div style="display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn-like-photo" id="btnLikePhoto">
                    <i class="fas fa-heart"></i> <span id="lightboxLikesCount">0</span> Me encanta
                </button>
                <a href="#" id="btnDownloadLightboxPhoto" class="btn-download-lightbox" download="foto_angie_xv.jpg" target="_blank">
                    <i class="fas fa-download"></i> Descargar
                </a>
            </div>
        </div>
    </div>

    <script>
    /* ================================================================
       DECK NAVIGATION
       ================================================================ */
    (() => {
        const slides = document.querySelectorAll('.card-slide');
        const totalSlides = slides.length;
        let currentSlide = 0;
        let isTransitioning = false;

        // Create nav dots
        const dotsContainer = document.getElementById('navDots');
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('div');
            dot.className = 'nav-dot' + (i === 0 ? ' active' : '');
            dot.addEventListener('click', () => goToSlide(i));
            dotsContainer.appendChild(dot);
        }

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        function updateSlides() {
            slides.forEach((slide, index) => {
                slide.classList.remove('active', 'prev', 'next', 'hidden');
                if (index === currentSlide) {
                    slide.classList.add('active');
                    // Trigger card animation
                    const card = slide.querySelector('.card');
                    if (card) {
                        card.classList.remove('animate-in');
                        void card.offsetWidth; // Force reflow
                        card.classList.add('animate-in');
                    }
                } else if (index < currentSlide) {
                    slide.classList.add('prev');
                } else {
                    slide.classList.add('next');
                }
            });

            // Update dots
            document.querySelectorAll('.nav-dot').forEach((dot, i) => {
                dot.classList.toggle('active', i === currentSlide);
            });

            // Update buttons
            prevBtn.disabled = currentSlide === 0;
            nextBtn.disabled = currentSlide === totalSlides - 1;
        }

        function goToSlide(index) {
            if (isTransitioning || index === currentSlide || index < 0 || index >= totalSlides) return;
            isTransitioning = true;
            currentSlide = index;
            updateSlides();
            setTimeout(() => { isTransitioning = false; }, 650);
        }

        prevBtn.addEventListener('click', () => goToSlide(currentSlide - 1));
        nextBtn.addEventListener('click', () => goToSlide(currentSlide + 1));

        // Touch/Swipe support
        let touchStartX = 0;
        let touchStartY = 0;
        const deck = document.getElementById('deckContainer');

        deck.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });

        deck.addEventListener('touchend', (e) => {
            const deltaX = e.changedTouches[0].screenX - touchStartX;
            const deltaY = e.changedTouches[0].screenY - touchStartY;
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
                if (deltaX < 0) goToSlide(currentSlide + 1);
                else goToSlide(currentSlide - 1);
            }
        }, { passive: true });

        // Keyboard nav
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') goToSlide(currentSlide + 1);
            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') goToSlide(currentSlide - 1);
        });
    })();

    /* ================================================================
       COUNTDOWN
       ================================================================ */
    (() => {
        const eventDate = new Date('2026-10-03T20:00:00-04:00').getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const diff = eventDate - now;

            if (diff <= 0) {
                document.getElementById('cd-days').textContent = '🎉';
                document.getElementById('cd-hours').textContent = '¡HOY!';
                document.getElementById('cd-minutes').textContent = '';
                document.getElementById('cd-seconds').textContent = '';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('cd-days').textContent = days;
            document.getElementById('cd-hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('cd-minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('cd-seconds').textContent = String(seconds).padStart(2, '0');
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    })();

    /* ================================================================
       PARTICLES
       ================================================================ */
    (() => {
        const container = document.getElementById('particles');
        if (!container) return;
        for (let i = 0; i < 20; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.left = Math.random() * 100 + '%';
            p.style.top = Math.random() * 100 + '%';
            p.style.setProperty('--duration', (2 + Math.random() * 4) + 's');
            p.style.setProperty('--delay', (Math.random() * 4) + 's');
            p.style.width = (2 + Math.random() * 4) + 'px';
            p.style.height = p.style.width;
            container.appendChild(p);
        }
    })();

    /* ================================================================
       TRIVIA
       ================================================================ */
    (() => {
        const triviaQuestions = [
            {
                question: '¿Cuál es el color favorito de Angie?',
                options: ['Rosa', 'Verde esmeralda', 'Azul cielo', 'Morado'],
                correct: 1,
                correctMsg: '¡Exacto! El verde esmeralda es su favorito y la temática de esta noche mágica 💚✨',
                wrongMsg: 'La respuesta correcta es: <strong>Verde esmeralda</strong> 💚'
            },
            {
                question: '¿Cuál es la actividad favorita de Angie?',
                options: ['La repostería', 'Pintar', 'Bailar', 'Fotografía'],
                correct: 0,
                correctMsg: '¡Así es! A Angie le fascina la repostería y hornear delicias con amor 🧁🎂',
                wrongMsg: 'La respuesta correcta es: <strong>La repostería</strong> 🧁🎂'
            },
            {
                question: '¿Cuál es la comida favorita de Angie?',
                options: ['Pizza', 'Pasticho', 'Hamburguesa', 'Sushi'],
                correct: 1,
                correctMsg: '¡Totalmente! El pasticho es su plato preferido e irresistible 🍝🧀',
                wrongMsg: 'La respuesta correcta es: <strong>Pasticho</strong> 🍝🧀'
            },
            {
                question: '¿Cuál es el género musical favorito de Angie?',
                options: ['Reggaetón', 'Pop', 'Salsa', 'Baladas'],
                correct: 0,
                correctMsg: '¡A romper la pista! El reggaetón es su ritmo favorito para bailar y disfrutar 🎶🔥',
                wrongMsg: 'La respuesta correcta es: <strong>Reggaetón</strong> 🎶🔥'
            },
            {
                question: '¿Cuándo es el cumpleaños de Angie?',
                options: ['23 de Septiembre', '3 de Octubre', '15 de Septiembre', '28 de Septiembre'],
                correct: 0,
                correctMsg: '¡Exacto! Nació el 23 de septiembre y lo celebramos en grande el 3 de octubre 🎂🎉',
                wrongMsg: 'Su cumpleaños es el <strong>23 de septiembre</strong> (¡y la fiesta el 3 de octubre!) 🎂'
            }
        ];

        let currentQ = 0;
        let answers = new Array(triviaQuestions.length).fill(null);

        const questionEl = document.getElementById('trivia-question');
        const optionsEl = document.getElementById('trivia-options');
        const resultEl = document.getElementById('trivia-result');
        const counterEl = document.getElementById('trivia-counter');
        const prevBtn = document.getElementById('trivia-prev');
        const nextBtn = document.getElementById('trivia-next');
        const navEl = document.getElementById('trivia-nav');

        if (!questionEl || !optionsEl || !counterEl || !prevBtn || !nextBtn) return;

        const letters = ['A', 'B', 'C', 'D'];

        function renderQuestion() {
            const q = triviaQuestions[currentQ];
            counterEl.textContent = `Pregunta ${currentQ + 1} de ${triviaQuestions.length}`;
            questionEl.textContent = q.question;
            optionsEl.innerHTML = '';
            
            const hasAnswered = answers[currentQ] !== null;

            if (!hasAnswered) {
                resultEl.style.display = 'none';
                resultEl.className = 'trivia-result';
            } else {
                const isCorrect = answers[currentQ] === q.correct;
                resultEl.className = `trivia-result ${isCorrect ? 'correct' : 'incorrect'}`;
                
                let feedback = '';
                if (currentQ === 4 && !isCorrect && answers[currentQ] === 1) {
                    feedback = '<i class="fas fa-info-circle"></i> ¡El 3 de octubre es la fiesta! Pero su cumpleaños real es el <strong>23 de septiembre</strong> 🎂';
                } else if (isCorrect) {
                    feedback = `<i class="fas fa-check-circle"></i> ${q.correctMsg}`;
                } else {
                    feedback = `<i class="fas fa-info-circle"></i> ${q.wrongMsg}`;
                }
                resultEl.innerHTML = feedback;
                resultEl.style.display = 'block';
            }

            q.options.forEach((opt, i) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'trivia-option';

                if (hasAnswered) {
                    btn.disabled = true;
                    if (i === q.correct) {
                        btn.classList.add('correct');
                    } else if (answers[currentQ] === i) {
                        btn.classList.add('incorrect');
                    }
                }

                const iconHtml = hasAnswered 
                    ? (i === q.correct 
                        ? '<i class="fas fa-check-circle opt-icon"></i>' 
                        : (answers[currentQ] === i ? '<i class="fas fa-times-circle opt-icon"></i>' : '<span class="opt-icon"></span>'))
                    : '<span class="opt-icon"></span>';

                btn.innerHTML = `
                    <span class="opt-letter">${letters[i] || (i + 1)}</span>
                    <span class="opt-text">${escapeHtml(opt)}</span>
                    ${iconHtml}
                `;

                btn.addEventListener('click', () => {
                    if (answers[currentQ] !== null) return;
                    answers[currentQ] = i;
                    renderQuestion();

                    // Si no es la última pregunta, avanzar suavemente
                    if (currentQ < triviaQuestions.length - 1) {
                        setTimeout(() => {
                            if (currentQ < triviaQuestions.length - 1 && answers[currentQ] !== null) {
                                currentQ++;
                                renderQuestion();
                            }
                        }, 1300);
                    }
                });

                optionsEl.appendChild(btn);
            });

            prevBtn.disabled = currentQ === 0;
            
            if (currentQ === triviaQuestions.length - 1) {
                nextBtn.innerHTML = 'Ver Resultados <i class="fas fa-trophy"></i>';
            } else {
                nextBtn.innerHTML = 'Siguiente <i class="fas fa-arrow-right"></i>';
            }
        }

        function showResults() {
            const correctCount = answers.filter((a, i) => a === triviaQuestions[i].correct).length;
            const total = triviaQuestions.length;
            
            counterEl.textContent = 'Trivia Finalizada';
            questionEl.textContent = '¡Resultados de la Trivia!';
            
            let badge = '✨';
            let msg = '';
            if (correctCount >= 4) {
                badge = '👑';
                msg = '¡Increíble! Eres de las personas que mejor conocen a Angie. ¡Qué gran amistad!';
            } else if (correctCount >= 2) {
                badge = '💛';
                msg = '¡Muy bien! Conoces grandes detalles de Angie, ¡esta fiesta será inolvidable!';
            } else {
                badge = '🌸';
                msg = '¡Qué lindo que nos acompañes! En esta noche mágica vas a conocerla mucho más.';
            }

            optionsEl.innerHTML = `
                <div style="text-align: center; padding: 12px 6px;">
                    <div style="font-size: 2.5rem; margin-bottom: 8px;">${badge}</div>
                    <div style="font-family: var(--font-serif); font-size: 1.3rem; color: var(--dorado-claro); margin-bottom: 6px;">
                        ${correctCount} de ${total} Aciertos
                    </div>
                    <p style="font-family: var(--font-sans); font-size: 0.85rem; color: var(--crema); line-height: 1.5; opacity: 0.95; margin-bottom: 18px;">
                        ${msg}
                    </p>
                    <button type="button" class="trivia-nav-btn" id="trivia-restart" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; font-size: 0.85rem;">
                        <i class="fas fa-redo"></i> Volver a jugar
                    </button>
                </div>
            `;
            
            resultEl.style.display = 'none';
            if (navEl) navEl.style.display = 'none';

            const restartBtn = document.getElementById('trivia-restart');
            if (restartBtn) {
                restartBtn.addEventListener('click', () => {
                    currentQ = 0;
                    answers = new Array(triviaQuestions.length).fill(null);
                    if (navEl) navEl.style.display = 'flex';
                    renderQuestion();
                });
            }
        }

        prevBtn.addEventListener('click', () => {
            if (currentQ > 0) {
                currentQ--;
                renderQuestion();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentQ < triviaQuestions.length - 1) {
                currentQ++;
                renderQuestion();
            } else {
                showResults();
            }
        });

        // Iniciar la trivia
        renderQuestion();
    })();

    /* ================================================================
       PLAYLIST (SUGERIR CANCIONES CON PERSISTENCIA EN BD)
       ================================================================ */
    (() => {
        const input = document.getElementById('playlist-song-input');
        const addBtn = document.getElementById('playlist-add-btn');
        const list = document.getElementById('playlist-songs');

        async function loadSongs() {
            try {
                const res = await fetch('api/canciones.php');
                const data = await res.json();
                if (data && data.success && Array.isArray(data.canciones)) {
                    list.innerHTML = '';
                    data.canciones.forEach(c => {
                        const div = document.createElement('div');
                        div.className = 'playlist-song';
                        div.innerHTML = `<i class="fas fa-music" style="color: var(--dorado); margin-right: 6px;"></i> <span>${escapeHtml(c.cancion)}</span>`;
                        list.appendChild(div);
                    });
                }
            } catch (e) {
                console.warn('Error cargando playlist:', e);
            }
        }

        async function addSong() {
            const song = input.value.trim();
            if (!song) return;

            let guestName = '';
            const rsvpName = document.getElementById('rsvp-nombre');
            if (rsvpName && rsvpName.value.trim()) {
                guestName = rsvpName.value.trim();
            } else {
                try { guestName = localStorage.getItem('angie_rsvp_name') || ''; } catch(e){}
            }

            addBtn.disabled = true;
            addBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const res = await fetch('api/canciones.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        cancion: song,
                        nombre: guestName
                    })
                });
                const data = await res.json();
                if (data && data.success) {
                    input.value = '';
                    loadSongs();
                } else {
                    alert(data.error || 'Error al agregar canción');
                }
            } catch (err) {
                console.warn('Error al guardar canción:', err);
            } finally {
                addBtn.disabled = false;
                addBtn.innerHTML = '<i class="fas fa-plus"></i>';
            }
        }

        addBtn.addEventListener('click', addSong);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') addSong();
        });

        loadSongs();
    })();

    /* ================================================================
       RSVP MODULE
       ================================================================ */
    (() => {
        const MAX_COMPANIONS = 5;
        let selectedAttendance = null;
        let isSubmitting = false;

        const form = document.getElementById('rsvp-form');
        const successDiv = document.getElementById('rsvp-success');
        const errorDiv = document.getElementById('rsvp-error');
        const nombreInput = document.getElementById('rsvp-nombre');
        const yesBtn = document.getElementById('rsvp-yes');
        const noBtn = document.getElementById('rsvp-no');
        const companionsSection = document.getElementById('rsvp-companions-section');
        const companionsList = document.getElementById('rsvp-companions-list');
        const addCompanionBtn = document.getElementById('rsvp-add-companion');
        const submitBtn = document.getElementById('rsvp-submit');

        function showError(msg) {
            errorDiv.textContent = msg;
            errorDiv.classList.add('visible');
            setTimeout(() => errorDiv.classList.remove('visible'), 5000);
        }

        function hideError() {
            errorDiv.classList.remove('visible');
        }

        // Attendance selection
        function selectAttendance(attending) {
            selectedAttendance = attending;
            yesBtn.classList.toggle('selected', attending === true);
            noBtn.classList.toggle('selected', attending === false);

            if (attending) {
                companionsSection.classList.add('visible');
            } else {
                companionsSection.classList.remove('visible');
                companionsList.innerHTML = '';
            }

            updateAddBtnState();
            submitBtn.disabled = false;
        }

        yesBtn.addEventListener('click', () => selectAttendance(true));
        noBtn.addEventListener('click', () => selectAttendance(false));

        const passesBadge = document.getElementById('rsvp-passes-badge');

        function updateAddBtnState() {
            const count = companionsList.children.length;
            addCompanionBtn.disabled = count >= MAX_COMPANIONS;
            if (passesBadge) {
                if (count === 0) {
                    passesBadge.textContent = '1 persona (Solo tú)';
                } else {
                    passesBadge.textContent = `${count + 1} personas (Tú + ${count} acompañante${count > 1 ? 's' : ''})`;
                }
            }
        }

        // Add companion
        function addCompanion() {
            const count = companionsList.children.length;
            if (count >= MAX_COMPANIONS) {
                showError(`Máximo ${MAX_COMPANIONS} acompañantes permitidos.`);
                return;
            }

            const entry = document.createElement('div');
            entry.className = 'rsvp-companion-entry';
            entry.innerHTML = `
                <input type="text" placeholder="Nombre y Apellido del acompañante ${count + 1}" maxlength="150" autocomplete="off">
                <button type="button" class="rsvp-remove-companion" title="Eliminar"><i class="fas fa-times"></i></button>
            `;

            entry.querySelector('.rsvp-remove-companion').addEventListener('click', () => {
                entry.remove();
                updateAddBtnState();
            });

            companionsList.appendChild(entry);
            entry.querySelector('input').focus();
            updateAddBtnState();
        }

        addCompanionBtn.addEventListener('click', addCompanion);

        // Submit RSVP
        submitBtn.addEventListener('click', async () => {
            hideError();

            if (isSubmitting) return;

            const nombre = nombreInput.value.trim();
            if (!nombre) {
                showError('Por favor ingresa tu nombre completo.');
                nombreInput.focus();
                return;
            }

            // Validate name chars
            if (!/^[\p{L}\s.\-']+$/u.test(nombre)) {
                showError('El nombre solo puede contener letras, espacios y tildes.');
                return;
            }

            if (selectedAttendance === null) {
                showError('Por favor indica si asistirás o no.');
                return;
            }

            // Gather companions
            const acompanantes = [];
            if (selectedAttendance) {
                const inputs = companionsList.querySelectorAll('input');
                for (const input of inputs) {
                    const val = input.value.trim();
                    if (val) {
                        if (!/^[\p{L}\s.\-']+$/u.test(val)) {
                            showError(`El nombre "${val}" contiene caracteres no válidos.`);
                            input.focus();
                            return;
                        }
                        acompanantes.push(val);
                    }
                }
            }

            // Disable and show spinner
            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Confirmando...';

            try {
                const response = await fetch('api/confirmar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        nombre_completo: nombre,
                        asistira: selectedAttendance,
                        acompanantes: acompanantes
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Hide form and show success container
                    form.style.display = 'none';
                    successDiv.classList.add('visible');

                    // Desplazar la tarjeta suavemente hacia arriba para que el pase sea visible de inmediato
                    const cardParent = form.closest('.card');
                    if (cardParent) {
                        cardParent.scrollTo({ top: 0, behavior: 'smooth' });
                    }

                    // Determinar si asiste
                    const isAttending = (data.asistira !== undefined) ? Boolean(data.asistira) : Boolean(selectedAttendance);

                    if (isAttending) {
                        // Rellenar datos del Pase VIP
                        const vipTicket = document.getElementById('vipTicket');
                        const guestNameEl = document.getElementById('ticketGuestName');
                        const companionsRow = document.getElementById('ticketCompanionsRow');
                        const companionsNamesEl = document.getElementById('ticketCompanionsNames');
                        const totalPassesEl = document.getElementById('ticketTotalPasses');
                        const qrCodeTextEl = document.getElementById('ticketQrCodeText');
                        const qrWrapper = document.getElementById('ticketQrWrapper');

                        guestNameEl.textContent = data.nombre || nombre;

                        // Extraer acompañantes del backend o del formulario
                        const finalAcomps = (data.acompanantes && Array.isArray(data.acompanantes) && data.acompanantes.length > 0)
                            ? data.acompanantes
                            : acompanantes;

                        if (finalAcomps.length > 0) {
                            companionsRow.style.display = 'flex';
                            companionsNamesEl.textContent = finalAcomps.join(', ');
                        } else {
                            companionsRow.style.display = 'none';
                        }

                        const totalP = data.total_pases || (1 + finalAcomps.length);
                        totalPassesEl.textContent = `${totalP} ${totalP === 1 ? 'Persona' : 'Personas'}`;

                        const qrCodeId = data.qr_code || ('XVANGIE-' + (data.id || '001'));
                        qrCodeTextEl.textContent = qrCodeId;

                        // Mostrar Boletos para la Rifa de Regalos
                        const raffleSection = document.getElementById('ticketRaffleSection');
                        const raffleBadges = document.getElementById('ticketRaffleBadges');
                        if (raffleSection && raffleBadges) {
                            if (data.boletos_rifa && data.boletos_rifa.length > 0) {
                                raffleBadges.innerHTML = '';
                                data.boletos_rifa.forEach(b => {
                                    const item = document.createElement('div');
                                    item.className = 'raffle-ticket-item';
                                    item.innerHTML = `
                                        <div class="raffle-ticket-person">
                                            <i class="fas fa-user${b.es_titular ? '-check' : ''}"></i>
                                            <span>${escapeHtml(b.nombre)}</span>
                                        </div>
                                        <div class="raffle-ticket-number">
                                            <i class="fas fa-ticket-alt"></i>
                                            <span>${escapeHtml(b.codigo)}</span>
                                        </div>
                                    `;
                                    raffleBadges.appendChild(item);
                                });
                                raffleSection.style.display = 'block';
                            } else {
                                raffleSection.style.display = 'none';
                            }
                        }

                        // Generar el Código QR
                        const qrPayload = `XV-ANGIE | PASE #${data.id || 1} | TITULAR: ${data.nombre || nombre} | PASES: ${totalP} | CODIGO: ${qrCodeId}`;
                        qrWrapper.innerHTML = '';

                        if (typeof QRCode !== 'undefined') {
                            try {
                                new QRCode(qrWrapper, {
                                    text: qrPayload,
                                    width: 170,
                                    height: 170,
                                    colorDark: '#062E25',
                                    colorLight: '#FFFDF5',
                                    correctLevel: QRCode.CorrectLevel.M
                                });
                            } catch(e) {
                                const qrImg = document.createElement('img');
                                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=170x170&data=${encodeURIComponent(qrPayload)}&color=062E25&bgcolor=FFFDF5`;
                                qrImg.alt = 'Código QR Pase VIP';
                                qrWrapper.appendChild(qrImg);
                            }
                        } else {
                            const qrImg = document.createElement('img');
                            qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=170x170&data=${encodeURIComponent(qrPayload)}&color=062E25&bgcolor=FFFDF5`;
                            qrImg.alt = 'Código QR Pase VIP';
                            qrWrapper.appendChild(qrImg);
                        }

                        vipTicket.style.display = 'block';

                        // Botón de descarga de Pase VIP (Ticket completo con diseño, QR y datos)
                        const downloadBtn = document.getElementById('btnDownloadTicket');
                        if (downloadBtn) {
                            downloadBtn.onclick = () => {
                                const guestFileName = (data.nombre || nombre || 'Invitado').replace(/\s+/g, '_');

                                const downloadQrFallback = () => {
                                    const canvas = qrWrapper.querySelector('canvas');
                                    let url = '';
                                    if (canvas) {
                                        url = canvas.toDataURL('image/png');
                                    } else {
                                        const img = qrWrapper.querySelector('img');
                                        if (img) url = img.src;
                                    }
                                    if (url) {
                                        const link = document.createElement('a');
                                        link.href = url;
                                        link.download = `Pase_XV_Angie_${guestFileName}.png`;
                                        document.body.appendChild(link);
                                        link.click();
                                        link.remove();
                                    }
                                };

                                if (typeof html2canvas !== 'undefined' && vipTicket) {
                                    const originalBtnHtml = downloadBtn.innerHTML;
                                    downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando Pase VIP...';
                                    downloadBtn.disabled = true;

                                    html2canvas(vipTicket, {
                                        scale: 2,
                                        backgroundColor: '#062E25',
                                        useCORS: true,
                                        allowTaint: true,
                                        logging: false,
                                        ignoreElements: (el) => el.id === 'btnDownloadTicket' || el.classList.contains('btn-ticket-download')
                                    }).then((canvas) => {
                                        const url = canvas.toDataURL('image/png');
                                        const link = document.createElement('a');
                                        link.href = url;
                                        link.download = `Pase_VIP_XV_Angie_${guestFileName}.png`;
                                        document.body.appendChild(link);
                                        link.click();
                                        link.remove();

                                        downloadBtn.innerHTML = '<i class="fas fa-check-circle"></i> ¡Pase Descargado!';
                                        setTimeout(() => {
                                            downloadBtn.innerHTML = originalBtnHtml;
                                            downloadBtn.disabled = false;
                                        }, 2500);
                                    }).catch((err) => {
                                        console.warn('html2canvas error, fallback al QR:', err);
                                        downloadQrFallback();
                                        downloadBtn.innerHTML = originalBtnHtml;
                                        downloadBtn.disabled = false;
                                    });
                                } else {
                                    downloadQrFallback();
                                }
                            };
                        }
                    } else {
                        // Mensaje de declinación
                        const declinedBox = document.getElementById('rsvp-declined-box');
                        document.getElementById('rsvp-declined-message').textContent = data.message;
                        declinedBox.style.display = 'block';
                    }

                    // Efecto de destello y chispas
                    const flash = document.createElement('div');
                    flash.className = 'gold-flash';
                    document.body.appendChild(flash);
                    setTimeout(() => flash.remove(), 1500);

                    createSparkleBurst();
                } else {
                    showError(data.error || 'Ocurrió un error. Intenta de nuevo.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Confirmar Asistencia';
                    isSubmitting = false;
                }
            } catch (err) {
                showError('Error de conexión. Verifica tu internet e intenta de nuevo.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Confirmar Asistencia';
                isSubmitting = false;
            }
        });

        function createSparkleBurst() {
            const burst = document.createElement('div');
            burst.className = 'sparkle-burst';
            burst.style.left = '50%';
            burst.style.top = '50%';
            document.body.appendChild(burst);

            const colors = ['#C8A24A', '#E7D49A', '#FFF8EC', '#2F8F68'];
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'sparkle-particle';
                particle.style.background = colors[Math.floor(Math.random() * colors.length)];
                const angle = (Math.PI * 2 * i) / 20;
                const distance = 80 + Math.random() * 120;
                particle.style.setProperty('--tx', Math.cos(angle) * distance + 'px');
                particle.style.setProperty('--ty', Math.sin(angle) * distance + 'px');
                burst.appendChild(particle);
            }

            setTimeout(() => burst.remove(), 1200);
        }
    })();

    /* ================================================================
       AUDIO (HTML5 Background Player - "Valiente / Con toda libertad")
       ================================================================ */
    (() => {
        const toggle = document.getElementById('audioToggle');
        const icon = document.getElementById('audioIcon');
        const tooltip = document.getElementById('audioTooltip');
        const audio = document.getElementById('bgMusic');
        if (!toggle || !icon || !audio) return;

        let isPlaying = false;
        let userWantsMusic = true;
        let tooltipTimeout = null;
        let lastToggleTimestamp = 0;

        function showTooltip(text, duration = 3500) {
            if (!tooltip) return;
            if (tooltipTimeout) clearTimeout(tooltipTimeout);
            tooltip.textContent = text;
            tooltip.classList.add('visible');
            tooltipTimeout = setTimeout(() => {
                tooltip.classList.remove('visible');
            }, duration);
        }

        function updatePlayState(playing) {
            isPlaying = playing;
            if (playing) {
                toggle.classList.add('playing');
                icon.className = 'fas fa-compact-disc';
                detachGestureListeners();
            } else {
                toggle.classList.remove('playing');
                icon.className = 'fas fa-volume-mute';
            }
        }

        audio.addEventListener('play', () => {
            updatePlayState(true);
            showTooltip('🎶 Sonando: Valiente - Con toda libertad', 3500);
        });

        audio.addEventListener('pause', () => {
            updatePlayState(false);
        });

        audio.addEventListener('ended', () => {
            updatePlayState(false);
        });

        // Intentar reproducir con volumen óptimo
        function tryPlay() {
            if (!userWantsMusic) return;
            audio.volume = 0.85;
            const promise = audio.play();
            if (promise !== undefined) {
                promise.then(() => {
                    updatePlayState(true);
                }).catch((err) => {
                    // En móviles espera al primer toque o deslizamiento
                    console.log('Esperando interacción de usuario en móvil:', err);
                });
            }
        }

        // Sugerencia inicial
        setTimeout(() => {
            if (!isPlaying && userWantsMusic) {
                showTooltip('🎵 Toca la pantalla para activar música', 4000);
            }
        }, 1200);

        function handleToggleAction(e) {
            const now = Date.now();
            if (now - lastToggleTimestamp < 350) return;
            lastToggleTimestamp = now;

            if (e && e.cancelable && e.type === 'touchend') {
                e.preventDefault();
            }
            if (e) {
                e.stopPropagation();
            }

            if (isPlaying) {
                userWantsMusic = false;
                audio.pause();
                showTooltip('Música en pausa', 2000);
            } else {
                userWantsMusic = true;
                tryPlay();
            }
        }

        toggle.addEventListener('click', handleToggleAction);
        toggle.addEventListener('touchend', handleToggleAction, { passive: false });

        // Activación inmediata en el primer toque de pantalla o deslizamiento (crucial para móviles iOS/Android)
        function onUserInteraction(e) {
            if (e && e.target && (toggle.contains(e.target) || e.target === toggle)) {
                return;
            }
            if (userWantsMusic && !isPlaying) {
                tryPlay();
            }
        }

        const gestureEvents = ['touchstart', 'touchend', 'pointerdown', 'click'];
        function attachGestureListeners() {
            gestureEvents.forEach(evt => {
                document.addEventListener(evt, onUserInteraction, { passive: true });
            });
        }

        function detachGestureListeners() {
            gestureEvents.forEach(evt => {
                document.removeEventListener(evt, onUserInteraction);
            });
        }

        attachGestureListeners();

        // Enlace con la navegación de tarjetas
        const navNext = document.getElementById('nextBtn');
        const navPrev = document.getElementById('prevBtn');
        if (navNext) navNext.addEventListener('click', () => { if (userWantsMusic && !isPlaying) tryPlay(); });
        if (navPrev) navPrev.addEventListener('click', () => { if (userWantsMusic && !isPlaying) tryPlay(); });

        const deckContainer = document.getElementById('deckContainer');
        if (deckContainer) {
            deckContainer.addEventListener('touchend', () => {
                if (userWantsMusic && !isPlaying) tryPlay();
            }, { passive: true });
        }

        // Intentar arranque automático
        tryPlay();
    })();

    /* ================================================================
       MARIPOSAS DORADAS EN LAS ESQUINAS (CORNER BUTTERFLIES LOGIC)
       ================================================================ */
    (() => {
        const container = document.getElementById('butterfliesContainer');
        if (!container) return;

        // Solo 4 mariposas sutiles (una por cada esquina) para no tapar la tarjeta
        const BUTTERFLY_COUNT = 4;
        const butterflies = [];

        function createButterflyElement(size, flapSpeed) {
            const el = document.createElement('div');
            el.className = 'gold-butterfly';
            el.style.setProperty('--b-size', size + 'px');
            el.style.setProperty('--flap-speed', flapSpeed + 's');

            el.innerHTML = `
                <div class="butterfly-inner">
                    <div class="butterfly-wing left">
                        <svg viewBox="0 0 50 60" style="width:100%;height:100%;display:block;overflow:visible;">
                            <path d="M48,28 C45,12 28,1 8,4 C0,12 5,26 22,28 C8,31 3,46 15,54 C28,60 42,48 48,28 Z" fill="url(#globalGoldWing)" fill-opacity="0.96" stroke="#FFE680" stroke-width="0.8" />
                            <path d="M46,28 C32,20 18,12 9,6 M46,28 C30,26 18,24 10,27 M46,28 C32,36 22,46 16,51 M36,18 C26,24 20,27 15,36" stroke="#FFFDF5" stroke-width="1.3" stroke-linecap="round" opacity="0.85" fill="none"/>
                            <circle cx="10" cy="5" r="1.6" fill="#FFF" opacity="0.9"/>
                            <circle cx="20" cy="2" r="1.6" fill="#FFF" opacity="0.9"/>
                            <circle cx="32" cy="5" r="1.6" fill="#FFF" opacity="0.9"/>
                            <circle cx="12" cy="52" r="1.4" fill="#FFF" opacity="0.9"/>
                            <circle cx="22" cy="56" r="1.4" fill="#FFF" opacity="0.9"/>
                        </svg>
                    </div>
                    <div class="butterfly-center">
                        <svg viewBox="0 0 10 60" style="width:100%;height:100%;display:block;overflow:visible;">
                            <path d="M4,15 C2,8 0,3 1,0 M6,15 C8,8 10,3 9,0" stroke="#FFFDF5" stroke-width="1.3" stroke-linecap="round" fill="none"/>
                            <circle cx="1" cy="0" r="1.3" fill="#FFE680"/>
                            <circle cx="9" cy="0" r="1.3" fill="#FFE680"/>
                            <ellipse cx="5" cy="18" rx="2.5" ry="3.5" fill="#FFE680"/>
                            <ellipse cx="5" cy="32" rx="2.2" ry="11" fill="#78520A"/>
                        </svg>
                    </div>
                    <div class="butterfly-wing right">
                        <svg viewBox="0 0 50 60" style="width:100%;height:100%;display:block;overflow:visible;">
                            <path d="M48,28 C45,12 28,1 8,4 C0,12 5,26 22,28 C8,31 3,46 15,54 C28,60 42,48 48,28 Z" fill="url(#globalGoldWing)" fill-opacity="0.96" stroke="#FFE680" stroke-width="0.8" />
                            <path d="M46,28 C32,20 18,12 9,6 M46,28 C30,26 18,24 10,27 M46,28 C32,36 22,46 16,51 M36,18 C26,24 20,27 15,36" stroke="#FFFDF5" stroke-width="1.3" stroke-linecap="round" opacity="0.85" fill="none"/>
                            <circle cx="10" cy="5" r="1.6" fill="#FFF" opacity="0.9"/>
                            <circle cx="20" cy="2" r="1.6" fill="#FFF" opacity="0.9"/>
                            <circle cx="32" cy="5" r="1.6" fill="#FFF" opacity="0.9"/>
                            <circle cx="12" cy="52" r="1.4" fill="#FFF" opacity="0.9"/>
                            <circle cx="22" cy="56" r="1.4" fill="#FFF" opacity="0.9"/>
                        </svg>
                    </div>
                </div>
            `;
            container.appendChild(el);
            return el;
        }

        function createSparkle(x, y) {
            const s = document.createElement('div');
            s.className = 'butterfly-sparkle';
            s.style.left = (x + (Math.random() * 8 - 4)) + 'px';
            s.style.top = (y + (Math.random() * 8 - 4)) + 'px';
            container.appendChild(s);
            setTimeout(() => s.remove(), 1000);
        }

        function getCornerBounds(cornerIndex, w, h) {
            // Límites para mantener el aleteo en las esquinas y no sobre el contenido central
            const maxW = Math.min(w * 0.22, 130);
            const maxH = Math.min(h * 0.20, 130);
            switch (cornerIndex) {
                case 0: // Superior izquierda
                    return { minX: 10, maxX: maxW, minY: 12, maxY: maxH };
                case 1: // Superior derecha
                    return { minX: w - maxW - 35, maxX: w - 45, minY: 12, maxY: maxH };
                case 2: // Inferior izquierda
                    return { minX: 10, maxX: maxW, minY: h - maxH - 35, maxY: h - 45 };
                case 3: // Inferior derecha
                default:
                    return { minX: w - maxW - 35, maxX: w - 45, minY: h - maxH - 35, maxY: h - 45 };
            }
        }

        class Butterfly {
            constructor(cornerIndex) {
                this.cornerIndex = cornerIndex;
                this.size = 26 + Math.random() * 8; // Más pequeñas y delicadas
                this.flapSpeed = (0.17 + Math.random() * 0.08).toFixed(2);
                this.el = createButterflyElement(this.size, this.flapSpeed);
                this.sparkleTimer = Math.floor(Math.random() * 30);
                this.sinOffset = Math.random() * 100;
                this.sinSpeed = 0.04 + Math.random() * 0.03;
                this.speed = 1.0 + Math.random() * 0.8;
                this.vx = 0;
                this.vy = 0;
                this.angle = 0;

                const w = Math.max(window.innerWidth, 360);
                const h = Math.max(window.innerHeight, 600);
                const bounds = getCornerBounds(this.cornerIndex, w, h);

                this.x = bounds.minX + Math.random() * Math.max(10, bounds.maxX - bounds.minX);
                this.y = bounds.minY + Math.random() * Math.max(10, bounds.maxY - bounds.minY);
                this.pickNewTarget();
            }

            pickNewTarget() {
                const w = Math.max(window.innerWidth, 360);
                const h = Math.max(window.innerHeight, 600);

                // 85% del tiempo permanece en su propia esquina; 15% recorre el borde hacia una esquina contigua
                if (Math.random() < 0.15) {
                    const adjacent = {
                        0: [1, 2],
                        1: [0, 3],
                        2: [0, 3],
                        3: [1, 2]
                    };
                    const choices = adjacent[this.cornerIndex] || [0];
                    this.cornerIndex = choices[Math.floor(Math.random() * choices.length)];
                }

                const bounds = getCornerBounds(this.cornerIndex, w, h);
                this.targetX = bounds.minX + Math.random() * Math.max(10, bounds.maxX - bounds.minX);
                this.targetY = bounds.minY + Math.random() * Math.max(10, bounds.maxY - bounds.minY);
            }

            update() {
                const w = Math.max(window.innerWidth, 360);
                const h = Math.max(window.innerHeight, 600);

                const dx = this.targetX - this.x;
                const dy = this.targetY - this.y;
                const dist = Math.hypot(dx, dy);

                if (dist < 45 || Math.random() < 0.01) {
                    this.pickNewTarget();
                }

                const targetAngle = Math.atan2(dy, dx);
                const desiredVx = Math.cos(targetAngle) * this.speed;
                const desiredVy = Math.sin(targetAngle) * this.speed;

                // Suave aceleración hacia el destino
                this.vx += (desiredVx - this.vx) * 0.05;
                this.vy += (desiredVy - this.vy) * 0.05;

                // Repulsión activa del centro de lectura de la tarjeta
                const centerMinX = w * 0.22;
                const centerMaxX = w * 0.78;
                const centerMinY = h * 0.16;
                const centerMaxY = h * 0.84;

                if (this.x > centerMinX && this.x < centerMaxX && this.y > centerMinY && this.y < centerMaxY) {
                    const pushX = (this.x < w / 2) ? -1.6 : 1.6;
                    const pushY = (this.y < h / 2) ? -1.6 : 1.6;
                    this.vx += pushX * 0.12;
                    this.vy += pushY * 0.12;
                    this.pickNewTarget();
                }

                this.sinOffset += this.sinSpeed;
                const wobble = Math.sin(this.sinOffset) * 1.2;

                this.x += this.vx;
                this.y += this.vy + wobble;

                // Mantener dentro de los bordes visibles
                if (this.x < 8) { this.x = 8; this.vx *= -0.5; this.pickNewTarget(); }
                if (this.x > w - this.size - 8) { this.x = w - this.size - 8; this.vx *= -0.5; this.pickNewTarget(); }
                if (this.y < 8) { this.y = 8; this.vy *= -0.5; this.pickNewTarget(); }
                if (this.y > h - this.size - 8) { this.y = h - this.size - 8; this.vy *= -0.5; this.pickNewTarget(); }

                // Orientación de vuelo suave
                const heading = Math.atan2(this.vy + wobble, this.vx) * (180 / Math.PI) + 90;
                this.angle += (heading - this.angle) * 0.12;

                this.el.style.transform = `translate3d(${this.x.toFixed(1)}px, ${this.y.toFixed(1)}px, 0) rotate(${this.angle.toFixed(1)}deg)`;

                // Destellos dorados suaves espaciados
                this.sparkleTimer++;
                if (this.sparkleTimer > 35) {
                    this.sparkleTimer = 0;
                    createSparkle(this.x + this.size * 0.4, this.y + this.size * 0.4);
                }

                return true;
            }
        }

        // Crear únicamente 4 mariposas (una en cada esquina)
        for (let i = 0; i < BUTTERFLY_COUNT; i++) {
            butterflies.push(new Butterfly(i));
        }

        // Bucle de animación a 60 FPS
        function animate() {
            for (let i = 0; i < butterflies.length; i++) {
                butterflies[i].update();
            }
            requestAnimationFrame(animate);
        }
        requestAnimationFrame(animate);
    })();

    /* ================================================================
       UTILITY
       ================================================================ */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /* ================================================================
       GALERÍA DE FOTOS EN VIVO (LIVE PARTY ALBUM LOGIC)
       ================================================================ */
    (() => {
        let livePhotos = [];
        let likedPhotoIds = new Set();
        let selectedFileBase64 = null;
        let selectedPhotoForLightbox = null;

        const btnOpenPhotoUpload = document.getElementById('btnOpenPhotoUpload');
        const photoUploadModal = document.getElementById('photoUploadModal');
        const btnCloseUploadModal = document.getElementById('btnCloseUploadModal');
        const filePhotoInput = document.getElementById('filePhotoInput');
        const uploadPreviewWrap = document.getElementById('uploadPreviewWrap');
        const imgUploadPreview = document.getElementById('imgUploadPreview');
        const uploadGuestName = document.getElementById('uploadGuestName');
        const uploadGuestMsg = document.getElementById('uploadGuestMsg');
        const btnSubmitPhoto = document.getElementById('btnSubmitPhoto');
        const uploadStatusMsg = document.getElementById('uploadStatusMsg');

        const liveGalleryGrid = document.getElementById('liveGalleryGrid');
        const liveGalleryEmpty = document.getElementById('liveGalleryEmpty');
        const liveGalleryCount = document.getElementById('liveGalleryCount');
        const btnRefreshGallery = document.getElementById('btnRefreshGallery');

        const photoLightbox = document.getElementById('photoLightbox');
        const btnCloseLightbox = document.getElementById('btnCloseLightbox');
        const lightboxImg = document.getElementById('lightboxImg');
        const lightboxAuthor = document.getElementById('lightboxAuthor');
        const lightboxMsg = document.getElementById('lightboxMsg');
        const btnLikePhoto = document.getElementById('btnLikePhoto');
        const lightboxLikesCount = document.getElementById('lightboxLikesCount');

        // Pre-llenar el nombre si el usuario ya lo escribió en el formulario RSVP
        function getStoredGuestName() {
            const rsvpInput = document.getElementById('rsvp-nombre');
            if (rsvpInput && rsvpInput.value.trim()) {
                return rsvpInput.value.trim();
            }
            try {
                return localStorage.getItem('angie_rsvp_name') || '';
            } catch (e) {
                return '';
            }
        }

        // Abrir modal de subida
        btnOpenPhotoUpload?.addEventListener('click', () => {
            if (!uploadGuestName.value.trim()) {
                uploadGuestName.value = getStoredGuestName();
            }
            uploadStatusMsg.textContent = '';
            filePhotoInput.click();
        });

        // Al seleccionar archivo con la cámara o galería del celular
        filePhotoInput?.addEventListener('change', (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            // Compresión del lado del cliente vía Canvas HTML5 (8-12MP a ~1400px JPEG de 350KB)
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const maxDimension = 1400;

                    if (width > height && width > maxDimension) {
                        height = Math.round((height * maxDimension) / width);
                        width = maxDimension;
                    } else if (height > maxDimension) {
                        width = Math.round((width * maxDimension) / height);
                        height = maxDimension;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    selectedFileBase64 = canvas.toDataURL('image/jpeg', 0.85);
                    imgUploadPreview.src = selectedFileBase64;
                    uploadPreviewWrap.style.display = 'flex';
                    photoUploadModal.style.display = 'flex';
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });

        // Cerrar modal de subida
        btnCloseUploadModal?.addEventListener('click', () => {
            photoUploadModal.style.display = 'none';
            selectedFileBase64 = null;
            filePhotoInput.value = '';
        });

        // Enviar foto
        btnSubmitPhoto?.addEventListener('click', async () => {
            if (!selectedFileBase64) {
                uploadStatusMsg.textContent = 'Por favor selecciona una foto primero.';
                uploadStatusMsg.style.color = '#ff9999';
                return;
            }

            const nombre = uploadGuestName.value.trim() || 'Invitado Especial';
            const mensaje = uploadGuestMsg.value.trim();

            btnSubmitPhoto.disabled = true;
            btnSubmitPhoto.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
            uploadStatusMsg.textContent = 'Enviando foto a la fiesta...';
            uploadStatusMsg.style.color = '#FFE680';

            try {
                // Guardar nombre en localStorage para próximas fotos
                try { localStorage.setItem('angie_rsvp_name', nombre); } catch(e){}

                const formData = new FormData();
                formData.append('nombre_invitado', nombre);
                formData.append('mensaje', mensaje);
                formData.append('foto_base64', selectedFileBase64);

                const res = await fetch('api/fotos.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();
                if (data && data.success) {
                    uploadStatusMsg.textContent = '✨ ¡Foto compartida con éxito!';
                    uploadStatusMsg.style.color = '#A7F3D0';

                    setTimeout(() => {
                        photoUploadModal.style.display = 'none';
                        selectedFileBase64 = null;
                        filePhotoInput.value = '';
                        uploadGuestMsg.value = '';
                        btnSubmitPhoto.disabled = false;
                        btnSubmitPhoto.innerHTML = '<i class="fas fa-paper-plane"></i> Publicar en el Álbum';
                        fetchLivePhotos();
                    }, 800);
                } else {
                    throw new Error(data.error || 'Error al subir la foto');
                }
            } catch (err) {
                btnSubmitPhoto.disabled = false;
                btnSubmitPhoto.innerHTML = '<i class="fas fa-paper-plane"></i> Intentar de Nuevo';
                uploadStatusMsg.textContent = 'Error: ' + err.message;
                uploadStatusMsg.style.color = '#ff9999';
            }
        });

        // Obtener fotos desde la API
        async function fetchLivePhotos() {
            try {
                const res = await fetch('api/fotos.php?limit=60&t=' + Date.now());
                const data = await res.json();
                if (data && data.success && Array.isArray(data.fotos)) {
                    livePhotos = data.fotos;
                    renderLiveGallery();
                }
            } catch (err) {
                console.warn('Error al cargar fotos:', err);
            }
        }

        // Renderizar el mosaico de la galería
        function renderLiveGallery() {
            liveGalleryCount.textContent = livePhotos.length;

            if (livePhotos.length === 0) {
                liveGalleryGrid.innerHTML = `
                    <div class="live-gallery-empty" id="liveGalleryEmpty">
                        <i class="fas fa-images"></i>
                        Aún no hay fotos. ¡Toca el botón arriba y sé el primero en subir un recuerdo!
                    </div>
                `;
                return;
            }

            liveGalleryGrid.innerHTML = '';
            livePhotos.forEach((photo) => {
                const item = document.createElement('div');
                item.className = 'live-gallery-item';
                item.innerHTML = `
                    <img src="${photo.url}" alt="${photo.nombre_invitado}" loading="lazy">
                    <div class="live-item-badge">
                        <i class="fas fa-heart" style="color: #ef4444;"></i> ${photo.likes || 0}
                    </div>
                `;
                item.addEventListener('click', () => openLightbox(photo));
                liveGalleryGrid.appendChild(item);
            });
        }

        const btnDownloadLightboxPhoto = document.getElementById('btnDownloadLightboxPhoto');

        // Abrir visor a pantalla completa
        function openLightbox(photo) {
            selectedPhotoForLightbox = photo;
            lightboxImg.src = photo.url;
            lightboxAuthor.textContent = photo.nombre_invitado || 'Invitado Especial';
            lightboxMsg.textContent = photo.mensaje ? `"${photo.mensaje}"` : '✨ ¡Celebrando los XV de Angie Karolina!';
            lightboxLikesCount.textContent = photo.likes || 0;

            if (btnDownloadLightboxPhoto) {
                const downloadUrl = photo.url + (photo.url.includes('?') ? '&' : '?') + 'download=1';
                btnDownloadLightboxPhoto.href = downloadUrl;
                btnDownloadLightboxPhoto.download = (photo.archivo || 'foto_angie_xv.jpg');
            }

            const isLiked = likedPhotoIds.has(photo.id);
            btnLikePhoto.classList.toggle('liked', isLiked);

            photoLightbox.style.display = 'flex';
        }

        // Cerrar visor
        btnCloseLightbox?.addEventListener('click', () => {
            photoLightbox.style.display = 'none';
            selectedPhotoForLightbox = null;
        });

        // Like a la foto
        btnLikePhoto?.addEventListener('click', async () => {
            if (!selectedPhotoForLightbox) return;
            const id = selectedPhotoForLightbox.id;
            if (likedPhotoIds.has(id)) return;

            likedPhotoIds.add(id);
            btnLikePhoto.classList.add('liked');

            try {
                const res = await fetch('api/fotos.php?action=like', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data && data.success) {
                    selectedPhotoForLightbox.likes = data.likes;
                    lightboxLikesCount.textContent = data.likes;
                    // Actualizar en el arreglo local
                    const p = livePhotos.find(x => x.id === id);
                    if (p) p.likes = data.likes;
                    renderLiveGallery();
                }
            } catch (err) {
                console.warn('Error al dar like:', err);
            }
        });

        // Botón refrescar
        btnRefreshGallery?.addEventListener('click', () => {
            const icon = btnRefreshGallery.querySelector('i');
            if (icon) icon.classList.add('fa-spin');
            fetchLivePhotos().finally(() => {
                setTimeout(() => { if (icon) icon.classList.remove('fa-spin'); }, 500);
            });
        });

        // Cargar fotos al inicio y refrescar periódicamente cada 25 segundos
        fetchLivePhotos();
        setInterval(fetchLivePhotos, 25000);
    })();
    </script>
</body>
</html>
