# AUDITORÍA TÉCNICA Y FUNCIONAL — PROYECTO INVITACIONANGIE
## DOCUMENTO 03: INVENTARIO FUNCIONAL EXHAUSTIVO

---

### 1. CRITERIOS DE CLASIFICACIÓN DEL ESTADO FUNCIONAL

Cada funcionalidad identificada en el repositorio se clasifica bajo uno de los siguientes estados objetivos:

- **COMPLETA Y VERIFICADA:** Funcionalidad con código completo, probado en ejecución local, con persistencia y respuesta adecuada en frontend y backend.
- **IMPLEMENTADA PARCIALMENTE:** Funcionalidad cuyo flujo está iniciado pero carece de integración con alguna de las capas (ej. frontend sin backend o viceversa).
- **IMPLEMENTADA, PERO NO VERIFICADA:** Código existente y completo en apariencia, pero que depende de servicios no disponibles localmente (ej. pasarelas externas o servidor de correos no configurado).
- **NO IMPLEMENTADA:** Funcionalidad esperada en una plataforma comercial o mencionada en la documentación pero inexistente en el código.
- **CON ERRORES O INCONVENIENTES:** Funcionalidad implementada pero que presenta fallas lógicas, bugs de ejecución o efectos secundarios negativos.

---

### 2. MÓDULO 1: INVITACIÓN DIGITAL INTERACTIVA (`public/index.php`)

| Funcionalidad | Archivos Responsables | Descripción | Datos que Utiliza | Dependencias | Estado Actual |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Deck de Tarjetas Deslizables (Swipe / Slider)** | `public/index.php` | Sistema de navegación vertical con gestos táctiles (touch swipe), flechas en pantalla y teclas de flecha arriba/abajo. Permite recorrer 13 tarjetas con animaciones de transición CSS suaves. | Estado del slide activo en memoria (`currentSlide`, `data-slide="0..12"`). | JavaScript Vanilla, CSS3 Transitions. | **COMPLETA Y VERIFICADA** |
| **Portada Real y Tiara SVG (Tarjeta 0)** | `public/index.php`, `angie-portada.jpg` | Pantalla de inicio con corona real vectorizada artesanalmente en SVG con gradientes dorados y diamantes, foto de Angie en marco redondeado, título caligráfico y llamado a la acción deslizable. | Imagen `angie-portada.jpg`, textos hardcodeados. | CSS Keyframes, SVG. | **COMPLETA Y VERIFICADA** |
| **Cuento de Fantasía "Érase una vez" (Tarjeta 1)** | `public/index.php` | Presentación poética estilo cuento de hadas medieval/celta. Muestra letra capital iluminada y dedicatoria especial mencionando a sus padres Edwin Johan Avendaño y Lady Rivera. | Textos fijos en HTML. | Google Fonts (`Great Vibes`, `Playfair Display`). | **COMPLETA Y VERIFICADA** |
| **Cuenta Regresiva con Google Calendar (Tarjeta 2)** | `public/index.php` | Temporizador en vivo calculando días, horas, minutos y segundos restantes hasta el 3 de Octubre de 2026, 8:00 PM (-04:00). Incluye botón interactivo que abre la plantilla de evento en Google Calendar. | Objeto `eventDate = new Date('2026-10-03T20:00:00-04:00')`. | `setInterval` nativo, enlace URL externo a Google Calendar. | **COMPLETA Y VERIFICADA** |
| **Invitación Formal y Bendición (Tarjeta 3)** | `public/index.php` | Mensaje formal de invitación destacando el honor de compartir la fecha junto a familiares y amigos. | Textos estáticos en HTML. | Tipografía serif. | **COMPLETA Y VERIFICADA** |
| **Información del Evento y Google Maps (Tarjeta 4)** | `public/index.php` | Muestra fecha de la fiesta (3 de Octubre de 2026), fecha de nacimiento real (23 de Septiembre) y ubicación: Colegio de Médicos de Mérida. Enlace directo a Google Maps. | Coordenadas y texto de dirección física. | Enlace URL a Google Maps. | **COMPLETA Y VERIFICADA** |
| **Dress Code y Paleta de Colores (Tarjeta 5)** | `public/index.php` | Muestra la indicación de vestimenta elegante, muestras circulares de la paleta sugerida (dorados, champán, beige, negro) y la instrucción explícita de reservar el verde esmeralda para la quinceañera. | Muestras de color en CSS (`#C8A24A`, `#FFF8EC`, etc.). | CSS Flexbox, Box Shadow. | **COMPLETA Y VERIFICADA** |
| **Itinerario de la Noche (Tarjeta 6)** | `public/index.php` | Línea de tiempo visual del programa nocturno desde las 8:00 PM hasta la medianoche (Recepción, Entrada, Vals, Pasapalos y Cócteles, Sorpresa, Hora Loca). | Horarios y actividades hardcodeadas. | FontAwesome Icons. | **COMPLETA Y VERIFICADA** |
| **Trivia Interactiva "¿Cuánto me conoces?" (Tarjeta 7)** | `public/index.php` | Juego interactivo de 5 preguntas sobre gustos de Angie (color verde esmeralda, repostería, pasticho, reggaetón y cumpleaños 23 de septiembre). Calcula puntuación, muestra retroalimentación y dispara confeti. | Array de preguntas JS `triviaQuestions`. | `canvas-confetti.browser.min.js`. | **COMPLETA Y VERIFICADA** |
| **Sugerencia de Canciones para la Fiesta (Tarjeta 8)** | `public/index.php`, `api/canciones.php` | Formulario en tiempo real donde el invitado escribe título y artista. Se envía por AJAX a la API y se visualiza en la lista inferior con actualización automática. | Tabla `canciones` (nombre, canción, ip). | `fetch()` API, `api/canciones.php`. | **COMPLETA Y VERIFICADA** |
| **Presente y Lluvia de Sobres (Tarjeta 9)** | `public/index.php` | Tarjeta informativa con icono de sobre abierto y buzón real, explicando cariñosamente que el regalo deseado es lluvia de sobres en efectivo para los proyectos futuros de la quinceañera. | Textos e iconos vectoriales. | CSS Card styling. | **COMPLETA Y VERIFICADA** |
| **Módulo RSVP y Generador de Pase VIP con QR (Tarjeta 10)** | `public/index.php`, `api/confirmar.php` | Formulario asíncrono que valida nombre completo (letras y tildes), asistencia afirmativa/negativa y permite añadir hasta 5 acompañantes dinámicamente. Al confirmar genera en pantalla un Pase VIP con QR, boletos de rifa individuales y botón para descargar el ticket completo como imagen PNG. | Tabla `invitados`, tabla `acompanantes`. | `qrcode.min.js`, `html2canvas.min.js`. | **COMPLETA Y VERIFICADA** |
| **Sección "Momentos Mágicos" y Galería en Vivo (Tarjeta 11)** | `public/index.php`, `api/fotos.php` | Mosaico fotográfico con botón "Tomar foto" (cámara nativa móvil), botón "Galería" y acceso directo al Libro 3D y a la pantalla de proyector. Permite subir fotos en vivo mediante modal. | Tabla `fotos_fiesta`. | Modal JS, `FileReader` API, `api/fotos.php`. | **COMPLETA Y VERIFICADA** |
| **Tarjeta de Despedida y Gratitud (Tarjeta 12)** | `public/index.php`, `angie-xv.jpg` | Cierre de la invitación con foto de Angie en vestido de gala verde esmeralda, firma digitalizada en tipografía `Great Vibes` y mensaje de agradecimiento. | Imagen `angie-xv.jpg`. | CSS Framing & Shine. | **COMPLETA Y VERIFICADA** |
| **Efectos de Partículas y Mariposas Doradas** | `public/index.php` | Sistema de animación en canvas y CSS: 4 mariposas doradas en las esquinas con aleteo y destellos, además de lluvia de estrellas flotantes persistentes en el fondo. | Funciones matemáticas sinusoidales en JS. | HTML5 `<canvas>`, CSS Keyframes. | **COMPLETA Y VERIFICADA** |

---

### 3. MÓDULO 2: MÚSICA Y MULTIMEDIA

| Funcionalidad | Archivos Responsables | Descripción | Datos que Utiliza | Dependencias | Estado Actual |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Reproductor de Música en la Invitación** | `public/index.php`, `musica.mp3`, `musica.m4a` | Botón flotante dorado en esquina superior con tooltip. Reproduce canción oficial (*Valiente - Con toda libertad*). Incluye desbloqueo automático en primer clic de usuario para cumplir políticas autoplay de iOS y Android. | Archivos físicos `musica.mp3` (3.84 MB) y `musica.m4a` (2.59 MB). | Etiqueta `<audio>` HTML5 nativa. | **COMPLETA Y VERIFICADA** |
| **Reproductor Vinilo en el Libro de Recuerdos** | `public/libro-recuerdos.php` | Widget circular interactivo con animación de disco de vinilo giratorio y notas musicales doradas cuando la música está activa. Permite pausar y reanudar. | `musica.mp3` / `musica.m4a`. | Web Audio / HTML5 Audio, CSS rotations. | **COMPLETA Y VERIFICADA** |
| **Servidor Streaming de Video con HTTP Range 206** | `api/video.php` | Endpoint especializado en servir fragmentos de video MP4 mediante lectura binaria con buffers de 64 KB y soporte del encabezado `HTTP_RANGE`. Evita descargas masivas innecesarias y permite saltar en la línea de tiempo en móviles. | 19 archivos MP4 en `public/assets/videos/`. | `fopen`, `fseek`, `fread` en PHP. | **COMPLETA Y VERIFICADA** |
| **Servidor de Entrega de Imágenes con Caché** | `api/foto.php` | Entrega fotos bajo parámetro `f=nombre.jpg`. Protegido contra Directory Traversal con `basename()`. Emite cabeceras de caché HTTP `Cache-Control: public, max-age=86400, immutable` y soporte de forzar descarga mediante `download=1`. | 160 archivos JPG en `public/assets/fotos/`. | PHP `readfile`, headers MIME. | **COMPLETA Y VERIFICADA** |
| **Subida y Procesamiento de Fotos de Invitados** | `api/fotos.php`, `public/index.php` | Procesa subida de imágenes tanto vía `multipart/form-data` como Base64 (data URI). Valida tipos MIME reales con `finfo` (jpeg, png, webp, heic) y tamaño máximo de 12 MB. Genera nombres aleatorios criptográficos. | Directorio `database/uploads/fotos/`, tabla `fotos_fiesta`. | Extensión PHP `fileinfo`, PDO. | **COMPLETA Y VERIFICADA** |
| **Redundancia y Desincronización de Archivos** | `api/fotos.php`, `admin/index.php` | Las nuevas fotos subidas por los invitados se guardan en `database/uploads/fotos/`, pero el repositorio tiene las 160 fotos iniciales también en `public/assets/fotos/`. El panel de administración solo borra de `database/uploads/`. | Directorios físicos en disco. | Sistema de archivos local. | **CON ERRORES O INCONVENIENTES** |

---

### 4. MÓDULO 3: LIBRO MÁGICO DE RECUERDOS (`public/libro-recuerdos.php`)

| Funcionalidad | Archivos Responsables | Descripción | Datos que Utiliza | Dependencias | Estado Actual |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Simulador de Grimorio y Paso de Páginas 3D** | `public/libro-recuerdos.php` | Renderizado de libro en perspectiva 3D con texturas de cuero esmeralda, bordes de pergamino, marcalibros laterales de navegación y animación de volteo de hoja con sombras realistas. | Array de pliegos (*spreads*) calculado dinámicamente en JS. | CSS 3D Transforms (`perspective`, `rotateY`). | **COMPLETA Y VERIFICADA** |
| **Efecto de Sonido de Hoja de Pergamino** | `public/libro-recuerdos.php` | Al voltear cada página, genera por síntesis de modulación de audio (ruido blanco filtrado por paso de banda y curvas exponenciales de ganancia) el crujido de papel antiguo. Cero archivos de audio externos. | Parámetros de frecuencia y ganancia en JS. | `AudioContext` nativo (Web Audio API). | **COMPLETA Y VERIFICADA** |
| **Navegación por Capítulos y Marcalibros** | `public/libro-recuerdos.php` | Marcalibros superiores e inferiores para saltar directamente a: Portada, Fotos Polaroid (Capítulo I), Galería de Videos (Capítulo II) y Dedicatorias (Capítulo III). | Índices de pliegos calculados según el total de items. | DOM manipulation JS. | **COMPLETA Y VERIFICADA** |
| **Álbum de Fotos Polaroid (160 Recuerdos)** | `public/libro-recuerdos.php`, `api/fotos.php` | Muestra las 160 fotografías organizadas de 2 en 2 por pliego de pergamino con estilo polaroid, cinta adhesiva dorada, pie de foto y conteo de likes. Al hacer clic abre el visor Lightbox. | API `api/fotos.php?limit=500`. | JSON Fetch, Lightbox JS. | **COMPLETA Y VERIFICADA** |
| **Galería de Momentos en Video (19 Videos)** | `public/libro-recuerdos.php`, `api/video.php` | Lista interactiva con 19 videos categorizados con títulos de momentos emotivos (Llegada, Vals con papá, Brindis, etc.). Permite reproducir en el Lightbox con streaming directo. | API `api/libro.php`. | HTML5 Video, HTTP Range 206. | **COMPLETA Y VERIFICADA** |
| **Sistema de Dedicatorias y Mensajes** | `public/libro-recuerdos.php`, `api/libro.php` | Muro de dedicatorias con fecha, tarjeta de color seleccionable (dorado, esmeralda, rosa, crema, púrpura, cielo), sticker emoji (👑, ✨, 💖, 🥂, 🌟), contador de likes en tiempo real y modal para publicar. | Tabla `libro_recuerdos`. | `api/libro.php` (POST / GET). | **COMPLETA Y VERIFICADA** |
| **Auto-Siembra de Dedicatorias Iniciales** | `api/libro.php` | Si la tabla `libro_recuerdos` está vacía, el endpoint inserta automáticamente 4 mensajes de ejemplo de alta calidez (Familia, Tía, Amigas, Padrinos). | Registros predeterminados en PHP. | PDO SQLite/MySQL. | **COMPLETA Y VERIFICADA** |
| **Adjuntar Fotos dentro de las Dedicatorias** | `public/libro-recuerdos.php`, `api/libro.php` | La base de datos tiene la columna `media_ref` en `libro_recuerdos`, pero el modal del libro no incluye campo para subir foto adjunta directamente desde el formulario de dedicatoria. | Campo `media_ref` en BD. | Incompleto en frontend. | **IMPLEMENTADA PARCIALMENTE** |

---

### 5. MÓDULO 4: PANEL DE ADMINISTRACIÓN (`admin/index.php`)

| Funcionalidad | Archivos Responsables | Descripción | Datos que Utiliza | Dependencias | Estado Actual |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Autenticación de Acceso Administrativo** | `admin/index.php` | Formulario de login protegido por clave con manejo de sesión (`$_SESSION['admin_auth']`) y soporte de HTTP Basic Auth. Incluye botón de logout. | Variable `ADMIN_PASS` de `.env` y claves de respaldo. | PHP Sessions. | **COMPLETA Y VERIFICADA** |
| **Pestaña 1: Gestión de Invitados y Asistencias** | `admin/index.php` | Tabla con lista de invitados titulares y acompañantes, estado RSVP, boletos de rifa asignados, fecha de registro y conmutador manual de Check-in. | Tablas `invitados`, `acompanantes`. | PDO, HTML Table. | **COMPLETA Y VERIFICADA** |
| **Buscador en Tiempo Real de Invitados** | `admin/index.php` | Campo de búsqueda instantánea por nombre del titular, acompañante, código de rifa o estado sin recargar la página. | Texto introducido en `#guestSearchInput`. | JavaScript DOM Filtering. | **COMPLETA Y VERIFICADA** |
| **Escáner QR para Recepción de Invitados (Check-in)** | `admin/index.php`, `api/checkin.php` | Modal con visor de cámara WebRTC que detecta el QR del Pase VIP. Consulta la API, marca asistencia, emite tono de audio de bienvenida (o de advertencia si ya entró) y actualiza la tabla. | API `api/checkin.php`, cámara del dispositivo. | `html5-qrcode.min.js`, Web Audio API. | **COMPLETA Y VERIFICADA** |
| **Módulo de Sorteo de Rifa en Vivo con Ruleta** | `admin/index.php` | Modal para sorteos de regalos durante la fiesta. Posee campo para definir el premio, ruleta con animación de boletos girando, sonido de carraca y fanfarria triunfal en Web Audio, y registro de ganadores no repetibles. | Array con todos los boletos de titulares y acompañantes activos. | Web Audio API, JavaScript Timer animation. | **COMPLETA Y VERIFICADA** |
| **Pestaña 2: Moderación de Fotos de la Fiesta** | `admin/index.php` | Muestra grid con las fotos subidas, botón para alternar visibilidad (ocultar foto indebida), botón para borrar foto individualmente y botones de limpieza de pruebas. | Tabla `fotos_fiesta`. | PDO, endpoints POST. | **COMPLETA Y VERIFICADA** |
| **Pestaña 3: Gestión de Playlist Sugerida** | `admin/index.php` | Lista de canciones sugeridas por los invitados con nombre del proponente, fecha y botón para eliminar sugerencia. | Tabla `canciones`. | PDO. | **COMPLETA Y VERIFICADA** |
| **Exportación de Invitados a CSV** | `admin/index.php` | Descarga archivo `.csv` con cabeceras UTF-8 BOM para apertura perfecta en Excel, consolidando titulares, acompañantes, códigos de rifa y totales. | Consulta SQL combinada. | `fputcsv` nativo de PHP. | **COMPLETA Y VERIFICADA** |
| **Descarga de Fotos en Archivo ZIP** | `admin/index.php` | Empaqueta todas las fotos visibles en un archivo ZIP nombrado con fecha y hora para resguardo de la familia. | Archivos en `database/uploads/fotos/`. | Extensión PHP `ZipArchive`. | **COMPLETA Y VERIFICADA** |
| **Gestión de Múltiples Celebraciones / Eventos** | `admin/index.php` | Capacidad para crear otro evento (ej. Boda de Carlos, XV de Sofía), cambiar de celebración o ver métricas por evento. | Inexistente en la base de datos actual. | No implementado. | **NO IMPLEMENTADA** |
| **Gestión de Roles y Usuarios de Administración** | `admin/index.php` | Creación de cuentas para recepcionistas con permisos limitados (solo escanear QR) separadas del administrador general. | Solo existe un usuario hardcodeado. | No implementado. | **NO IMPLEMENTADA** |

---

### 6. MÓDULO 5: CAPA DE SERVICIOS API REST JSON (`/api`)

| Endpoint | Método | Archivo | Parámetros / Payload | Validaciones | Respuesta / Códigos HTTP | Estado Actual |
| :--- | :---: | :--- | :--- | :--- | :--- | :--- |
| `/api/confirmar.php` | `POST` | `api/confirmar.php` | JSON: `nombre_completo`, `asistira`, `acompanantes` (array). | Nombre obligatorio (letras y tildes, max 150 caracteres). No duplicados en acompañantes. Detección de si ya fue registrado como titular o acompañante previo. Transacción DB. | `200 OK` (Pase VIP generado, boletos rifa, hash MD5). `400 Bad Request` (errores de validación). `500 Error`. | **COMPLETA Y VERIFICADA** |
| `/api/checkin.php` | `POST`, `GET` | `api/checkin.php` | JSON / POST: `qr_text`, `action` (`checkin`, `toggle`, `status`). | Regex para extraer ID de `PASE #id`, `XVANGIE-id`, `RIFA-xxxx`, ID numérico directo o coincidencia de nombre. | `200 OK` (datos del titular, acompañantes, presentes). `400 Bad Request`. `404 Not Found`. `500 Error`. | **COMPLETA Y VERIFICADA** |
| `/api/fotos.php` | `GET` | `api/fotos.php` | `limit`, `offset`, `since_id`, `order`. | Auto-siembra si hay menos de 160 fotos. Paginación segura. | `200 OK` (array de fotos con URLs relativas seguras). | **COMPLETA Y VERIFICADA** |
| `/api/fotos.php` | `POST` | `api/fotos.php` | `multipart/form-data` con archivo `foto` o `foto_base64`, `nombre_invitado`, `mensaje`. | Límite 12 MB. Validación de tipo MIME real con `finfo` (jpeg, png, webp, heic). Sanitización de textos. | `200 OK` (objeto de la foto creada con ID y URL). `400 Bad Request`. `500 Error`. | **COMPLETA Y VERIFICADA** |
| `/api/fotos.php?action=like` | `POST` | `api/fotos.php` | JSON / POST: `id` (entero). | `id > 0`, foto existente y visible. Incremento atómico `likes = likes + 1`. | `200 OK` (`likes` actualizado). `400 Bad Request`. | **COMPLETA Y VERIFICADA** |
| `/api/foto.php` | `GET` | `api/foto.php` | Query `f=nombre.jpg`, opcional `download=1`. | `basename()` contra Directory Traversal. Comprueba existencia física en `public/assets` y `uploads`. | `200 OK` (stream de imagen con encabezados de caché). `400` / `404`. | **COMPLETA Y VERIFICADA** |
| `/api/libro.php` | `GET` | `api/libro.php` | Ninguno. | Auto-siembra inicial si la tabla está vacía. Escanea los 19 videos en disco. Formatea fechas. | `200 OK` (mensajes, videos y total fotos). | **COMPLETA Y VERIFICADA** |
| `/api/libro.php` | `POST` | `api/libro.php` | JSON / POST: `nombre`, `mensaje`, `icono`, `color`, `media_ref`. | Nombre y mensaje obligatorios. Límite de caracteres. Color dentro de whitelist (`gold`, `emerald`, etc.). | `200 OK` (nuevo mensaje creado). `400 Bad Request`. | **COMPLETA Y VERIFICADA** |
| `/api/libro.php?action=like` | `POST` | `api/libro.php` | JSON: `id`. | `id > 0`. Incremento atómico en `libro_recuerdos`. | `200 OK` (`likes` actualizados). | **COMPLETA Y VERIFICADA** |
| `/api/video.php` | `GET` | `api/video.php` | Query `v=nombre.mp4`. | `basename()` contra Directory Traversal. Cabeceras `Accept-Ranges: bytes`. | `206 Partial Content` (streaming con range) o `200 OK`. `404 Not Found`. | **COMPLETA Y VERIFICADA** |
| `/api/canciones.php` | `GET` | `api/canciones.php` | Ninguno. | `LIMIT 50`. Salida sanitizada con `htmlspecialchars`. | `200 OK` (lista de canciones sugeridas). | **COMPLETA Y VERIFICADA** |
| `/api/canciones.php` | `POST` | `api/canciones.php` | JSON / POST: `cancion`, `nombre_invitado`. | Campo obligatorio, longitud máx 255. Anti-duplicados por comparación insensible a mayúsculas. | `200 OK` (canción guardada o aviso de ya existente). `400 Bad Request`. | **COMPLETA Y VERIFICADA** |
| `/api/invitados.php` | `GET` | `api/invitados.php` | Ninguno. | Genera códigos de rifa faltantes retroactivamente. Agrupa acompañantes por titular. | `200 OK` (lista completa de confirmados, totales y desglose). | **COMPLETA Y VERIFICADA** |

---

### 7. MÓDULO 6: PANTALLA EN VIVO PARA PROYECTOR (`public/en-vivo.php`)

| Funcionalidad | Archivos Responsables | Descripción | Datos que Utiliza | Dependencias | Estado Actual |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Carrusel de Fotos en Pantalla Completa** | `public/en-vivo.php`, `api/fotos.php` | Diseñado para televisores y videobeams en el salón de fiesta. Presenta diapositiva con marco esmeralda y oro, foto centrada, nombre del invitado que la tomó y su mensaje. | Datos de `api/fotos.php`. | JavaScript Timer, CSS transitions. | **COMPLETA Y VERIFICADA** |
| **Polling en Tiempo Real de Nuevas Fotos** | `public/en-vivo.php` | Consulta asíncrona cada 6 segundos a `api/fotos.php?since_id=X`. Si un invitado sube una foto en ese instante, se inserta prioritariamente en la cola de proyección. | Endpoint `api/fotos.php`. | `setInterval`, Fetch API. | **COMPLETA Y VERIFICADA** |
| **Código QR en Pantalla para Invitados** | `public/en-vivo.php` | Muestra en la esquina inferior un código QR dinámico que apunta a `#slide-11` de la invitación para que los presentes escaneen con su móvil y suban fotos directamente. | `https://api.qrserver.com/v1/create-qr-code/...`. | QR Server API externo. | **COMPLETA Y VERIFICADA** |
| **Modo Pantalla Completa y Pausa** | `public/en-vivo.php` | Botón interactivo para activar Fullscreen API del navegador y barra espaciadora para pausar el carrusel en una foto específica. | Fullscreen API del DOM. | JavaScript nativo. | **COMPLETA Y VERIFICADA** |
