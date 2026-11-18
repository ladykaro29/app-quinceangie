<?php
namespace App\Middleware;

use App\Core\Database;
use App\Core\CurrentEventContext;
use PDO;

/**
 * Middleware que identifica y resuelve la celebración correspondiente (Tenant)
 * Soporta resolución por subdominio, parámetro en query (?evento=slug), ruta (/e/{slug}) o fallback por defecto.
 */
class TenantResolverMiddleware
{
    public static function resolve(?string $requestedSlug = null): bool
    {
        $db = Database::getInstance();

        // 1. Slug pasado explícitamente por el enrutador
        $slug = $requestedSlug;

        // 2. Si no viene de ruta, revisar query parameter
        if (!$slug && !empty($_GET['evento'])) {
            $slug = trim($_GET['evento']);
        }

        // 3. Fallback: Si no se especifica slug, el evento predeterminado es Angie Karolina ('angie-xv' o ID 1)
        if (!$slug) {
            $slug = 'angie-xv';
        }

        // 4. Consultar en base de datos (con auto-recuperación si falta la tabla eventos)
        try {
            $stmt = $db->prepare("SELECT * FROM eventos WHERE slug = :slug AND activo = 1 LIMIT 1");
            $stmt->execute([':slug' => $slug]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            \App\Core\DatabaseMigrationService::ensureMigrated($db);
            $stmt = $db->prepare("SELECT * FROM eventos WHERE slug = :slug AND activo = 1 LIMIT 1");
            $stmt->execute([':slug' => $slug]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$event) {
            // Intentar por ID 1 como garantía
            $stmt = $db->query("SELECT * FROM eventos WHERE id = 1 LIMIT 1");
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$event) {
                return false;
            }
        }

        // 5. Cargar configuraciones del evento
        $stmtCfg = $db->prepare("SELECT clave, valor, tipo FROM configuraciones_evento WHERE evento_id = :evento_id");
        $stmtCfg->execute([':evento_id' => $event['id']]);
        $rows = $stmtCfg->fetchAll(PDO::FETCH_ASSOC);

        $configs = [];
        foreach ($rows as $r) {
            $val = $r['valor'];
            if ($r['tipo'] === 'json') {
                $decoded = json_decode($val, true);
                $val = ($decoded !== null) ? $decoded : $val;
            }
            $configs[$r['clave']] = $val;
        }

        CurrentEventContext::set($event, $configs);
        return true;
    }
}
