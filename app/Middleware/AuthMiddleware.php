<?php
namespace App\Middleware;

use App\Services\AuthService;

/**
 * Middleware de Control de Acceso y Roles (RBAC)
 * Plataforma: XV Experience
 */
class AuthMiddleware
{
    public static function requireAuth(): void
    {
        if (!AuthService::check()) {
            header('Location: /admin/login');
            exit;
        }
    }

    public static function requireSuperAdmin(): void
    {
        self::requireAuth();
        if (!AuthService::isSuperAdmin()) {
            http_response_code(403);
            echo "<!DOCTYPE html><html><head><title>403 Acceso Denegado</title></head><body style='background:#062E25;color:#FFD700;font-family:sans-serif;text-align:center;padding:50px;'><h1>403 · Acceso Restringido</h1><p style='color:#FFF;'>Se requieren privilegios de Super Administrador para ver esta sección.</p><a href='/admin' style='color:#FFD700;'>Volver al panel</a></body></html>";
            exit;
        }
    }

    public static function requireEventAccess(int $eventoId): void
    {
        self::requireAuth();
        if (!AuthService::canAccessEvent($eventoId)) {
            http_response_code(403);
            echo "<!DOCTYPE html><html><head><title>403 Acceso Denegado</title></head><body style='background:#062E25;color:#FFD700;font-family:sans-serif;text-align:center;padding:50px;'><h1>403 · Celebración No Autorizada</h1><p style='color:#FFF;'>No tienes permiso para administrar esta fiesta.</p><a href='/admin' style='color:#FFD700;'>Volver a mis celebraciones</a></body></html>";
            exit;
        }
    }
}
