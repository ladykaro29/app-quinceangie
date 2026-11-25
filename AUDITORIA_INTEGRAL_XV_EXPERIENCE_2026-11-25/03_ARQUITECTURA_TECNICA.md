# 03 — ARQUITECTURA TÉCNICA DEL SISTEMA
**Plataforma:** XV Experience  
**Fecha:** 25 de Noviembre de 2026  
**Especialidad:** Arquitectura de Software y Full Stack Senior

---

## 1. DIAGRAMA ARQUITECTÓNICO ACTUAL

```mermaid
graph TD
    User["Cliente / Navegador Web"] -->|HTTP GET/POST| WebServer["Apache 2.4 (.htaccess / mod_rewrite)"]
    WebServer -->|Redirige a| FC["Front Controller (index.php)"]
    
    subgraph "Capa de Enrutamiento y Middleware"
        FC --> Router["App\\Core\\Router"]
        Router --> TRM["App\\Middleware\\TenantResolverMiddleware"]
        TRM --> CEC["App\\Core\\CurrentEventContext (Singleton)"]
    end
    
    subgraph "Capa de Base de Datos"
        TRM --> DB["App\\Core\\Database (PDO)"]
        DB --> DMS["App\\Core\\DatabaseMigrationService"]
        DMS -.->|Auto-creación / Auto-seed| SQLite[("invitacion_xv.sqlite")]
    end
    
    subgraph "Capa de Presentación y Despacho"
        Router -->|/ o /e/{slug}| MonolithView["public/index.php (Monolito 4,619 lín.)"]
        Router -->|/libro/{slug}| MemoryBookCtrl["App\\Controllers\\MemoryBookController"]
        Router -->|/en-vivo| LiveView["public/en-vivo.php"]
        Router -->|/subir-fotos| UploadView["public/subir-fotos.php"]
        Router -->|/admin| AdminPanel["admin/index.php (Monolito 2,018 lín.)"]
        Router -->|/api/album/fotos| CollabPhotoCtrl["App\\Controllers\\Api\\CollaborativePhotoController"]
    end
    
    subgraph "APIs Procedurales Directas"
        User -->|/api/confirmar.php| ApiConfirmar["api/confirmar.php"]
        User -->|/api/fotos.php| ApiFotos["api/fotos.php"]
        User -->|/api/libro.php| ApiLibro["api/libro.php"]
        User -->|/api/invitados.php| ApiInvitados["api/invitados.php"]
    end
    
    MonolithView --> CEC
    LiveView --> CEC
    UploadView --> CEC
    ApiConfirmar --> DB
    ApiFotos --> DB
```

---

## 2. EVALUACIÓN DETALLADA DE COMPONENTES

### 2.1 Capa de Enrutamiento ([app/Core/Router.php](file:///c:/Users/troni/Documents/Descarga-Antigravity\InvitacionAngie\app\Core\Router.php))
- **Mecanismo:** Convierte expresiones como `/e/{slug}` o `/subir-fotos/{slug}` en expresiones regulares (`#^/e/(?P<slug>[^/]+)$#`) para capturar parámetros de URL.
- **Ventajas:** Permite rutas amigables y limpias en producción sin requerir parámetros de consulta forzados (`?evento=...`).
- **Limitación:** Coexiste con peticiones directas a archivos procedurales en `/api/` que no pasan por el despachador del Router.

### 2.2 Capa de Contexto ([app/Core/CurrentEventContext.php](file:///c:/Users/troni/Documents/Descarga-Antigravity\InvitacionAngie\app\Core\CurrentEventContext.php))
- **Mecanismo:** Almacén estático en memoria que contiene:
  - Registro completo de la tabla `eventos` (festejada, fecha, hora, lugar, paleta, audio, slug).
  - Diccionario asociativo clave-valor de `configuraciones_evento` (itinerario JSON, trivia JSON, tokens CSS, nombres de padres).
- **Consistencia:** Proporciona métodos de acceso seguro `CurrentEventContext::id()`, `CurrentEventContext::slug()`, `CurrentEventContext::config($key)`.

### 2.3 Capa de Datos ([app/Core/Database.php](file:///c:/Users/troni/Documents/Descarga-Antigravity\InvitacionAngie\app\Core\Database.php))
- **Conexión Dual:** Detecta variables de entorno para MySQL (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`). Si no están configuradas o fallan, conmuta a SQLite (`database/invitacion_xv.sqlite`).
- **Resiliencia Autónoma:** Se integra con [DatabaseMigrationService.php](file:///c:/Users/troni/Documents/Descarga-Antigravity\InvitacionAngie\app\Core\DatabaseMigrationService.php) para asegurar que las tablas multi-tenant se creen automáticamente si la base de datos está vacía.

---

## 3. COMPARACIÓN CON LA ARQUITECTURA OBJETIVO (LARAVEL)

| Requisito Arquitectónico | Estado Actual (XV Experience) | Estado Objetivo (Laravel 11 + Filament 3) |
| :--- | :--- | :--- |
| **Framework Base** | PHP 8.2 vanilla procedural + OOP híbrido | Laravel 11.x (LTS) |
| **Manejador de Paquetes** | Ninguno (Sin `composer.json`) | Composer 2.x con dependencias versionadas |
| **Motor de Vistas** | PHP embebido dentro de HTML monolítico | Blade Components con `@props` y directivas `@vite` |
| **ORM / Acceso a Datos** | PDO manual con sentencias SQL preparadas | Eloquent ORM con Modelos, Relaciones y Scopes |
| **Aislamiento Multi-Tenant** | `TenantResolverMiddleware` manual | `stancl/tenancy` o Global Scope `TenantScope` nativo |
| **Panel de Administración** | Script procedural de 2,018 líneas en `/admin` | Filament 3 Admin Panel con Resources, Widgets y RBAC |
| **Gestión de Assets** | Archivos estáticos directos en `public/` | Vite con compilación, compresión y hashing de assets |
| **Manejo de Errores** | `try-catch` locales con `error_log()` | Handler centralizado de excepciones con logging estructurado |

---

## 4. EVALUACIÓN DE DEUDA ARQUITECTÓNICA
1. **Acoplamiento de Vistas:** El archivo [public/index.php](file:///c:/Users/troni/Documents/Descarga-Antigravity\InvitacionAngie\public\index.php) contiene 4,619 líneas de código donde coexisten la lógica de presentación, consultas directas a base de datos para la rifa y la galería, definición de estilos CSS mediante etiquetas `<style>` y scripts JavaScript inline de más de 1,500 líneas.
2. **Duplicación de Código de Conexión:** Existen dos mecanismos de conexión paralelos: [config/database.php](file:///c:/Users/troni/Documents/Descarga-Antigravity\InvitacionAngie\config\database.php) (utilizado por las APIs procedurales) y [app/Core/Database.php](file:///c:/Users/troni/Documents/Descarga-Antigravity\InvitacionAngie\app\Core\Database.php) (utilizado por los controladores modernos).
