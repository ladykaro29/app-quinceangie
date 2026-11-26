<?php
/**
 * ==============================================================================
 * XV EXPERIENCE — PROPUESTA VISUAL OFICIAL: "EL JARDÍN ENCANTADO DE SOPHIA"
 * Maqueta de Evaluación para las Primeras 5 Tarjetas
 * Celebración: Sophia Valentina Rivas Sánchez (21 de Diciembre de 2026)
 * ==============================================================================
 */

require_once __DIR__ . '/../app/autoload.php';

use App\Core\CurrentEventContext;
use App\Models\Evento;

// Establecer contexto para Sophia Valentina (ID 2 o slug sophia-xv)
$sophiaEvent = Evento::findBySlug('sophia-xv') ?? Evento::find(2);
if ($sophiaEvent) {
    CurrentEventContext::set($sophiaEvent);
}

$portadaUrl = 'sophia-portada.webp';
if (!file_exists(__DIR__ . '/' . $portadaUrl)) {
    $portadaUrl = 'sophia-portada.jpg';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propuesta Visual Oficial (5 Tarjetas) — Sophia Valentina | XV Experience</title>
    
    <!-- Tipografías -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Iconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tema Oficial Sophia -->
    <link rel="stylesheet" href="assets/css/theme-sophia-jardin.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #1F1017;
            color: #FFF3E7;
            padding: 20px 10px 60px 10px;
            min-height: 100vh;
        }

        .header-showcase {
            max-width: 900px;
            margin: 0 auto 25px auto;
            text-align: center;
            background: rgba(56, 33, 40, 0.7);
            border: 1px solid #C5A16A;
            border-radius: 20px;
            padding: 24px 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .badge-concept {
            display: inline-block;
            background: rgba(197, 161, 106, 0.2);
            color: #EAD8B8;
            border: 1px solid #C5A16A;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .header-title {
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            color: #FFF3E7;
            text-shadow: 0 2px 8px rgba(197, 161, 106, 0.5);
            line-height: 1.1;
        }

        .header-sub {
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            color: #C5A16A;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .palette-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 16px;
        }

        .swatch {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.15);
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .color-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.5);
        }

        /* Vistas de las 5 Tarjetas */
        .cards-grid {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            justify-items: center;
            align-items: start;
        }

        .card-preview-col {
            width: 100%;
            max-width: 380px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card-number-badge {
            background: linear-gradient(135deg, #C5A16A, #EAD8B8);
            color: #382128;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 6px 18px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            box-shadow: 0 4px 15px rgba(197, 161, 106, 0.4);
        }

        /* Estilos del Card Frame simulado */
        .device-card-frame {
            width: 100%;
            height: 640px;
            overflow-y: auto;
            border-radius: 28px;
            padding: 30px 20px 40px 20px;
            box-sizing: border-box;
            position: relative;
            scrollbar-width: thin;
            scrollbar-color: #C5A16A transparent;
        }

        /* Componentes Específicos */
        .tiara-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .tiara-icon {
            font-size: 26px;
            color: #C5A16A;
            filter: drop-shadow(0 2px 6px rgba(197, 161, 106, 0.5));
        }

        .cover-photo-wrapper {
            position: relative;
            width: 100%;
            max-width: 290px;
            margin: 0 auto 16px auto;
            border-radius: 22px;
            padding: 4px;
            background: linear-gradient(145deg, #C5A16A, #FFF3E7, #C5A16A);
            box-shadow: 0 10px 25px rgba(56, 33, 40, 0.35);
        }

        .cover-photo-wrapper img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 18px;
        }

        .cover-announcement {
            text-align: center;
            font-size: 0.72rem;
            letter-spacing: 3px;
            color: #C5A16A;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .cover-name-big {
            text-align: center;
            font-family: 'Great Vibes', cursive;
            font-size: 2.8rem;
            color: #79544D;
            line-height: 1;
            margin-bottom: 6px;
            text-shadow: 0 1px 2px rgba(197, 161, 106, 0.3);
        }

        .cover-xv-text {
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #C5A16A;
            letter-spacing: 6px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .cover-date-text {
            text-align: center;
            font-size: 0.82rem;
            letter-spacing: 2px;
            color: #79544D;
            font-weight: 500;
        }

        /* SVG Butterfly Reutilizable */
        .butterfly-svg-elem {
            position: absolute;
            width: 44px;
            height: 44px;
            pointer-events: none;
            z-index: 10;
        }

        .bottom-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(31, 16, 23, 0.95);
            border-top: 1px solid #C5A16A;
            padding: 12px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            backdrop-filter: blur(10px);
            z-index: 999;
        }

        .btn-direct-link {
            background: linear-gradient(135deg, #C5A16A, #EAD8B8);
            color: #382128;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 12px rgba(197, 161, 106, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body class="theme-sophia-jardin">

    <!-- Header de Presentación de la Maqueta -->
    <header class="header-showcase">
        <div class="badge-concept">Dirección Artística Oficial</div>
        <h1 class="header-title">El Jardín Encantado de Sophia</h1>
        <div class="header-sub">Propuesta de las Primeras 5 Tarjetas — Sophia Valentina</div>
        
        <div class="palette-bar">
            <div class="swatch"><div class="color-dot" style="background:#EBC5CC;"></div> Rosa Blush (#EBC5CC)</div>
            <div class="swatch"><div class="color-dot" style="background:#D9A5B5;"></div> Rosa Empolvado (#D9A5B5)</div>
            <div class="swatch"><div class="color-dot" style="background:#C6AEDB;"></div> Lavanda (#C6AEDB)</div>
            <div class="swatch"><div class="color-dot" style="background:#C5A16A;"></div> Dorado Champagne (#C5A16A)</div>
            <div class="swatch"><div class="color-dot" style="background:#FFF3E7;"></div> Marfil Cálido (#FFF3E7)</div>
            <div class="swatch"><div class="color-dot" style="background:#79544D;"></div> Marrón Rosado (#79544D)</div>
        </div>
    </header>

    <!-- Contenedor de las 5 Tarjetas en Detalle -->
    <main class="cards-grid">

        <!-- =========================================================================
             TARJETA 1: PORTADA
             ========================================================================= -->
        <div class="card-preview-col">
            <div class="card-number-badge">1. Portada Oficial</div>
            <div class="device-card-frame sophia-card">
                <!-- Mariposa 3D Animada Esquina Superior Derecha -->
                <div class="sophia-butterfly sophia-butterfly-float-1">
                    <svg viewBox="0 0 100 100" class="butterfly-icon">
                        <defs>
                            <linearGradient id="bfWing1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#EBC5CC" />
                                <stop offset="50%" stop-color="#D9A5B5" />
                                <stop offset="100%" stop-color="#C6AEDB" />
                            </linearGradient>
                        </defs>
                        <path class="wing-left" d="M 50 50 C 20 10, 0 35, 10 65 C 20 85, 45 65, 50 50 Z" fill="url(#bfWing1)" stroke="#C5A16A" stroke-width="1.8" />
                        <path class="wing-right" d="M 50 50 C 80 10, 100 35, 90 65 C 80 85, 55 65, 50 50 Z" fill="url(#bfWing1)" stroke="#C5A16A" stroke-width="1.8" />
                        <ellipse cx="50" cy="50" rx="3" ry="14" fill="#C5A16A" />
                    </svg>
                </div>

                <div class="tiara-container">
                    <i class="fas fa-crown tiara-icon"></i>
                </div>

                <!-- Marco con la foto oficial de portada -->
                <div class="cover-photo-wrapper">
                    <img src="<?= htmlspecialchars($portadaUrl) ?>" alt="Sophia Valentina - Mis XV Años" loading="eager">
                </div>

                <div class="cover-announcement">Te invito a celebrar</div>
                <div class="cover-name-big">Sophia Valentina</div>
                <div class="sophia-divider"></div>
                <div class="cover-xv-text">XV AÑOS</div>
                <div class="cover-date-text">21 · DICIEMBRE · 2026</div>

                <div style="text-align: center; margin-top: 18px; color: #C5A16A; font-size: 0.78rem;">
                    <i class="fas fa-chevron-down" style="animation: bounce 1.8s infinite;"></i><br>
                    <span>Desliza para descubrir</span>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             TARJETA 2: TARJETA FAMILIAR (PADRES Y BENDICIÓN)
             ========================================================================= -->
        <div class="card-preview-col">
            <div class="card-number-badge">2. Tarjeta Familiar</div>
            <div class="device-card-frame sophia-card">
                <!-- Mariposa 3D Animada Esquina Inferior Izquierda -->
                <div class="sophia-butterfly sophia-butterfly-float-2">
                    <svg viewBox="0 0 100 100">
                        <path class="wing-left" d="M 50 50 C 20 10, 0 35, 10 65 C 20 85, 45 65, 50 50 Z" fill="#EBC5CC" stroke="#C5A16A" stroke-width="1.8" />
                        <path class="wing-right" d="M 50 50 C 80 10, 100 35, 90 65 C 80 85, 55 65, 50 50 Z" fill="#D9A5B5" stroke="#C5A16A" stroke-width="1.8" />
                        <ellipse cx="50" cy="50" rx="3" ry="14" fill="#C5A16A" />
                    </svg>
                </div>

                <div style="text-align: center; margin-bottom: 8px;">
                    <i class="fas fa-heart-pulse sophia-icon" style="font-size: 26px;"></i>
                </div>
                <div class="sophia-title-script" style="font-size: 2.4rem; text-align: center;">Con la bendición de Dios</div>
                <div class="sophia-title-serif" style="font-size: 0.75rem; text-align: center;">Y EL AMOR DE MI FAMILIA</div>
                <div class="sophia-divider"></div>

                <div class="sophia-blessing-quote" style="text-align: center;">
                    "Como una mariposa que despliega sus alas al viento, hoy comienzo a escribir el más hermoso capítulo de mi historia..."
                </div>

                <div class="sophia-family-badge" style="text-align: center;">
                    <div style="font-size: 0.75rem; letter-spacing: 2px; color: #C5A16A; text-transform: uppercase; font-weight: 600;">Mis Amados Padres</div>
                    <div class="sophia-parents-names">Ramón Eduardo Rivas Avendaño</div>
                    <div style="font-size: 0.8rem; color: #C5A16A;">&</div>
                    <div class="sophia-parents-names">Estefany Yamileth Sánchez Ángulo</div>
                    <p style="font-size: 0.82rem; color: #79544D; margin-top: 8px;">
                        Tienen el inmenso honor de invitarte a compartir la alegría de ver a nuestra amada hija cumplir sus quince años.
                    </p>
                </div>

                <div style="text-align: center; font-size: 0.82rem; color: #79544D; margin-top: 14px;">
                    ✨ Tu presencia iluminará nuestro jardín de ensueño.
                </div>
            </div>
        </div>

        <!-- =========================================================================
             TARJETA 3: CUENTA REGRESIVA
             ========================================================================= -->
        <div class="card-preview-col">
            <div class="card-number-badge">3. Cuenta Regresiva</div>
            <div class="device-card-frame sophia-card">
                <div style="text-align: center; margin-bottom: 8px;">
                    <i class="fas fa-hourglass-half sophia-icon" style="font-size: 26px;"></i>
                </div>
                <div class="sophia-title-script" style="font-size: 2.4rem; text-align: center;">Cuenta Regresiva</div>
                <div class="sophia-title-serif" style="font-size: 0.75rem; text-align: center;">CADA SEGUNDO CUENTA</div>
                <div class="sophia-divider"></div>

                <p style="text-align: center; font-size: 0.85rem; color: #79544D; margin-bottom: 15px;">
                    Faltan muy pocos instantes para encender las luces de nuestra noche mágica:
                </p>

                <!-- Grid de Cuenta Regresiva Activa -->
                <div class="sophia-countdown-grid">
                    <div class="sophia-countdown-box">
                        <div class="sophia-countdown-num" id="demo-days">390</div>
                        <div class="sophia-countdown-lbl">Días</div>
                    </div>
                    <div class="sophia-countdown-box">
                        <div class="sophia-countdown-num" id="demo-hours">21</div>
                        <div class="sophia-countdown-lbl">Horas</div>
                    </div>
                    <div class="sophia-countdown-box">
                        <div class="sophia-countdown-num" id="demo-minutes">30</div>
                        <div class="sophia-countdown-lbl">Min</div>
                    </div>
                    <div class="sophia-countdown-box">
                        <div class="sophia-countdown-num" id="demo-seconds">00</div>
                        <div class="sophia-countdown-lbl">Seg</div>
                    </div>
                </div>

                <div style="text-align: center; font-size: 0.85rem; color: #79544D; margin: 18px 0 20px 0;">
                    🌸 21 de Diciembre de 2026 · 7:30 PM 🌸
                </div>

                <div style="text-align: center;">
                    <a href="#" class="sophia-btn-gold" style="font-size: 0.78rem;">
                        <i class="fab fa-google"></i>
                        <span>Agendar en Google Calendar</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             TARJETA 4: RECEPCIÓN Y LUGAR (NOVA ROMA)
             ========================================================================= -->
        <div class="card-preview-col">
            <div class="card-number-badge">4. Recepción y Lugar</div>
            <div class="device-card-frame sophia-card">
                <div style="text-align: center; margin-bottom: 8px;">
                    <i class="fas fa-location-dot sophia-icon" style="font-size: 26px;"></i>
                </div>
                <div class="sophia-title-script" style="font-size: 2.4rem; text-align: center;">Lugar y Recepción</div>
                <div class="sophia-title-serif" style="font-size: 0.75rem; text-align: center;">DONDE LOS SUEÑOS SE HACEN REALIDAD</div>
                <div class="sophia-divider"></div>

                <div class="sophia-info-card">
                    <div class="sophia-info-title">📅 Fecha de Gala</div>
                    <div class="sophia-info-val">Lunes, 21 de Diciembre de 2026</div>
                </div>

                <div class="sophia-info-card">
                    <div class="sophia-info-title">🕗 Hora de Recepción</div>
                    <div class="sophia-info-val">7:30 PM</div>
                </div>

                <div class="sophia-info-card">
                    <div class="sophia-info-title">🏛️ Salón de Fiesta</div>
                    <div class="sophia-info-val">Salón Nova Roma</div>
                    <div class="sophia-info-address">Mérida, Venezuela (Dirección detallada por confirmar)</div>
                </div>

                <div class="sophia-info-card" style="background: rgba(235, 197, 204, 0.4) !important;">
                    <div class="sophia-info-title">👗 Código de Vestimenta</div>
                    <div class="sophia-info-val" style="font-size: 0.92rem;">Formal de Gala</div>
                    <p style="font-size: 0.75rem; color: #79544D; margin-top: 4px; font-style: italic;">
                        Sugerimos tonos suaves y elegantes. Por favor reserva los tonos rosa y dorado champagne para la quinceañera.
                    </p>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             TARJETA 5: CONFIRMACIÓN DE ASISTENCIA (RSVP)
             ========================================================================= -->
        <div class="card-preview-col">
            <div class="card-number-badge">5. Confirmación (RSVP)</div>
            <div class="device-card-frame sophia-card">
                <div style="text-align: center; margin-bottom: 8px;">
                    <i class="fas fa-envelope-circle-check sophia-icon" style="font-size: 26px;"></i>
                </div>
                <div class="sophia-title-script" style="font-size: 2.4rem; text-align: center;">Confirmar Asistencia</div>
                <div class="sophia-title-serif" style="font-size: 0.75rem; text-align: center;">PASE VIP PERSONALIZADO</div>
                <div class="sophia-divider"></div>

                <p style="text-align: center; font-size: 0.82rem; color: #79544D; margin-bottom: 12px;">
                    Por favor confirma tu asistencia antes del 1 de Diciembre para coordinar tu lugar en el jardín:
                </p>

                <!-- Simulación de Formulario RSVP -->
                <form onsubmit="event.preventDefault(); alert('¡Gracias por confirmar para Sophia Valentina!');" style="display: flex; flex-direction: column; gap: 10px;">
                    <div>
                        <input type="text" class="sophia-form-control" style="width: 100%;" placeholder="Tu Nombre Completo" value="Familia Invitada Especial">
                    </div>
                    <div>
                        <select class="sophia-form-control" style="width: 100%;">
                            <option>✨ Sí, asistiré con mucho gusto</option>
                            <option>💌 Lamentablemente no podré asistir</option>
                        </select>
                    </div>
                    <div>
                        <input type="number" class="sophia-form-control" style="width: 100%;" placeholder="Número de pases confirmados" value="2" min="1" max="5">
                    </div>

                    <!-- Pase VIP Preview -->
                    <div class="sophia-vip-pass" style="text-align: center; margin: 8px 0;">
                        <div style="font-size: 0.72rem; letter-spacing: 2px; color: #C5A16A; text-transform: uppercase; font-weight: 700;">PASE VIP OFICIAL</div>
                        <div style="font-family: 'Playfair Display', serif; font-size: 1.1rem; color: #79544D; font-weight: 700; margin: 4px 0;">
                            XV-SOPHIA-002
                        </div>
                        <div style="font-size: 0.75rem; color: #C5A16A;">Salón Nova Roma · 7:30 PM</div>
                    </div>

                    <div style="text-align: center;">
                        <button type="submit" class="sophia-btn-gold" style="width: 100%; font-size: 0.8rem;">
                            <i class="fas fa-check-double"></i>
                            <span>Confirmar Asistencia</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- =========================================================================
             TARJETA 6: VIDEO DE DESPEDIDA (AL FINAL DE LA INVITACIÓN)
             ========================================================================= -->
        <div class="card-preview-col">
            <div class="card-number-badge">Final: Video Mágico</div>
            <div class="device-card-frame sophia-card">
                <!-- Mariposa 3D Animada -->
                <div class="sophia-butterfly sophia-butterfly-float-1">
                    <svg viewBox="0 0 100 100">
                        <path class="wing-left" d="M 50 50 C 20 10, 0 35, 10 65 C 20 85, 45 65, 50 50 Z" fill="#EBC5CC" stroke="#C5A16A" stroke-width="1.8" />
                        <path class="wing-right" d="M 50 50 C 80 10, 100 35, 90 65 C 80 85, 55 65, 50 50 Z" fill="#D9A5B5" stroke="#C5A16A" stroke-width="1.8" />
                        <ellipse cx="50" cy="50" rx="3" ry="14" fill="#C5A16A" />
                    </svg>
                </div>

                <div class="sophia-title-script" style="font-size: 2.3rem; text-align: center; margin-bottom: 2px;">¡Te Espero!</div>
                <div class="sophia-title-serif" style="font-size: 0.72rem; letter-spacing: 2px; text-align: center; margin-bottom: 8px;">MIS XV AÑOS</div>

                <!-- Marco con el video oficial de la festejada -->
                <div style="position: relative; width: 100%; max-width: 290px; margin: 0 auto 12px auto; border-radius: 20px; overflow: hidden; border: 2.5px solid #C5A16A; box-shadow: 0 10px 25px rgba(56, 33, 40, 0.4);">
                    <video id="previewFarewellVideo" src="api/video.php?v=sophia_transformacion.mp4" playsinline webkit-playsinline loop muted autoplay style="width: 100%; height: auto; max-height: 320px; display: block; border-radius: 17px; object-fit: cover;" poster="sophia-portada.webp">
                        Tu navegador no soporta reproducción de video.
                    </video>
                    <!-- Control de Audio Flotante -->
                    <button type="button" onclick="const v=document.getElementById('previewFarewellVideo'); v.muted=!v.muted; this.querySelector('i').className = v.muted ? 'fas fa-volume-mute' : 'fas fa-volume-high';" style="position: absolute; bottom: 8px; right: 8px; background: rgba(56,33,40,0.75); color: #FFF3E7; border: 1px solid #C5A16A; border-radius: 50%; width: 34px; height: 34px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; backdrop-filter: blur(4px);" title="Activar/Silenciar Audio">
                        <i class="fas fa-volume-mute"></i>
                    </button>
                </div>

                <div class="sophia-divider" style="margin: 8px auto;"></div>

                <p class="sophia-text" style="text-align: center; font-size: 0.85rem; line-height: 1.4; margin: 0;">
                    Tu presencia es el mejor regalo.<br>
                    ¡Celebremos juntos esta noche mágica! ✨
                </p>

                <p class="sophia-text" style="text-align: center; font-size: 0.78rem; margin-top: 8px;">
                    Con todo mi cariño,<br>
                    <span style="font-family: 'Great Vibes', cursive; font-size: 1.8rem; color: #C5A16A; display: inline-block; margin-top: 1px;">
                        Sophia Valentina
                    </span>
                </p>
            </div>
        </div>

    </main>

    <!-- Barra de Navegación Rápida -->
    <div class="bottom-action-bar">
        <a href="/e/sophia-xv" target="_blank" class="btn-direct-link">
            <i class="fas fa-mobile-screen"></i> Ver Invitación Sophia (/e/sophia-xv)
        </a>
        <a href="/admin" target="_blank" class="btn-direct-link" style="background: rgba(255,255,255,0.1); color: #FFF3E7; border: 1px solid #C5A16A;">
            <i class="fas fa-shield-halved"></i> Panel Admin
        </a>
    </div>

    <!-- Script de Cuenta Regresiva en Vivo -->
    <script>
        function updateDemoCountdown() {
            const target = new Date('2026-12-21T19:30:00').getTime();
            const now = new Date().getTime();
            const diff = target - now;

            if (diff > 0) {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                const dEl = document.getElementById('demo-days');
                const hEl = document.getElementById('demo-hours');
                const mEl = document.getElementById('demo-minutes');
                const sEl = document.getElementById('demo-seconds');

                if (dEl) dEl.innerText = days;
                if (hEl) hEl.innerText = String(hours).padStart(2, '0');
                if (mEl) mEl.innerText = String(minutes).padStart(2, '0');
                if (sEl) sEl.innerText = String(seconds).padStart(2, '0');
            }
        }
        setInterval(updateDemoCountdown, 1000);
        updateDemoCountdown();
    </script>
</body>
</html>
