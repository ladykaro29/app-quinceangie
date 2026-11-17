<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\CurrentEventContext;
use App\Middleware\TenantResolverMiddleware;

/**
 * Controlador de la Invitación Digital Principal
 * Plataforma: XV Experience
 */
class InvitationController
{
    public static function render(?string $slug = null): void
    {
        // 1. Resolver el evento (Tenant)
        TenantResolverMiddleware::resolve($slug);

        $event = CurrentEventContext::get();
        $configs = CurrentEventContext::allConfigs();

        // 2. Parámetros del entorno web
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) 
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
            ? 'https://' : 'http://';

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = rtrim($protocol . $host, '/');

        $data = [
            'event' => $event,
            'configs' => $configs,
            'baseUrl' => $baseUrl,
            'slug' => CurrentEventContext::slug(),
            'shareImageUrl' => $baseUrl . '/' . ($event['portada_url'] ?? 'angie-portada.jpg'),
            'pageUrl' => $baseUrl . '/'
        ];

        // 3. Renderizar el tema asignado al evento
        $theme = $event['plantilla_id'] == 2 ? 'rose-gold-princess' : 'emerald-prestige';
        
        // Incluir la vista principal del tema
        require_once dirname(__DIR__, 2) . '/templates/themes/' . $theme . '/invitation.php';
    }
}
