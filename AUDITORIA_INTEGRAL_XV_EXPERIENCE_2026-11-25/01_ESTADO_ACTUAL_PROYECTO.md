# 01 — ESTADO ACTUAL DEL PROYECTO
**Plataforma:** XV Experience  
**Fecha:** 25 de Noviembre de 2026  
**Especialidad:** Full Stack, DevOps y Arquitectura de Software

---

## 1. REPOSITORIO Y CONTROL DE VERSIONES
- **Repositorio Git Remoto:** `https://github.com/ladykaro29/app-quinceangie.git`
- **Rama Activa:** `main`
- **Último Commit en Remoto:** `4a8111c` (*fix: Auto-migracion y auto-recuperacion de tablas multi-celebracion para resolver error no such table eventos en Easypanel*)
- **Estado del Working Tree:** Limpio. No existen cambios pendientes de commit sobre archivos del sistema base.
- **Historial de Commits Clave:**
  - `4a8111c` (25-Nov-2026): Incorporación de [DatabaseMigrationService.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/app/Core/DatabaseMigrationService.php) para auto-recuperación de esquema multi-tenant en Easypanel.
  - `98fcd49` (18-Nov-2026): Portada oficial de Sophia, aislamiento de diseño, tokens dinámicos e imágenes editoriales.
  - `ef5b67b` (18-Nov-2026): Integración de `TenantResolverMiddleware` en `public/index.php`.
  - `5fe3682` (17-Nov-2026): Lanzamiento oficial de arquitectura base XV Experience v1.0 multi-celebración.

---

## 2. ESTRUCTURA FÍSICA DE DIRECTORIOS

```
C:\Users\troni\Documents\Descarga-Antigravity\InvitacionAngie
├── admin/                           # Panel administrativo monolítico (2,018 líneas)
│   └── index.php
├── api/                             # Endpoints REST procedurales
│   ├── canciones.php                # Sugerencias musicales (sin filtro evento_id)
│   ├── checkin.php                  # Validación de entrada QR / código
│   ├── confirmar.php                # Registro de asistencia RSVP y pases VIP
│   ├── foto.php                     # Despacho de imágenes individuales
│   ├── fotos.php                    # Galería y subida colaborativa en vivo
│   ├── invitados.php                # Listado público de confirmados (sin filtro evento_id)
│   ├── libro.php                    # Dedicatorias y momentos del libro mágico
│   └── video.php                    # Streaming HTTP Range de videos
├── app/                             # Núcleo MVC y Servicios (Namespace App\)
│   ├── Controllers/
│   │   ├── Api/CollaborativePhotoController.php
│   │   ├── InvitationController.php
│   │   └── MemoryBookController.php
│   ├── Core/
│   │   ├── CurrentEventContext.php   # Singleton del contexto activo
│   │   ├── Database.php             # Conexión PDO con soporte MySQL/SQLite
│   │   ├── DatabaseMigrationService.php # Auto-instalador y seeder dinámico
│   │   ├── Router.php               # Enrutador HTTP regex
│   │   └── View.php                 # Renderizador de plantillas
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   └── TenantResolverMiddleware.php # Resolución por slug / path
│   ├── Services/
│   │   └── AuthService.php          # Control de acceso RBAC y contraseñas bcrypt
│   └── autoload.php                 # Autocargador PSR-4
├── config/
│   └── database.php                 # Configuración de base de datos y fallback
├── database/
│   ├── invitacion_xv.sqlite         # Base de datos SQLite activa (159 KB)
│   ├── migrations_multi_tenant.sql  # DDL completo de tablas multi-celebración
│   ├── seed_angie_event.php         # Seeder específico de Angie Karolina
│   ├── seed_sophia_event.php        # Seeder oficial de Sophia Valentina
│   └── uploads/fotos/               # Almacén de fotos subidas por invitados
├── public/                          # Raíz web pública (DocumentRoot alternativo)
│   ├── assets/                      # Fotos de recuerdos y videos de gala
│   ├── en-vivo.php                  # Pantalla interactiva para proyector
│   ├── index.php                    # Monolito principal de la invitación (4,619 líneas)
│   ├── libro-recuerdos.php          # Grimorio 3D de recuerdos interactivo
│   ├── subir-fotos.php              # Interfaz móvil para escaneo QR
│   ├── angie-portada.jpg
│   └── sophia-portada.jpg
├── storage/media/                   # Almacén estructurado por celebración
│   └── 2/                           # Celebración Sophia Valentina (covers, audio)
├── templates/                       # Plantillas modulares preparadas para desacoplamiento
│   ├── components/cards/            # 13 tarjetas desacopladas (card-00 a card-12)
│   └── components/book-3d/          # Vista desacoplada del libro mágico
├── tests/                           # Suite de pruebas automatizadas funcionales
│   ├── executor.php                 # Aislador CLI de peticiones HTTP
│   └── suite_runner.php             # Ejecutor de las 24 pruebas de integración
├── Dockerfile                       # Definición de contenedor Apache + PHP 8.2
├── index.php                        # Front Controller centralizado
└── .htaccess                        # Reescritura mod_rewrite Apache
```

---

## 3. INVENTARIO DE RECURSOS MULTIMEDIA EN DISCO

| Recurso | Ruta Local | Peso | Rol en la Plataforma |
| :--- | :--- | :---: | :--- |
| **Música Angie** | `musica.mp3` / `public/musica.mp3` | 3.67 MB | Canción oficial Angie (*Valiente*) |
| **Música Alternativa** | `musica.m4a` | 2.47 MB | Versión comprimida para Safari |
| **Música Sophia** | `storage/media/2/audio/princesa_sofia.mp3` | *Mapeado* | Canción oficial Sophia (*Princesa Sofía*) |
| **Portada Angie** | `angie-portada.jpg` | 470 KB | Retrato vertical verde esmeralda |
| **Portada Sophia** | `sophia-portada.jpg` | 913 KB | Retrato editorial alta resolución |
| **Videos Angie** | `public/assets/videos/*.mp4` | 58.61 MB | 19 videos del vals y cortejo de gala |
| **Fotos Recuerdo Angie**| `database/uploads/fotos/` | Variable | 160 fotografías oficiales precargadas |

---

## 4. ANÁLISIS DE VERSIONES Y ENTORNO DE EJECUCIÓN
- **Versión de PHP Local:** PHP 8.2.32 (ZTS Visual C++ 2019 x64).
- **Versión de PHP en Contenedor Docker:** PHP 8.2 Debian Bookworm (`php:8.2-apache`).
- **Servidor Web:** Apache 2.4 con módulos `mod_rewrite` y `mod_headers` activos.
- **Manejador de Base de Datos:** Dual. Configurado para MySQL vía variables de entorno (`DB_HOST`, `DB_USER`, `DB_PASS`), con fallback y auto-migración garantizada hacia SQLite (`database/invitacion_xv.sqlite`) mediante `App\Core\DatabaseMigrationService`.
