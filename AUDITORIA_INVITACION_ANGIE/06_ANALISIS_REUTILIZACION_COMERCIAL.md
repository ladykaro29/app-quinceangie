# AUDITORÍA TÉCNICA Y FUNCIONAL — PROYECTO INVITACIONANGIE
## DOCUMENTO 06: ANÁLISIS DE REUTILIZACIÓN Y ESCALABILIDAD COMERCIAL

---

### 1. EVALUACIÓN DE PERSONALIZACIÓN ACTUAL

A continuación se responde puntualmente a las interrogantes operativas de personalización en el estado actual del repositorio:

| Pregunta de Evaluación | ¿Es posible hoy sin editar código? | Diagnóstico Técnico Detallado |
| :--- | :---: | :--- |
| **¿Es posible cambiar el nombre de la quinceañera sin editar código?** | **NO** | El nombre "Angie Karolina" y sus apellidos están incrustados en al menos 35 ubicaciones directas dentro de `public/index.php`, `public/libro-recuerdos.php`, `public/en-vivo.php`, `admin/index.php`, `api/confirmar.php` y metaetiquetas Open Graph. |
| **¿Es posible cambiar fotografías sin editar código?** | **NO** | Las imágenes de portada y cierre (`angie-portada.jpg` y `angie-xv.jpg`) tienen sus nombres fijos en las etiquetas `<img>`. Para sustituirlas, el usuario debe reemplazar físicamente los archivos con el mismo nombre exacto en el disco o alterar el HTML. |
| **¿Es posible modificar textos y dedicatorias sin editar código?** | **NO** | Los textos de la historia ("Érase una vez..."), la bendición de los padres, el itinerario y la información de la lluvia de sobres están escritos en texto plano dentro del código HTML. |
| **¿Es posible modificar fecha y ubicación sin editar código?** | **NO** | La fecha del evento (`2026-10-03T20:00:00-04:00`), la fecha de cumpleaños, la dirección del Colegio de Médicos y las URLs de Google Calendar y Google Maps están escritas a mano en JavaScript y HTML. |
| **¿Es posible cambiar colores sin editar código?** | **NO** | Las variables CSS (`--verde-esmeralda`, `--dorado`, etc.) están definidas dentro de la etiqueta `<style>` de cada archivo. No existe un panel de control ni un archivo JSON de configuración que permita conmutar temas (ej. rosa dorado, azul zafiro, lila pastel). |
| **¿Es posible cambiar música y videos sin editar código?** | **NO** | Los archivos de audio `musica.mp3` y `musica.m4a` están fijados en la etiqueta `<audio>`. Los 19 videos del libro de recuerdos tienen sus títulos y descripciones hardcodeados en el script `api/libro.php` (array `$titulosMomentos`). |

---

### 2. INDEPENDENCIA DE DATOS VS. DISEÑO

- **Estado Actual:** Acoplamiento extremo (*Tight Coupling*). No existe separación entre capa de presentación y capa de datos. No se utiliza ningún patrón arquitectónico desacoplado (como MVC, MVVM o Clean Architecture).
- **Archivos que requieren dinamización urgente:**
  1. `public/index.php`: Debe transformarse en una plantilla que reciba un objeto `$evento` o consuma un endpoint de configuración JSON.
  2. `public/libro-recuerdos.php`: Debe cargar el título del libro, la temática, los capítulos y los recursos desde la base de datos según el ID del evento.
  3. `public/en-vivo.php`: Debe filtrar las fotos y el título superior en función del evento activo.
  4. `api/confirmar.php` y `api/checkin.php`: Deben recibir un identificador de evento (`evento_id` o `slug`) para no mezclar invitados de distintas celebraciones.

---

### 3. POTENCIAL Y VIABILIDAD DE MOTOR DE PLANTILLAS

El proyecto cuenta con una base de diseño excelente que puede convertirse en un motor de plantillas sumamente competitivo si se abstraen sus componentes:

```text
ESTRUCTURA DE COMPONENTES REUTILIZABLES (Propuesta Arquitectónica):
├── Layouts/
│   ├── DeckSliderLayout.php       (Contenedor de tarjetas móviles con swipe)
│   ├── FlipbookGrimorioLayout.php (Libro 3D de recuerdos con Web Audio)
│   └── LiveScreenLayout.php       (Pantalla de proyección para salón)
│
├── Components/
│   ├── HeroTiara.php              (Tiara/Corona SVG parametrizable en color)
│   ├── StoryFairyTale.php         (Cuento con letra capital personalizable)
│   ├── CountdownTimer.php         (Temporizador con sincronización Google Calendar)
│   ├── EventLocation.php          (Fecha, hora y enlace GPS Google Maps / Waze)
│   ├── DressCodePalette.php       (Generador dinámico de muestras de color)
│   ├── TimelineItinerary.php      (Programa del evento con iconos dinámicos)
│   ├── TriviaGame.php             (Motor de preguntas y respuestas con confeti)
│   ├── SongSuggester.php          (Widget de playlist interactiva)
│   ├── GiftBox.php                (Lluvia de sobres, cuenta bancaria o mesa de regalos)
│   └── RsvpPassQr.php             (Formulario, pase VIP, QR con hash MD5 y boletos de rifa)
```

---

### 4. AUDITORÍA DE CAPACIDADES ADMINISTRATIVAS COMERCIALES

| Capacidad Requerida para un Producto Comercial | ¿Existe Actualmente? | Brecha Detectada |
| :--- | :---: | :--- |
| **Capacidad para crear nuevas celebraciones** | **NO** | No existe interfaz ni modelo relacional para dar de alta un nuevo evento. El sistema asume que existe un único evento en el mundo. |
| **Administración simultánea de varias invitaciones** | **NO** | Las tablas `invitados`, `fotos_fiesta` y `canciones` no tienen una columna `evento_id`. Todos los datos caerían en el mismo contenedor global. |
| **Separación de roles (SuperAdmin vs. Anfitrión)** | **NO** | No existen roles. Solo hay una contraseña maestra. Si se le entrega acceso al anfitrión (padres de la quinceañera), este tendría acceso a la administración global. |
| **Módulo de paquetes, precios y vigencia** | **NO** | Inexistente. No hay control de suscripciones, límites de invitados contratados ni fecha de expiración del enlace. |
| **Gestión de subdominios o URLs amigables (`/angie-xv`, `/sofia-xv`)** | **NO** | El sistema responde únicamente en la raíz o en `/public/`. No cuenta con enrutamiento dinámico por *slug*. |

---

### 5. ANÁLISIS DE ESCALABILIDAD TÉCNICA (10, 50 Y 100 EVENTOS)

Si el software actual se desplegara tal como está para atender a múltiples clientes, colapsaría rápidamente debido a los siguientes cuellos de botella técnicos:

#### A. Almacenamiento y Transferencia de Archivos Pesados:
- **Diagnóstico:** Cada celebración incluye actualmente 160 fotos JPG (~14 MB) y 19 videos MP4 (~59 MB), totalizando ~73 MB por evento. Además, los invitados suben fotos de hasta 12 MB cada una.
- **Proyección a 100 eventos:** $100 \times 73\text{ MB} = 7.3\text{ GB}$ de multimedia base, más el almacenamiento incremental de fotos de invitados.
- **Problema:** En el servidor actual, los videos se sirven desde el disco local mediante PHP (`api/video.php`). Cien eventos con cientos de invitados reproduciendo videos simultáneamente saturarán el ancho de banda del servidor web y agotarán los procesos PHP-FPM o Apache workers.
- **Solución requerida:** Migrar el almacenamiento multimedia a un servicio de objetos en la nube compatible con S3 (ej. Cloudflare R2 o AWS S3) conectado a un CDN global para entrega directa sin pasar por PHP.

#### B. Persistencia y Concurrencia de Base de Datos:
- **Diagnóstico:** Si la aplicación entra en modo SQLite por caída o falta de configuración de MySQL, SQLite bloqueará la base de datos completa ante escrituras concurrentes intensas (*database is locked*), situación muy probable cuando 200 invitados intentan subir fotos o confirmar su asistencia al mismo tiempo.
- **Solución requerida:** Es mandatorio exigir un clúster MySQL/MariaDB o PostgreSQL dedicado con índices bien estructurados y pool de conexiones para entornos multi-inquilino.

#### C. Renderizado y Rendimiento en Dispositivos Móviles:
- **Diagnóstico:** Las páginas actuales cargan de golpe scripts pesados, CSS inmenso y recursos multimedia en un solo documento HTML de 200 KB.
- **Solución requerida:** Minificación y compresión automática de assets (Gzip/Brotli), *lazy loading* estricto de imágenes y modularización mediante Vite o esbuild.
