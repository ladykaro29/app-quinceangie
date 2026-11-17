# AUDITORÍA TÉCNICA Y FUNCIONAL — PROYECTO INVITACIONANGIE
## DOCUMENTO 07: AUDITORÍA DE SEGURIDAD Y CONTROL DE ACCESO

---

### 1. MATRIZ RESUMEN DE RIESGOS DE SEGURIDAD

A continuación se presenta la clasificación técnica de los riesgos detectados en el análisis estático y dinámico de solo lectura del código fuente:

| ID | Vulnerabilidad / Riesgo | Clasificación | Módulo / Archivo Afectado | Impacto Potencial |
| :---: | :--- | :---: | :--- | :--- |
| **SEC-01** | Endpoints de Check-in y Lista de Invitados sin Autenticación | **CRÍTICO** | `api/checkin.php`, `api/invitados.php` | Acceso público a datos personales y alteración no autorizada de asistencia |
| **SEC-02** | Contraseñas de Respaldo Hardcodeadas en Código Fuente | **ALTO** | `admin/index.php` (Línea 23) | Bypass de la variable de entorno y acceso indebido al panel de control |
| **SEC-03** | Ausencia de Tokens de Protección Anti-CSRF | **ALTO** | `admin/index.php` (Acciones POST) | Ejecución de borrado o cambios por parte de un admin víctima de phishing |
| **SEC-04** | Exposición de Mensajes de Error Detallados de Base de Datos | **MEDIO** | `api/confirmar.php`, `api/invitados.php` | Fuga de información técnica sobre estructura interna de tablas (Information Disclosure) |
| **SEC-05** | Ausencia de Rate Limiting (Protección contra Inundación) | **MEDIO** | `api/confirmar.php`, `api/fotos.php`, `api/canciones.php` | Denegación de servicio por llenado de disco o spam masivo en formularios |
| **SEC-06** | Encabezados CORS Permisivos Globales (`Access-Control-Allow-Origin: *`) | **MEDIO** | Todos los endpoints de `/api/*.php` | Cualquier sitio web de terceros puede consultar o disparar peticiones a la API |
| **SEC-07** | Desincronización en Borrado Físico de Archivos | **BAJO** | `admin/index.php`, `database/uploads/fotos/` | Archivos huérfanos que permanecen accesibles en el servidor tras ser eliminados |

---

### 2. DETALLE DE HALLAZGOS POR CATEGORÍA

#### A. Autenticación y Protección de Rutas Administrativas
1. **Contraseñas Fijas en el Código (Riesgo Alto):**
   - En `admin/index.php`:
     ```php
     $validPasswords = array_unique(array_filter([
         ADMIN_PASS,
         'angie2026',
         'angie15'
     ]));
     ```
   - **Vulnerabilidad:** Aunque el administrador configure una contraseña compleja en el archivo `.env`, el código siempre aceptará `angie2026` y `angie15` debido a esta lista predeterminada.
2. **Endpoints Administrativos Expuestos al Público (Riesgo Crítico):**
   - El endpoint `api/checkin.php` permite modificar el estado de asistencia real de cualquier invitado mediante una petición POST con `{ "action": "toggle" }` o `{ "action": "checkin" }`. **No valida sesión de administrador ni token de autorización**.
   - El endpoint `api/invitados.php` devuelve en formato JSON la lista completa de invitados con sus nombres completos, estado de asistencia y acompañantes. **Cualquier usuario anónimo puede leer la lista completa de asistentes**.

#### B. Validación de Formularios y Protección contra Inyección SQL
- **Evaluación Positiva:**
  - El proyecto utiliza **sentencias preparadas PDO con paso de parámetros tipados** (`$stmt->prepare()` y `$stmt->execute([':param' => $valor])`) en prácticamente todas sus consultas de inserción, actualización y consulta.
  - La emulación de preparaciones está desactivada de forma segura en `config/database.php` (`PDO::ATTR_EMULATE_PREPARES => false`).
  - No se identificaron concatenaciones directas de variables de usuario dentro de cadenas SQL en los endpoints públicos.

#### C. Cross-Site Scripting (XSS) y Sanitización
- **Evaluación Positiva:**
  - En `api/canciones.php`, `api/libro.php` y `api/fotos.php`, los campos de texto son sanitizados mediante `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')` antes de emitir las respuestas JSON.
  - En `public/index.php`, el nombre completo del invitado en el RSVP es validado mediante expresión regular estricta (`preg_match('/^[\p{L}\s\.\-\']+$/u', $nombreCompleto)`), rechazando etiquetas HTML, `<script>` o caracteres especiales.
- **Área de Atención Menor:**
  - En algunos puntos del panel de administración y del libro de recuerdos, se hace uso de `.innerHTML` en JavaScript para inyectar textos de retorno. Aunque los datos provienen filtrados de la API, la práctica recomendada es utilizar `.textContent` o frameworks que escapen el DOM por defecto.

#### D. Protección contra Cross-Site Request Forgery (CSRF)
- **Vulnerabilidad (Riesgo Alto):**
  - Los formularios dentro de `admin/index.php` (para eliminar invitados, borrar fotos de prueba, purgar todas las fotos o alternar check-in) procesan solicitudes `POST` directas sin verificar un token de un solo uso (*anti-CSRF token*).
  - Si un administrador con sesión abierta visita un enlace malicioso en otra pestaña, una petición oculta podría forzar la eliminación de la base de datos de invitados (`delete_all_guests`).

#### E. Subida Segura de Archivos Multimedia (File Upload Security)
- **Evaluación del Módulo `api/fotos.php`:**
  1. **Validación de Tamaño:** Correcta. Rechaza archivos de más de 12 MB (`file['size'] > 12 * 1024 * 1024`).
  2. **Validación de Tipo MIME Real:** Correcta. Utiliza `finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmp_name)` para inspeccionar la firma binaria del archivo, restringiendo a `image/jpeg`, `image/png`, `image/webp`, `image/heic`.
  3. **Generación de Nombres en Servidor:** Excelente. No conserva el nombre original del archivo subido por el usuario (evitando ejecución de scripts con nombres como `shell.php.jpg`). Genera un nombre pseudoaleatorio criptográfico: `'angie_xv_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext`.
  4. **Protección contra Directory Traversal:**
     - En `api/foto.php` y `api/video.php`, el parámetro de entrada es filtrado estrictamente con `basename($_GET['f'])`, lo que imposibilita ataques de evasión de directorios tipo `../../../../etc/passwd`.

#### F. Manejo de Errores y Fuga de Información Técnica
- **Vulnerabilidad (Riesgo Medio):**
  - En los bloques `catch (\PDOException $e)` de `api/confirmar.php` y `api/invitados.php`, el mensaje devuelto al cliente incluye `$e->getMessage()`.
  - En caso de un fallo de sintaxis o conexión, el navegador del cliente recibe detalles internos como nombres de tablas, columnas o rutas absolutas del servidor, lo que facilita el reconocimiento a atacantes.

---

### 3. RECOMENDACIONES DE MITIGACIÓN PRIORITARIAS

1. **Eliminar las contraseñas de respaldo cableadas:** Permitir únicamente la contraseña inyectada por la variable de entorno `ADMIN_PASS`.
2. **Proteger la API con Tokens de Sesión / Bearer:**
   - Exigir que `api/checkin.php` y `api/invitados.php` validen la sesión administrativa activa o requieran una cabecera `Authorization: Bearer <token_secreto>`.
3. **Implementar Tokens CSRF en el Panel de Administración:**
   - Generar `$_SESSION['csrf_token'] = bin2hex(random_bytes(32))` y validar su coincidencia en cada operación destructiva `POST`.
4. **Generalizar Mensajes de Error en la API:**
   - Registrar el detalle técnico en los logs del servidor con `error_log()` y responder al usuario un mensaje genérico ("Ocurrió un error al procesar su solicitud").
