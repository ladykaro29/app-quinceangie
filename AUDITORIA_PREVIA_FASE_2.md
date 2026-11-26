# AUDITORÍA PREVIA — FASE 2: GESTIÓN INTELIGENTE DE INVITADOS, RESEND Y EXPERIENCIA DIGITAL

**Fecha de Ejecución:** 26 de Noviembre de 2026  
**Rama de Trabajo:** `feature/fase2-gestion-invitados-resend`  
**Punto de Restauración (Tag):** `fase1-estable-punto-restauracion`  
**Commit Base:** `a3798c2`  
**Auditor:** Arquitecto de Software Senior & Especialista Full-Stack  

---

## 1. OBJETIVO DE LA AUDITORÍA

Evaluar de forma rigurosa la infraestructura actual del proyecto antes de realizar cualquier modificación para la Fase 2, verificando:
1. Arquitectura del sistema multi-tenant existente.
2. Mecanismos de enrutamiento y despacho de peticiones.
3. Esquema actual de bases de datos relacionales (SQLite / MySQL).
4. Flujo funcional y técnico del RSVP actual.
5. Implementación del módulo "Momentos Mágicos" y su acoplamiento con la invitación.
6. Infraestructura de almacenamiento de medios (fotos, videos, miniaturas).
7. Existencia o ausencia de servicios de correo electrónico.
8. Estado del conjunto automatizado de pruebas (Fase 1).

---

## 2. ARQUITECTURA MULTI-TENANT EXISTENTE

### 2.1 Modelo de Datos Multi-Tenant
La plataforma opera bajo un modelo de aislamiento lógico multi-inquilino (*Logical Multi-Tenancy*) estructurado en:
- **`eventos`**: Contiene la definición de cada celebración:
  - `id = 1`: `angie-xv` (Angie Karolina, 05/09/2026, Estilo: Verde Esmeralda & Oro Imperial).
  - `id = 2`: `sophia-xv` (Sophia Valentina, 21/12/2026, Estilo: El Jardín Encantado, Rosa Blush, Lavanda & Champagne).
- **`configuraciones_evento`**: Pares clave-valor tipados (`clave`, `valor`, `tipo`) asociados a `evento_id`, tales como `design_tokens`, `musica_url`, `cuenta_bancaria`, `frase_portada`.
- **Aislamiento en tablas de datos**:
  - `invitados.evento_id`
  - `fotos_fiesta.evento_id`
  - `canciones.evento_id`
  - `libro_recuerdos.evento_id`

### 2.2 Resolución de Inquilino (*Tenant Resolution*)
- **Middleware:** `App\Middleware\TenantResolverMiddleware` intercepta cada petición.
- **Detección por Slug:** Analiza la URI buscando el prefijo `/e/{slug}` o el parámetro `?evento={slug}`.
- **Contexto Global:** Inicializa `App\Core\CurrentEventContext`, haciendo accesible el evento activo y sus configuraciones en toda la aplicación de manera segura y desacoplada.

---

## 3. ENRUTAMIENTO Y FRONT CONTROLLER

### 3.1 Servidor Web y Reglas Rewrite
- **`.htaccess` Raíz:** Redirige peticiones no estáticas hacia `index.php`. Enruta de forma transparente recursos estáticos de `assets/`, `storage/` y `public/`.
- **`index.php` Raíz:** Actúa como Front Controller orquestado por `App\Core\Router`:
  - `GET /`: Invitación por defecto (Angie Karolina).
  - `GET /e/{slug}`: Invitación multi-celebración (`public/index.php`).
  - `GET /libro/{slug}`: Grimorio 3D de recuerdos (`MemoryBookController::render($slug)`).
  - `GET /en-vivo`: Pantalla proyector en tiempo real (`public/en-vivo.php`).
  - `GET /subir-fotos/{slug}`: Formulario móvil para subir fotos (`public/subir-fotos.php`).
  - `GET /tarjeta-qr/{slug}`: Tarjeta imprimible de mesa con QR.
  - `ANY /admin`: Panel de control administrativo (`admin/index.php`).
  - `ANY /admin/moderacion-fotos`: Centro de moderación multimedia (`admin/moderacion-fotos.php`).
  - `ANY /api/album/fotos`: Endpoint REST para álbum colaborativo (`CollaborativePhotoController::handle`).

---

## 4. SISTEMA ACTUAL DE RSVP (CONFIRMACIÓN DE ASISTENCIA)

### 4.1 Esquema de Datos Actual
- **Tabla `invitados`:**
  - `id` (INTEGER PRIMARY KEY)
  - `nombre_completo` (TEXT)
  - `asistira` (INTEGER, 1 o 0)
  - `codigo_rifa` (TEXT, e.g. `XV-SOPHIA-8291`)
  - `asistio_evento` (INTEGER, check-in físico)
  - `checkin_at` (DATETIME)
  - `created_at` (DATETIME)
  - `evento_id` (INT)
- **Tabla `acompanantes`:**
  - `id` (INTEGER PRIMARY KEY)
  - `invitado_id` (INTEGER FOREIGN KEY)
  - `nombre_completo` (TEXT)
  - `codigo_rifa` (TEXT)

### 4.2 Limitaciones Identificadas para la Fase 2
1. **Falta de datos de contacto:** No almacena correo electrónico (`email`) ni teléfono (`telefono`).
2. **Falta de agrupación:** No existe concepto de `grupo_familiar` ni `pases_autorizados`.
3. **Ausencia de tokens de acceso seguro:** No hay tokens individuales criptográficos (`token_seguro`) para enlaces mágicos ni personalización de correos.
4. **Falta de historial de comunicaciones:** No hay registro de qué correos o recordatorios ha recibido cada invitado.
5. **No hay importación masiva administrativa:** Actualmente solo se registran mediante el formulario web de la tarjeta 10 o de forma manual directa en base de datos.

---

## 5. MÓDULO "MOMENTOS MÁGICOS" (ÁLBUM COLABORATIVO)

### 5.1 Ubicación y Acoplamiento Actual
- En la invitación (`public/index.php`), Momentos Mágicos se encuentra como la **Tarjeta 12 (Slide 11 de 13)**:
  - Título: *"Momentos Mágicos — FOTOS DE LA FIESTA EN VIVO"*.
  - Incluye: Explicación de 3 pasos, botón para abrir modal de carga de fotos, visualizador previo de fotos y acceso a la galería.
- **Desventaja del diseño actual:** Sobrecarga la invitación digital formal con funcionalidades interactivas pesadas pensadas exclusivamente para el momento de la fiesta (21 de diciembre), cuando la invitación suele enviarse con meses o semanas de antelación.

### 5.2 Rutas Externas Existentes
- `public/subir-fotos.php` (`/subir-fotos/{slug}`): Pantalla móvil standalone para que los invitados suban fotos escaneando el QR de la mesa.
- `public/en-vivo.php` (`/en-vivo`): Pantalla optimizada para videobeam/proyector en tiempo real.
- `public/tarjeta-qr.php` (`/tarjeta-qr/{slug}`): Hoja imprimible tamaño 10x15 cm para mesas.

---

## 6. INFRAESTRUCTURA DE ALMACENAMIENTO MULTIMEDIA

### 6.1 `App\Services\StorageService`
- Directorio base: `storage/media/{evento_id}/`.
- Subdirectorios aislados:
  - `storage/media/{evento_id}/fotos/`: Fotografías originales validadas (JPEG, PNG, WebP).
  - `storage/media/{evento_id}/thumbs/`: Miniaturas generadas (320x320 px).
- **Validaciones de Seguridad:**
  - Validación de extensiones permitidas (`jpg`, `jpeg`, `png`, `webp`).
  - Validación de firma binaria MIME (`finfo_file`).
  - Detección de duplicados mediante hash SHA-256 en 24 horas.
  - Idempotencia mediante `subida_uuid`.

---

## 7. AUDITORÍA DE SERVICIOS DE CORREO ELECTRÓNICO

- **Resultado:** **No existe ningún servicio de correo electrónico implementado en el proyecto.**
- No se encontraron librerías como PHPMailer, Symfony Mailer ni clientes API.
- La función nativa `mail()` no está en uso.
- **Oportunidad de Diseño:** Permite crear una arquitectura de correo limpia, modular y desacoplada basada en la API oficial de Resend, sin arrastrar deuda técnica de servicios obsoletos.

---

## 8. ESTADO DE LA SUITE DE PRUEBAS DE FASE 1

- **Comando:** `php tests/suite_runner.php`
- **Resultado:** **40 de 40 pruebas aprobadas (100.0% PASS)**.
- **Aislamiento Multi-Tenant:** Verificado en 10 categorías críticas (identidad visual, aislamiento de fotos, libro de recuerdos, pantalla en vivo, check-in, concurrencia de subidas, etc.).
- **Punto de Restauración Establecido:**
  - Tag git: `fase1-estable-punto-restauracion`
  - Rama aislada de trabajo: `feature/fase2-gestion-invitados-resend`

---

## 9. CONCLUSIONES Y PASOS SIGUIENTES

1. La arquitectura actual es sólida, modular y extensible.
2. El enrutamiento de `Router` permite añadir sin fricción las rutas desacopladas solicitadas:
   - `/e/{slug}`: Invitación digital pura (12 tarjetas).
   - `/e/{slug}/momentos`: Álbum interactivo standalone.
   - `/e/{slug}/recuerdos`: Libro 3D de dedicatorias.
   - `/e/{slug}/proyeccion`: Pantalla de proyección en vivo.
3. Se requiere una migración de base de datos no destructiva para expandir la tabla `invitados` y crear las tablas de automatizaciones y logs de correo.
4. Proceder con la redacción de los documentos de arquitectura e integración antes de modificar código o estructuras.
