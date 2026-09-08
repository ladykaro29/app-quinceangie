<?php
/**
 * Pantalla de Presentación en Vivo para Proyector / TV
 * Invitación XV Años - Angie Karolina
 * 
 * Modo presentación a pantalla completa con actualización en tiempo real
 * Diseñado para proyectar en la fiesta mientras los invitados suben fotos.
 */

require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📸 En Vivo · Los XV de Angie Karolina</title>
    <!-- Favicon Corona Real -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='30' fill='%23062E25' stroke='%23C8A24A' stroke-width='2.5'/%3E%3Cpath d='M13 43 L17 24 L24 33 L32 15 L40 33 L47 24 L51 43 Z' fill='%23FFD700' stroke='%23FFF0BA' stroke-width='0.8'/%3E%3Cpath d='M13 43 Q32 47 51 43 L51 47 Q32 51 13 47 Z' fill='%23C8A24A'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Great+Vibes&family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --verde-profundo: #041B16;
            --verde-esmeralda: #062E25;
            --verde-medio: #0A4337;
            --dorado: #C8A24A;
            --dorado-claro: #E8D390;
            --dorado-brillante: #FFD700;
            --blanco: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--verde-profundo);
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(200, 162, 74, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 85% 80%, rgba(10, 67, 55, 0.5) 0%, transparent 50%),
                linear-gradient(135deg, #02110E 0%, #062E25 50%, #031713 100%);
            color: var(--blanco);
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Barra Superior */
        .live-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 28px;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(14px);
            border-bottom: 1.5px solid rgba(200, 162, 74, 0.4);
            z-index: 25;
            position: relative;
        }

        .brand-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-crown {
            width: 44px;
            height: 44px;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.7));
            animation: pulse 3s ease-in-out infinite;
            flex-shrink: 0;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.06); }
        }

        .brand-text h1 {
            font-family: 'Great Vibes', cursive;
            font-size: 2.2rem;
            color: var(--dorado-brillante);
            line-height: 1;
            text-shadow: 0 0 15px rgba(200, 162, 74, 0.6);
        }

        .brand-text span {
            font-family: 'Cinzel Decorative', serif;
            font-size: 0.78rem;
            letter-spacing: 2.5px;
            color: var(--dorado-claro);
            text-transform: uppercase;
            display: block;
            margin-top: 2px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .live-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(220, 38, 38, 0.25);
            border: 1.5px solid #ef4444;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 2px;
            color: #fca5a5;
            text-transform: uppercase;
        }

        .live-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 10px #ef4444;
            animation: blink 1.2s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.7); }
        }

        /* Escenario Central */
        .stage-container {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px 20px;
            overflow: hidden;
            gap: 24px;
        }

        .photo-card {
            max-width: 800px;
            width: 100%;
            max-height: calc(100vh - 160px);
            background: rgba(6, 46, 37, 0.82);
            border: 2px solid var(--dorado);
            border-radius: 22px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.85), 0 0 40px rgba(200, 162, 74, 0.25);
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            backdrop-filter: blur(16px);
            transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            z-index: 10;
        }

        .photo-img-wrap {
            width: 100%;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 14px;
            background: #000;
            max-height: 56vh;
            border: 1px solid rgba(200, 162, 74, 0.25);
        }

        .photo-img-wrap img {
            max-width: 100%;
            max-height: 55vh;
            object-fit: contain;
            border-radius: 10px;
            transition: transform 0.5s ease;
        }

        .photo-meta {
            width: 100%;
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(200, 162, 74, 0.35);
            padding-top: 10px;
            gap: 12px;
        }

        .guest-info {
            text-align: left;
            flex: 1;
            min-width: 0;
        }

        .guest-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: var(--dorado-brillante);
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .guest-msg {
            font-size: 0.92rem;
            color: #FFFDF5;
            font-style: italic;
            margin-top: 2px;
            opacity: 0.92;
            word-break: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .likes-pill {
            display: flex;
            align-items: center;
            gap: 7px;
            background: rgba(200, 162, 74, 0.2);
            border: 1px solid var(--dorado);
            padding: 5px 14px;
            border-radius: 20px;
            color: var(--dorado-brillante);
            font-weight: 700;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        /* QR para invitar a subir fotos (Elegante y no invasivo) */
        .qr-floating {
            position: absolute;
            bottom: 20px;
            right: 24px;
            background: rgba(4, 27, 22, 0.92);
            border: 2px solid var(--dorado);
            border-radius: 18px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.75), 0 0 20px rgba(200, 162, 74, 0.25);
            backdrop-filter: blur(12px);
            z-index: 30;
            transition: all 0.3s ease;
        }

        .qr-floating img {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            background: #fff;
            padding: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }

        .qr-text {
            font-size: 0.68rem;
            color: var(--dorado-claro);
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            line-height: 1.25;
        }

        /* Adaptación responsiva especial para móviles y pantallas verticales */
        @media (max-width: 768px) {
            .live-header {
                padding: 10px 14px;
                flex-wrap: wrap;
                gap: 8px;
            }

            .brand-text h1 {
                font-size: 1.6rem;
            }

            .brand-text span {
                font-size: 0.65rem;
                letter-spacing: 1.5px;
            }

            .brand-crown {
                width: 34px;
                height: 34px;
            }

            .live-badge {
                padding: 4px 10px;
                font-size: 0.72rem;
                letter-spacing: 1px;
            }

            .stage-container {
                padding: 10px;
                flex-direction: column;
                justify-content: flex-start;
                gap: 12px;
                overflow-y: auto;
            }

            .photo-card {
                width: 100%;
                max-width: 100%;
                max-height: none;
                padding: 12px;
                margin-bottom: 95px; /* Espacio reservado para que el QR flotante nunca tape el texto ni los likes */
            }

            .photo-img-wrap {
                max-height: 48vh;
            }

            .photo-img-wrap img {
                max-height: 47vh;
            }

            .guest-name {
                font-size: 1.15rem;
            }

            .guest-msg {
                font-size: 0.85rem;
            }

            .qr-floating {
                position: fixed;
                bottom: 12px;
                right: 12px;
                padding: 8px 10px;
                border-radius: 14px;
                flex-direction: row;
                gap: 10px;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.85);
            }

            .qr-floating img {
                width: 60px;
                height: 60px;
                padding: 3px;
            }

            .qr-text {
                font-size: 0.65rem;
                text-align: left;
                line-height: 1.3;
            }
        }

        /* Estado vacío */
        .empty-display {
            text-align: center;
            padding: 40px;
        }

        .empty-display i {
            font-size: 4rem;
            color: var(--dorado);
            margin-bottom: 20px;
            opacity: 0.8;
            animation: pulse 2s infinite;
        }

        .empty-display h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--dorado-claro);
            margin-bottom: 10px;
        }

        .empty-display p {
            font-size: 1.1rem;
            color: #ddd;
            max-width: 500px;
            margin: 0 auto;
        }

        /* Botón de pantalla completa */
        .fullscreen-btn {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid var(--dorado);
            color: var(--dorado);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 30;
            transition: all 0.3s;
        }

        .fullscreen-btn:hover {
            background: var(--dorado);
            color: #062E25;
            transform: scale(1.1);
        }
    </style>
</head>
<body>

    <header class="live-header">
        <div class="brand-title">
            <svg class="brand-crown" viewBox="0 0 64 64">
                <circle cx="32" cy="32" r="30" fill="#062E25" stroke="#C8A24A" stroke-width="2.5"/>
                <path d="M13 43 L17 24 L24 33 L32 15 L40 33 L47 24 L51 43 Z" fill="#FFD700" stroke="#FFF0BA" stroke-width="0.8"/>
                <path d="M13 43 Q32 47 51 43 L51 47 Q32 51 13 47 Z" fill="#C8A24A"/>
                <circle cx="32" cy="14" r="3.6" fill="#FFFDF5"/>
                <polygon points="32,25 36,32 32,39 28,32" fill="#006B4F" stroke="#FFF0BA" stroke-width="0.8"/>
            </svg>
            <div class="brand-text">
                <h1>Angie Karolina</h1>
                <span>Galería de Fotos en Vivo · Mis XV Años</span>
            </div>
        </div>

        <div class="header-actions">
            <div class="live-badge">
                <span class="live-dot"></span>
                EN VIVO
            </div>
            <button class="fullscreen-btn" id="btnFs" title="Pantalla Completa">
                <i class="fas fa-expand"></i>
            </button>
        </div>
    </header>

    <main class="stage-container" id="stage">
        <div class="empty-display" id="emptyView">
            <i class="fas fa-camera-retro"></i>
            <h2>¡Esperando los primeros momentos!</h2>
            <p>Escanea el código QR en la pantalla con tu celular y sé el primero en subir una foto a la fiesta de Angie.</p>
        </div>

        <div class="photo-card" id="photoCard" style="display: none;">
            <div class="photo-img-wrap">
                <img id="currentImg" src="" alt="Momento en vivo">
            </div>
            <div class="photo-meta">
                <div class="guest-info">
                    <div class="guest-name" id="currentGuest">Invitado</div>
                    <div class="guest-msg" id="currentMsg">"¡Felicidades Angie!"</div>
                </div>
                <div class="likes-pill">
                    <i class="fas fa-heart" style="color: #ef4444;"></i>
                    <span id="currentLikes">0</span>
                </div>
            </div>
        </div>

        <div class="qr-floating">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/#slide-11") ?>&color=062E25&bgcolor=FFFFFF" alt="QR para subir fotos">
            <span class="qr-text">📸 Sube tu foto<br>aquí con tu celular</span>
        </div>
    </main>

    <script>
    (() => {
        let fotos = [];
        let currentIndex = -1;
        let slideTimer = null;

        const photoCard = document.getElementById('photoCard');
        const emptyView = document.getElementById('emptyView');
        const currentImg = document.getElementById('currentImg');
        const currentGuest = document.getElementById('currentGuest');
        const currentMsg = document.getElementById('currentMsg');
        const currentLikes = document.getElementById('currentLikes');
        const btnFs = document.getElementById('btnFs');

        async function fetchFotos() {
            try {
                const res = await fetch('api/fotos.php?limit=80&t=' + Date.now());
                const data = await res.json();
                if (data && data.success && Array.isArray(data.fotos)) {
                    const wasEmpty = fotos.length === 0;
                    fotos = data.fotos;

                    if (fotos.length > 0) {
                        emptyView.style.display = 'none';
                        photoCard.style.display = 'flex';
                        if (wasEmpty || currentIndex < 0) {
                            showNextPhoto();
                        }
                    } else {
                        emptyView.style.display = 'block';
                        photoCard.style.display = 'none';
                    }
                }
            } catch (err) {
                console.warn('Error sincronizando fotos en vivo:', err);
            }
        }

        function showNextPhoto() {
            if (fotos.length === 0) return;
            currentIndex = (currentIndex + 1) % fotos.length;
            const f = fotos[currentIndex];

            // Animación suave de cambio
            photoCard.style.opacity = '0';
            photoCard.style.transform = 'scale(0.96)';

            setTimeout(() => {
                currentImg.src = f.url;
                currentGuest.textContent = f.nombre_invitado || 'Invitado Especial';
                currentMsg.textContent = f.mensaje ? `"${f.mensaje}"` : '✨ ¡Celebrando los XV Años de Angie!';
                currentLikes.textContent = f.likes || 0;

                photoCard.style.opacity = '1';
                photoCard.style.transform = 'scale(1)';
            }, 400);
        }

        // Cambiar diapositiva cada 7 segundos
        slideTimer = setInterval(showNextPhoto, 7000);

        // Consultar nuevas fotos del servidor cada 10 segundos
        setInterval(fetchFotos, 10000);

        // Carga inicial
        fetchFotos();

        // Botón Pantalla Completa
        btnFs.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
                btnFs.innerHTML = '<i class="fas fa-compress"></i>';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    btnFs.innerHTML = '<i class="fas fa-expand"></i>';
                }
            }
        });
    })();
    </script>
</body>
</html>
