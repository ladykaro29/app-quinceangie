<?php
namespace App\Controllers;

use App\Core\CurrentEventContext;
use App\Middleware\TenantResolverMiddleware;

/**
 * Controlador del Libro Mágico 3D de Recuerdos
 * Plataforma: XV Experience
 */
class MemoryBookController
{
    public static function render(?string $slug = null): void
    {
        TenantResolverMiddleware::resolve($slug);

        $event = CurrentEventContext::get();
        $configs = CurrentEventContext::allConfigs();

        $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $protocol = $isHttps ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $protocol . $host;

        $data = [
            'event' => $event,
            'configs' => $configs,
            'baseUrl' => $baseUrl,
            'eventoId' => CurrentEventContext::id() ?? 1,
            'slug' => CurrentEventContext::slug() ?? 'angie-xv',
            'festejada' => $event['festejada_nombre'] ?? 'La Quinceañera',
            'titulo' => $event['nombre_celebracion'] ?? 'Mis XV Años',
            'portadaImg' => $event['avatar_url'] ?? 'angie-xv.jpg'
        ];

        // Cargar vista desacoplada del grimorio 3D
        require_once dirname(__DIR__, 2) . '/templates/components/book-3d/grimoire-book.php';
    }
}
