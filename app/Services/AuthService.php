<?php
namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Servicio de Autenticación Segura y Control de Sesiones
 * Plataforma: XV Experience
 */
class AuthService
{
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
    }

    /**
     * Intenta autenticar a un usuario por correo o usuario y contraseña
     */
    public static function login(string $emailOrUser, string $password): bool
    {
        self::startSession();
        $db = Database::getInstance();

        // 1. Compatibilidad con el usuario admin por defecto
        if ($emailOrUser === 'admin' && $password === 'angie2026') {
            $stmt = $db->query("SELECT u.id, u.nombre, u.email, r.nombre as rol 
                                FROM usuarios u 
                                JOIN roles r ON u.rol_id = r.id 
                                WHERE u.id = 1 LIMIT 1");
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol'];
                $_SESSION['is_admin'] = true;
                return true;
            }
        }

        // 2. Consulta en base de datos con verificación BCRYPT
        $stmt = $db->prepare("SELECT u.id, u.rol_id, u.nombre, u.email, u.password_hash, u.activo, r.nombre as rol 
                              FROM usuarios u 
                              JOIN roles r ON u.rol_id = r.id 
                              WHERE (u.email = :cred OR u.nombre = :cred) AND u.activo = 1 
                              LIMIT 1");
        $stmt->execute([':cred' => $emailOrUser]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['rol'];
            $_SESSION['is_admin'] = ($user['rol'] === 'superadmin');

            // Actualizar último login
            $stmtUp = $db->prepare("UPDATE usuarios SET ultimo_login = :now WHERE id = :id");
            $stmtUp->execute([':now' => date('Y-m-d H:i:s'), ':id' => $user['id']]);

            // Cargar eventos asociados si es anfitrión
            if ($user['rol'] === 'anfitrion') {
                $stmtEv = $db->prepare("SELECT evento_id, nivel_acceso FROM permisos_evento_usuario WHERE usuario_id = :id");
                $stmtEv->execute([':id' => $user['id']]);
                $_SESSION['user_events'] = $stmtEv->fetchAll(PDO::FETCH_KEY_PAIR);
            }

            return true;
        }

        return false;
    }

    public static function check(): bool
    {
        self::startSession();
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        self::startSession();
        if (!self::check()) return null;

        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['user_role'] ?? 'guest',
        ];
    }

    public static function isSuperAdmin(): bool
    {
        self::startSession();
        return ($_SESSION['user_role'] ?? '') === 'superadmin';
    }

    public static function canAccessEvent(int $eventoId): bool
    {
        self::startSession();
        if (self::isSuperAdmin()) return true;

        $events = $_SESSION['user_events'] ?? [];
        return array_key_exists($eventoId, $events);
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (!headers_sent() && ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            @setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }
}
