# 11 — AUDITORÍA DE CIBERSEGURIDAD Y PROTECCIÓN DE DATOS
**Plataforma:** XV Experience  
**Fecha:** 25 de Noviembre de 2026  
**Especialidad:** Ingeniero Senior en Ciberseguridad y Seguridad de Aplicaciones Web (OWASP)

---

## 1. MATRIZ DE HALLAZGOS Y CLASIFICACIÓN DE VULNERABILIDADES

```
PIRÁMIDE DE VULNERABILIDADES:
├── P0 (Crítico):  0 hallazgos
├── P1 (Alto):     1 hallazgo   -> Exposición pública y transversal de lista de invitados
├── P2 (Medio):    3 hallazgos  -> Ausencia de tokens CSRF, falta de Rate Limiting, CORS wildcard
└── P3 (Bajo):     2 hallazgos  -> Falta de headers de seguridad HTTP, credenciales hardcoded por defecto
```

---

## 2. HALLAZGOS DETALLADOS DE CIBERSEGURIDAD

### [P1 - ALTO] SEC-01: Exposición Pública y Transversal de Invitados Confirmados
- **Archivo Afectado:** [api/invitados.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/api/invitados.php#L33-L45)
- **Evidencia:**
  ```php
  $stmtInvitados = $pdo->query('SELECT id, nombre_completo, asistira, codigo_rifa, created_at FROM invitados ORDER BY created_at DESC');
  ```
- **Impacto:** Cualquier persona que envíe un `GET /api/invitados.php` recibe el JSON completo con los nombres y apellidos de los invitados de **todas las celebraciones** sin requerir token ni sesión. Representa un riesgo de privacidad y fuga de datos personales (BOLA/IDOR).
- **Recomendación:** Proteger el endpoint con sesión administrativa o incorporar obligatoriamente el parámetro `evento_id` firmado y limitarlo únicamente a conteo numérico si es consultado desde el frontend público.

---

### [P2 - MEDIO] SEC-02: Ausencia de Protección contra CSRF en Formularios Públicos
- **Archivos Afectados:** [api/confirmar.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/api/confirmar.php), [api/fotos.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/api/fotos.php), [api/libro.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/api/libro.php)
- **Evidencia:** Cabecera `Access-Control-Allow-Origin: *` con aceptación de payloads JSON sin validar token anti-CSRF (`$_SESSION['csrf_token']`).
- **Impacto:** Sitios web maliciosos de terceros podrían disparar envíos forzados de confirmaciones o dedicatorias спам simulando ser un invitado.
- **Recomendación:** Implementar tokens CSRF por sesión o restringir CORS al dominio oficial de la aplicación.

---

### [P2 - MEDIO] SEC-03: Ausencia de Rate Limiting en Subida de Fotos y Dedicatorias
- **Archivos Afectados:** [api/fotos.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/api/fotos.php), [api/libro.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/api/libro.php)
- **Evidencia:** No existen límites de peticiones por minuto por IP en la capa de aplicación.
- **Impacto:** Un script automatizado podría inundar el servidor enviando cientos de imágenes hasta agotar el espacio en disco del contenedor Docker (ataque DoS por llenado de disco).
- **Recomendación:** Incorporar límite de velocidad en Apache (`mod_ratelimit` / `mod_evasive`) o validar en PHP un máximo de 5 fotos por IP cada 10 minutos.

---

### [P2 - MEDIO] SEC-04: Regex Rígido en Escaneo de Pases VIP en Puerta
- **Archivo Afectado:** [api/checkin.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/api/checkin.php#L51)
- **Evidencia:**
  ```php
  if (!$invitado && preg_match('/XVANGIE-(\d+)/i', $rawCode, $matches)) {
  ```
- **Impacto:** El personal de recepción que intente escanear códigos de pases generados para Sophia Valentina (`XVSOPHIA-0002-...`) experimentará rechazo en la puerta de la fiesta por regla fija de texto.
- **Recomendación:** Generalizar la expresión regular a `preg_match('/XV[A-Z0-9]+-(\d+)/i', $rawCode, $matches)`.

---

### [P3 - BAJO] SEC-05: Contraseñas por Defecto en Código
- **Archivo Afectado:** [admin/index.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/admin/index.php#L28-L33)
- **Evidencia:** Arreglo con claves conocidas `angie2026`, `sophia2026`, `angie15` como respaldo si falla el login por base de datos.
- **Recomendación:** Forzar que la autenticación administrativa dependa exclusivamente de contraseñas hasheadas en base de datos (`usuarios`) y variables de entorno (`ADMIN_PASS`).

---

### [P3 - BAJO] SEC-06: Cabeceras de Seguridad HTTP Incompletas
- **Archivo Afectado:** [.htaccess](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/.htaccess) / [Dockerfile](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/Dockerfile)
- **Evidencia:** No se envían cabeceras como `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN` ni `Referrer-Policy: strict-origin-when-cross-origin`.
- **Recomendación:** Añadir directivas `Header set` en `.htaccess`.

---

## 3. PUNTOS FUERTES DE SEGURIDAD CONFIRMADOS
- ✅ **Cero Inyecciones SQL:** 100% de consultas parametrizadas con PDO en el núcleo de la aplicación.
- ✅ **Sanitización contra XSS:** Escapado riguroso con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- ✅ **Validación de Archivos Reales:** Detección de binarios mediante `finfo_file` para impedir la subida de scripts PHP disfrazados de imagen.
- ✅ **Protección de Base de Datos:** `.htaccess` deniega el acceso HTTP directo a archivos `.sqlite`, `.env` y `.sql`.
