# ARQUITECTURA DE SOFTWARE — PLATAFORMA "XV EXPERIENCE"
## DOCUMENTO 08: ESTRATEGIA DE MIGRACIÓN DE LA CELEBRACIÓN DE ANGIE

---

### 1. OBJETIVO DE LA MIGRACIÓN

Garantizar que la celebración existente de **Angie Karolina Avendaño Rivera** se convierta en el **Evento #1** de la nueva plataforma **XV Experience** sin pérdida de información, sin romper los enlaces que ya han sido compartidos a los familiares por WhatsApp y preservando intactos sus 160 recuerdos fotográficos, 19 videos y dedicatorias registradas.

---

### 2. MAPEO DE DATOS ACTUALES HACIA EL NUEVO MODELO RELACIONAL

| Dato / Registro Actual | Ubicación Actual (Auditada) | Destino en el Nuevo Modelo | Valores Mapeados para Angie |
| :--- | :--- | :--- | :--- |
| **Cliente / Contratante** | No existía tabla de clientes | Tabla `clientes` (ID: 1) | `nombre_contacto`: "Edwin Avendaño / Lady Rivera", `email`: "familia.avendano@correo.com", `ciudad`: "Mérida". |
| **Paquete Contratado** | No existía tabla de paquetes | Tabla `paquetes` (ID: 3) | Asignado al paquete `royal_diamond` (todos los módulos ilimitados). |
| **Registro de Evento** | Datos fijos en código PHP | Tabla `eventos` (ID: 1) | `slug`: `'angie-xv'`, `nombre_festejado`: `'Angie Karolina'`, `fecha_evento`: `'2026-10-03 20:00:00'`, `plantilla_id`: 1 (`esmeralda-real`). |
| **Configuración del Evento** | HTML hardcodeado | Tabla `configuraciones_evento` (ID: 1, `evento_id`: 1) | Textos de la historia de fantasía, nombres de padres ("Edwin Johan Avendaño", "Lady Rivera"), salón Colegio de Médicos, paleta esmeralda/oro, 5 preguntas de trivia e itinerario completo. |
| **160 Fotos Oficiales** | `fotos_fiesta` en SQLite | Tabla `fotografias_evento` (`evento_id`: 1) | Se conservan los 160 registros con sus respectivos archivos `angie_xv_recuerdo_001.jpg` a `160.jpg`, likes asignados y `es_oficial = 1`. |
| **19 Videos de Momentos** | Array `$titulosMomentos` en `api/libro.php` | Tabla `videos_evento` (`evento_id`: 1) | Se insertan 19 filas con títulos ("Llegada triunfal", "Vals con Papá", etc.), tamaño de archivo y nombres `angie_xv_video_01.mp4` a `19.mp4`. |
| **5 Dedicatorias del Libro** | `libro_recuerdos` en SQLite | Tabla `dedicatorias_recuerdos` (`evento_id`: 1) | Se migran íntegras las 5 dedicatorias (Familia Avendaño, Tía Carmen, Mejores Amigas, Padrinos y Tía Elena) con sus likes, emojis y fechas. |

---

### 3. SCRIPT DE MIGRACIÓN SQL Y REORDENAMIENTO DE ARCHIVOS

#### A. Inserción de Datos Maestros de Angie:
```sql
-- 1. Insertar Plantilla de Angie
INSERT INTO plantillas (id, codigo, nombre, version, ruta_vistas, activo)
VALUES (1, 'esmeralda-real', 'Esmeralda Real & Oro', '1.0.0', 'themes/esmeralda-real', 1)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- 2. Insertar Cliente y Pedido
INSERT INTO clientes (id, nombre_contacto, email, telefono, ciudad)
VALUES (1, 'Familia Avendaño Rivera', 'contacto.angie15@ejemplo.com', '+584140000000', 'Mérida')
ON DUPLICATE KEY UPDATE nombre_contacto=VALUES(nombre_contacto);

INSERT INTO pedidos (id, cliente_id, paquete_id, codigo_pedido, estado, total, fecha_expiracion)
VALUES (1, 1, 3, 'PED-ANGIE-2026', 'aprobado', 0.00, '2027-01-01 00:00:00')
ON DUPLICATE KEY UPDATE estado=VALUES(estado);

-- 3. Crear el Evento #1
INSERT INTO eventos (id, pedido_id, plantilla_id, slug, subdominio, tipo_evento, nombre_festejado, titulo_evento, fecha_evento, fecha_nacimiento, timezone, estado)
VALUES (1, 1, 1, 'angie-xv', 'angie', 'xv_anos', 'Angie Karolina', 'Mis XV Años', '2026-10-03 20:00:00', '2026-09-23 00:00:00', 'America/Caracas', 'publicado')
ON DUPLICATE KEY UPDATE slug=VALUES(slug);

-- 4. Inyectar la Configuración Estética y de Contenidos de Angie
INSERT INTO configuraciones_evento (
    evento_id, nombre_padre, nombre_madre, direccion_salon, nombre_salon, url_google_maps,
    dress_code_titulo, dress_code_descripcion, dress_code_colores_json, audio_musica_url,
    foto_portada_url, foto_despedida_url, itinerario_json, trivia_preguntas_json, tokens_estilo_css_json, modulos_habilitados_json
) VALUES (
    1,
    'Edwin Johan Avendaño',
    'Lady Rivera',
    'Colegio de Médicos del Estado Mérida, Av. Urdaneta, Mérida, Venezuela',
    'Colegio de Médicos',
    'https://maps.google.com/?q=Colegio+de+Medicos+del+Estado+Merida+Av+Urdaneta+Merida+Venezuela',
    'ELEGANTE',
    'Te invitamos a vestirte elegante para esta noche especial. Por favor reserva el color verde esmeralda para la quinceañera.',
    '["#C8A24A", "#E7D49A", "#FFF8EC", "#1A1410"]',
    'storage/media/1/audio/musica.mp3',
    'storage/media/1/covers/angie-portada.jpg',
    'storage/media/1/covers/angie-xv.jpg',
    '[{"hora":"8:00 PM","actividad":"Recepción de invitados"},{"hora":"9:00 PM","actividad":"Entrada de la Quinceañera"},{"hora":"9:30 PM","actividad":"El tradicional Vals y Brindis"},{"hora":"10:15 PM","actividad":"Pasapalos & Cócteles especiales"},{"hora":"11:00 PM","actividad":"Sorpresa & Baile"},{"hora":"12:00 AM","actividad":"Hora Loca & Fiesta"}]',
    '[{"pregunta":"¿Cuál es el color favorito de Angie?","opciones":["Rosa","Verde esmeralda","Azul cielo","Morado"],"correcta":1,"feedback_correcto":"¡Exacto! El verde esmeralda es su favorito 💚✨","feedback_incorrecto":"La respuesta correcta es: Verde esmeralda 💚"},{"pregunta":"¿Cuál es la actividad favorita de Angie?","opciones":["La repostería","Pintar","Bailar","Fotografía"],"correcta":0,"feedback_correcto":"¡Así es! A Angie le fascina la repostería 🧁🎂","feedback_incorrecto":"La respuesta correcta es: La repostería 🧁🎂"},{"pregunta":"¿Cuál es la comida favorita de Angie?","opciones":["Pizza","Pasticho","Hamburguesa","Sushi"],"correcta":1,"feedback_correcto":"¡Totalmente! El pasticho es su plato preferido 🍝🧀","feedback_incorrecto":"La respuesta correcta es: Pasticho 🍝🧀"},{"pregunta":"¿Cuál es el género musical favorito de Angie?","opciones":["Reggaetón","Pop","Salsa","Baladas"],"correcta":0,"feedback_correcto":"¡A romper la pista! El reggaetón es su ritmo favorito 🎶🔥","feedback_incorrecto":"La respuesta correcta es: Reggaetón 🎶🔥"},{"pregunta":"¿Cuándo es el cumpleaños de Angie?","opciones":["23 de Septiembre","3 de Octubre","15 de Septiembre","28 de Septiembre"],"correcta":0,"feedback_correcto":"¡Exacto! Nació el 23 de septiembre y lo celebramos el 3 de octubre 🎂🎉","feedback_incorrecto":"Su cumpleaños es el 23 de septiembre 🎂"}]',
    '{"--color-primary":"#062E25","--color-secondary":"#006B4F","--color-gold":"#C8A24A","--color-gold-light":"#E7D49A","--color-bg":"#021410"}',
    '{"tarjeta_historia":true,"cuenta_regresiva":true,"trivia":true,"playlist":true,"lluvia_sobres":true,"rsvp":true,"fotos_envivo":true,"libro_3d":true,"proyector_tv":true,"sorteo_rifa":true,"checkin_qr":true}'
) ON DUPLICATE KEY UPDATE nombre_padre=VALUES(nombre_padre);
```

#### B. Migración de Dedicatorias y Medios:
Se ejecuta una migración de datos desde las tablas previas hacia las nuevas tablas multi-tenant, asignando `evento_id = 1` a cada registro:
```sql
-- Migración de dedicatorias
INSERT INTO dedicatorias_recuerdos (evento_id, nombre_firmante, mensaje, icono_emoji, color_tema, likes, ip_origen, created_at)
SELECT 1, nombre, mensaje, icono, color, likes, ip, created_at FROM libro_recuerdos;

-- Migración de fotos
INSERT INTO fotografias_evento (evento_id, nombre_autor, mensaje, archivo_nombre, url_recurso, storage_provider, likes, ip_origen, visible, es_oficial, created_at)
SELECT 1, nombre_invitado, mensaje, archivo, CONCAT('storage/media/1/photos/', archivo), 'local', likes, ip, visible, 1, created_at FROM fotos_fiesta;
```

---

### 4. CONSOLIDACIÓN DE ASSETS Y ELIMINACIÓN DE DUPLICADOS

Para eliminar los 145 MB redundantes identificados en la auditoría, se realiza la unificación física hacia `storage/media/1/`:

1. Se conservan los archivos de `public/assets/fotos/` y se mueven a `storage/media/1/photos/`.
2. Se conservan los archivos de `public/assets/videos/` y se mueven a `storage/media/1/videos/`.
3. Se mueven `angie-portada.jpg` y `angie-xv.jpg` a `storage/media/1/covers/`.
4. Se mueven `musica.mp3` y `musica.m4a` a `storage/media/1/audio/`.
5. Se eliminan las copias redundantes de `database/uploads/`.

---

### 5. GARANTÍA DE RETROCOMPATIBILIDAD DE ENLACES (ZERO BROKEN LINKS)

Para que ningún familiar que haya recibido el enlace de Angie experimente un error 404, el Front Controller incorpora reglas de redirección transparente permanente (HTTP 301):

| Enlace Antiguo | Comportamiento del Router | Destino Nuevo |
| :--- | :---: | :--- |
| `https://dominio/` o `/index.php` | Redirección 301 o Carga por Defecto del Evento 1 | `https://dominio/evento/angie-xv` |
| `https://dominio/libro-recuerdos.php` | Redirección 301 | `https://dominio/evento/angie-xv/libro-recuerdos` |
| `https://dominio/public/en-vivo.php` | Redirección 301 | `https://dominio/evento/angie-xv/en-vivo` |
| `https://dominio/admin/` | Redirección al panel con contexto | `https://dominio/admin/login?evento=angie-xv` |
| Enlaces de fotos compartidas | Redirección dinámica en `PhotoApiController` | Servidas desde `storage/media/1/photos/...` |
