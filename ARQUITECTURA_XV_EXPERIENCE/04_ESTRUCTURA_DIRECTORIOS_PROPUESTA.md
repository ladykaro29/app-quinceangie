# ARQUITECTURA DE SOFTWARE — PLATAFORMA "XV EXPERIENCE"
## DOCUMENTO 04: ESTRUCTURA DE DIRECTORIOS PROPUESTA

---

### 1. MAPEO COMPARATIVO DE LA REORGANIZACIÓN

La siguiente tabla resume la transformación del árbol monolítico actual hacia la nueva estructura modular:

| Ubicación Actual (Auditada) | Nueva Ubicación Propuesta | Justificación del Reordenamiento |
| :--- | :--- | :--- |
| `public/index.php` (5,161 líneas con HTML, CSS, JS y lógica) | `app/Controllers/InvitationController.php` + `templates/themes/{theme}/` | Desacopla la lógica de backend del renderizado visual y del diseño. |
| `public/libro-recuerdos.php` (2,828 líneas) | `app/Controllers/MemoryBookController.php` + `templates/components/book-3d/` | Encapsula el grimorio 3D como un componente reutilizable para cualquier festejada. |
| `public/en-vivo.php` (558 líneas) | `app/Controllers/LiveScreenController.php` + `templates/views/live-screen.php` | Permite que el proyector consulte dinámicamente las fotos del `evento_id` activo. |
| `admin/index.php` (2,126 líneas) | `app/Controllers/Admin/` + `templates/admin/` | Divide el panel administrativo en controladores específicos (Dashboard, Rifa, Scanner, Media). |
| `api/*.php` (8 scripts independientes) | `app/Controllers/Api/` (Enrutados vía Front Controller) | Centraliza el manejo de cabeceras, middleware de seguridad, rate-limit y CORS. |
| `database/uploads/` y `public/assets/` (Duplicados) | `storage/media/{evento_id}/` (Carpeta centralizada única) | **Elimina 145 MB de duplicación redundante** y organiza los archivos por fiesta. |

---

### 2. ÁRBOL COMPLETO DE DIRECTORIOS PROPUESTO

```text
xv-experience/
│
├── .env.example                                # Plantilla de variables de entorno
├── .env                                        # Variables locales seguras (excluido de git)
├── .gitignore
├── composer.json                               # Autoload PSR-4 y dependencias opcionales
├── Dockerfile                                  # Contenedor optimizado Apache + PHP 8.2
│
├── app/                                        # LÓGICA DE APLICACIÓN (CORE)
│   ├── Controllers/                            # Controladores HTTP
│   │   ├── InvitationController.php            # Renderiza la invitación principal por slug
│   │   ├── MemoryBookController.php            # Renderiza el Libro Mágico 3D
│   │   ├── LiveScreenController.php            # Renderiza la pantalla para TV / Proyector
│   │   ├── Admin/                              # Controladores del Panel Administrativo
│   │   │   ├── AuthController.php              # Login, logout y gestión de sesión
│   │   │   ├── DashboardController.php         # Métricas globales de la fiesta
│   │   │   ├── GuestController.php             # CRUD de invitados y exportación CSV
│   │   │   ├── RaffleController.php            # Módulo de ruleta y sorteo de regalos
│   │   │   ├── MediaController.php             # Moderación de fotos y descarga ZIP
│   │   │   └── SongController.php              # Moderación de playlist sugerida
│   │   └── Api/                                # Controladores de la API REST JSON
│   │       ├── RsvpApiController.php           # Procesa confirmación y genera Pase VIP QR
│   │       ├── CheckinApiController.php        # Recepción y lectura WebRTC de pases
│   │       ├── PhotoApiController.php          # Subida (multipart/base64) y likes
│   │       ├── VideoApiController.php          # Streaming de video con HTTP Range 206
│   │       ├── BookApiController.php           # Publicación y likes de dedicatorias
│   │       ├── SongApiController.php           # Sugerencias musicales
│   │       └── GuestApiController.php          # Consulta segura para staff
│   │
│   ├── Middleware/                             # Filtros de Petición HTTP
│   │   ├── TenantResolverMiddleware.php        # Identifica evento por subdominio o slug
│   │   ├── AuthMiddleware.php                  # Valida roles (SuperAdmin, Anfitrion, Staff)
│   │   ├── CsrfMiddleware.php                  # Validación de tokens anti-CSRF
│   │   ├── RateLimitMiddleware.php             # Protección contra inundación (DDoS)
│   │   └── CorsMiddleware.php                  # Cabeceras CORS restrictivas
│   │
│   ├── Models/                                 # Entidades de Base de Datos (Active Record / Repository)
│   │   ├── Event.php
│   │   ├── EventConfig.php
│   │   ├── Guest.php
│   │   ├── Companion.php
│   │   ├── Photo.php
│   │   ├── Video.php
│   │   ├── Song.php
│   │   ├── MemoryDedication.php
│   │   ├── Order.php
│   │   └── User.php
│   │
│   ├── Services/                               # Lógica de Negocio Pura y Algoritmos
│   │   ├── VipPassGeneratorService.php         # Genera el QR criptográfico y hash MD5
│   │   ├── RaffleCodeGeneratorService.php      # Algoritmo único anti-colisión RIFA-XXXX
│   │   ├── MediaStorageService.php             # Adaptador de almacenamiento (Local / R2 / S3)
│   │   ├── AudioSynthesizerService.php         # Constantes de Web Audio API
│   │   └── AuditLoggerService.php              # Registro en tabla logs_auditoria
│   │
│   └── Core/                                   # Clases Base del Framework Ligero
│       ├── Router.php                          # Enrutador por expresiones regulares
│       ├── Database.php                        # Conexión PDO Singleton estricta
│       ├── Request.php                         # Envoltorio de peticiones HTTP seguras
│       ├── Response.php                        # Respuestas JSON y HTML estandarizadas
│       └── View.php                            # Renderizador de plantillas y layouts
│
├── config/                                     # CONFIGURACIONES DEL SISTEMA
│   ├── database.php                            # Parámetros PDO y conexiones
│   ├── app.php                                 # URLs base, entorno (dev/prod), zona horaria
│   ├── storage.php                             # Rutas locales y credenciales S3/R2
│   └── packages.php                            # Definición de límites y precios de paquetes
│
├── database/                                   # MIGRACIONES Y SEEDERS
│   ├── migrations/                             # Scripts de creación y alteración versionados
│   │   ├── 001_create_roles_and_users.sql
│   │   ├── 002_create_commercial_tables.sql
│   │   ├── 003_create_events_and_configs.sql
│   │   └── 004_create_event_operational_tables.sql
│   └── seeders/                                # Semilleros iniciales
│       ├── SeedRolesAndSuperadmin.php
│       ├── SeedPackages.php
│       └── SeedAngieEventMigration.php         # Migración fiel de los datos de Angie
│
├── public/                                     # DOCUMENT ROOT PÚBLICO DEL SERVIDOR WEB
│   ├── index.php                               # Front Controller único de la plataforma
│   ├── .htaccess                               # Regla que redirige todo tráfico a index.php
│   ├── favicon.ico / png / svg                 # Favicon institucional
│   └── assets/                                 # Recursos estáticos globales inmutables
│       ├── css/                                # Hojas de estilo globales
│       │   ├── framework-base.css
│       │   └── animaciones.css
│       ├── js/                                 # Scripts JS modulares y librerías
│       │   ├── qrcode.min.js
│       │   ├── html2canvas.min.js
│       │   ├── canvas-confetti.min.js
│       │   ├── html5-qrcode.min.js
│       │   ├── audio-synthesizer.js            # Módulo Web Audio reutilizable
│       │   ├── deck-slider.js                  # Lógica táctil de tarjetas
│       │   └── book-3d-engine.js               # Motor de paso de páginas 3D
│       └── icons/                              # SVG coronas, tiaras y adornos
│
├── storage/                                    # ALMACENAMIENTO DINÁMICO DE ARCHIVOS
│   ├── media/                                  # Archivos organizados por celebración
│   │   ├── 1/                                  # Carpeta del Evento #1 (Angie)
│   │   │   ├── photos/                         # Las 160 fotos oficiales + fotos invitados
│   │   │   ├── videos/                         # Los 19 videos MP4
│   │   │   ├── covers/                         # Portada y foto de agradecimiento
│   │   │   └── audio/                          # musica.mp3 y musica.m4a
│   │   └── 2/                                  # Carpeta del Evento #2 (Sophia - Piloto)
│   ├── logs/                                   # Logs de errores y auditoría de servidor
│   └── temp/                                   # Archivos temporales para generación de ZIPs
│
└── templates/                                  # MOTOR DE VISTAS Y PLANTILLAS VISUALES
    ├── layouts/                                # Estructuras envolventes
    │   ├── invitation-deck.php                 # Layout vertical de 13 tarjetas
    │   ├── grimoire-book.php                   # Layout 3D de libro de recuerdos
    │   ├── live-projector.php                  # Layout de pantalla de proyector
    │   └── admin-dashboard.php                 # Layout de administración
    │
    ├── components/                             # Tarjetas y componentes reutilizables
    │   ├── card-00-cover.php
    │   ├── card-01-story.php
    │   ├── card-02-countdown.php
    │   ├── card-03-blessing.php
    │   ├── card-04-location.php
    │   ├── card-05-dresscode.php
    │   ├── card-06-itinerary.php
    │   ├── card-07-trivia.php
    │   ├── card-08-playlist.php
    │   ├── card-09-gifts.php
    │   ├── card-10-rsvp.php
    │   ├── card-11-livephotos.php
    │   └── card-12-farewell.php
    │
    ├── themes/                                 # Catálogo de Temas Estéticos
    │   ├── esmeralda-real/                     # Tema de Angie (Verde Esmeralda y Oro)
    │   │   ├── theme.json                      # Tokens de color, tipografía y assets
    │   │   └── theme.css                       # Variables CSS del tema
    │   ├── rose-gold-princess/                 # Tema de Sophia (Oro Rosa y Destellos)
    │   │   ├── theme.json
    │   │   └── theme.css
    │   └── celestial-night/                    # Tema Azul Medianoche y Plata
    │       ├── theme.json
    │       └── theme.css
    │
    └── admin/                                  # Vistas del Panel Administrativo
        ├── login.php
        ├── dashboard.php
        ├── guests-table.php
        ├── raffle-roulette.php
        ├── scanner-modal.php
        └── media-gallery.php
```

---

### 3. EXPLICACIÓN DEL FLUJO FRONT CONTROLLER

Con la nueva estructura, el archivo `public/index.php` actúa como único punto de entrada:

1. El servidor web Apache recibe cualquier solicitud HTTP (ej. `GET /evento/angie-xv/libro-recuerdos`).
2. La regla del archivo `public/.htaccess` redirige la petición a `public/index.php`.
3. El Front Controller inicializa la configuración, arranca la sesión segura y ejecuta el pipeline de middlewares:
   - `CorsMiddleware` -> `RateLimitMiddleware` -> `TenantResolverMiddleware`.
4. El `TenantResolverMiddleware` detecta el slug `angie-xv`, consulta la base de datos y carga el contexto del evento.
5. El `Router` empareja la ruta interna con `MemoryBookController@renderBook`.
6. El controlador obtiene los datos del evento, carga el layout `templates/layouts/grimoire-book.php`, inyecta el tema `esmeralda-real` y emite la respuesta HTML compilada.
