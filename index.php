<?php
/**
 * Front Controller Centralizado — Plataforma "XV Experience"
 * Administra el enrutamiento limpio para invitaciones, grimorio 3D, panel de control y API.
 */

require_once __DIR__ . '/app/autoload.php';

use App\Core\Router;
use App\Controllers\InvitationController;
use App\Controllers\MemoryBookController;
use App\Controllers\Api\CollaborativePhotoController;

// ==============================================================================
// 1. RUTAS DE INVITACIÓN (FRONTEND)
// ==============================================================================

// Invitación predeterminada (Angie Karolina)
Router::get('/', function() {
    require_once __DIR__ . '/public/index.php';
});

// Invitación por slug multi-celebración (/e/angie-xv, /e/sophia-xv, etc.)
Router::get('/e/{slug}', function($slug) {
    $_GET['evento'] = $slug;
    require_once __DIR__ . '/public/index.php';
});

// Maqueta de Evaluación: Propuesta Visual 5 Tarjetas Sophia Valentina
Router::get('/propuesta-sophia', function() {
    require_once __DIR__ . '/public/propuesta-sophia-5-tarjetas.php';
});
Router::get('/propuesta-sophia-5-tarjetas.php', function() {
    require_once __DIR__ . '/public/propuesta-sophia-5-tarjetas.php';
});

// ==============================================================================
// 2. RUTAS DEL LIBRO MÁGICO 3D (RECUERDOS)
// ==============================================================================

// Libro por defecto (Angie Karolina)
Router::get('/libro', function() {
    require_once __DIR__ . '/public/libro-recuerdos.php';
});

// Libro por slug multi-celebración
Router::get('/libro/{slug}', function($slug) {
    MemoryBookController::render($slug);
});

// ==============================================================================
// 3. RUTAS DE LA FIESTA EN VIVO Y ÁLBUM COLABORATIVO
// ==============================================================================

// Pantalla proyector en vivo
Router::get('/en-vivo', function() {
    require_once __DIR__ . '/public/en-vivo.php';
});

// Subida móvil de fotos para invitados
Router::get('/subir-fotos', function() {
    require_once __DIR__ . '/public/subir-fotos.php';
});

Router::get('/subir-fotos/{slug}', function($slug) {
    $_GET['evento'] = $slug;
    require_once __DIR__ . '/public/subir-fotos.php';
});

// Tarjeta imprimible con QR para mesas (10x15 cm)
Router::get('/tarjeta-qr', function() {
    require_once __DIR__ . '/public/tarjeta-qr.php';
});

Router::get('/tarjeta-qr/{slug}', function($slug) {
    $_GET['evento'] = $slug;
    require_once __DIR__ . '/public/tarjeta-qr.php';
});

// ==============================================================================
// 4. RUTAS DEL PANEL ADMINISTRATIVO
// ==============================================================================

Router::any('/admin', function() {
    require_once __DIR__ . '/admin/index.php';
});

Router::any('/admin/moderacion-fotos', function() {
    require_once __DIR__ . '/admin/moderacion-fotos.php';
});

// ==============================================================================
// 5. RUTAS DE API REST
// ==============================================================================

Router::any('/api/album/fotos', [CollaborativePhotoController::class, 'handle']);

// ==============================================================================
// 6. DESPACHO DE LA PETICIÓN
// ==============================================================================

Router::dispatch();
