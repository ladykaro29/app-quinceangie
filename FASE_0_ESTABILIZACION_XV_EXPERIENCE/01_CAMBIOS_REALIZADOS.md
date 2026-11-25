# 01. INVENTARIO DE CAMBIOS TÉCNICOS REALIZADOS — FASE 0

**Documento:** 01 de 07  
**Fecha:** 25 de noviembre de 2026  
**Rama:** `fase-0-seguridad-aislamiento`  
**Proyecto:** XV Experience

---

## 1. RESUMEN DE ARCHIVOS MODIFICADOS

Se intervinieron 7 archivos del núcleo del sistema y 1 archivo de pruebas automatizadas:

| Archivo | Tipo de Intervención | Objetivo |
|---|---|---|
| `app/Middleware/TenantResolverMiddleware.php` | Lógica de resolución | Evitar fallback silencioso a Angie cuando se solicita un slug inexistente o inválido. |
| `public/index.php` | Control de flujo | Interrumpir ejecución con HTTP 404 amigable si la celebración solicitada no existe. |
| `public/libro-recuerdos.php` | Control de flujo | Manejar HTTP 404 si el libro solicitado pertenece a una celebración inexistente. |
| `api/invitados.php` | Seguridad y Aislamiento | Exigir autenticación (sesión o token), resolver evento activo y filtrar todas las consultas por `evento_id`. |
| `api/checkin.php` | Seguridad y Aislamiento | Eliminar prefijo fijo `XVANGIE`, validar pertenencia estricta del pase al evento activo y bloquear pases cruzados (HTTP 403). |
| `api/libro.php` | Lógica de datos | Aislar la entrega de los 19 videos originales exclusivamente a Angie y generar mensajes dinámicos de agradecimiento. |
| `api/canciones.php` | Aislamiento multi-tenant | Incorporar resolución de `evento_id`, asociar sugerencias a su celebración y aislar la lista de reproducción. |
| `api/video.php` | Hardening de seguridad | Exigir extensión `.mp4` y bloquear peticiones con Directory Traversal o archivos no multimedia (HTTP 400). |
| `tests/suite_runner.php` | QA y Automatización | Actualizar prueba de `api/invitados.php` y agregar suite `PRUEBA 8` con 8 pruebas negativas y de regresión. |

---

## 2. DETALLE TÉCNICO DE MODIFICACIONES POR ARCHIVO

### 1. `app/Middleware/TenantResolverMiddleware.php`
- **Problema previo:** Si el usuario visitaba `/e/evento-falso`, la consulta en BD devolvía null y el middleware ejecutaba `SELECT * FROM eventos WHERE id = 1 LIMIT 1`, asignando silenciosamente a Angie Karolina.
- **Solución implementada:** Se agregó la bandera booleana `$isExplicit = !empty($slug)`. Si un slug fue solicitado explícitamente y no se encuentra en la base de datos, el método retorna inmediatamente `false` sin realizar fallback. El fallback a ID 1 queda reservado exclusivamente para la ruta raíz (`/`) donde no se especifica slug.

### 2. `public/index.php` y `public/libro-recuerdos.php`
- **Problema previo:** Ambos archivos llamaban a `TenantResolverMiddleware::resolve()` sin verificar su valor de retorno, ejecutando renderizados con variables vacías o inconsistentes ante slugs erróneos.
- **Solución implementada:**
```php
if (!TenantResolverMiddleware::resolve()) {
    http_response_code(404);
    echo "<!DOCTYPE html><html lang='es'>...<h1>404 · Celebración No Encontrada</h1>...</html>";
    exit;
}
```

### 3. `api/invitados.php`
- **Problema previo:** No exigía autenticación; cualquier usuario de internet podía hacer `GET /api/invitados.php` y obtener nombres, asistencia, acompañantes y códigos de rifa de todas las celebraciones mezcladas.
- **Solución implementada:**
  1. Autenticación dual obligatoria: Sesión de administrador (`$_SESSION['admin_auth']`, `AuthService::check()`) o encabezado `Authorization: Bearer <TOKEN>`. Sin credenciales, retorna `401 Unauthorized`.
  2. Resolución mandatoria del evento activo mediante `CurrentEventContext` o `TenantResolverMiddleware::resolve()`.
  3. Filtrado de consultas SQL:
     ```sql
     SELECT id, evento_id, nombre_completo, asistira, codigo_rifa, asistio_evento, checkin_at, created_at 
     FROM invitados WHERE evento_id = :evento_id ORDER BY created_at DESC;
     
     SELECT a.id, a.invitado_id, a.nombre_completo, a.codigo_rifa 
     FROM acompanantes a INNER JOIN invitados i ON a.invitado_id = i.id 
     WHERE i.evento_id = :evento_id ORDER BY a.id ASC;
     ```

### 4. `api/checkin.php`
- **Problema previo:** Requería obligatoriamente el prefijo `XVANGIE-` e intentaba buscar pases por ID o nombre sin verificar si el pase pertenecía a la celebración activa.
- **Solución implementada:**
  1. Autenticación de operador mediante sesión, Bearer token o parámetro seguro `operator_key`.
  2. Detección de prefijos cruzados (`XV-ANGIE` en Sophia o `XV-SOPHIA` en Angie) con respuesta inmediata `403 Forbidden` (`cross_event: true`).
  3. Verificación de pertenencia de registro:
     ```php
     $guestEventId = (int)($candidateGuest['evento_id'] ?? 1);
     if ($guestEventId !== $eventoId) {
         http_response_code(403);
         echo json_encode([
             'success' => false,
             'error' => "Pase no válido para esta celebración. El pase pertenece a la celebración ID {$guestEventId}.",
             'cross_event' => true
         ]);
         exit;
     }
     ```
  4. Control de duplicados: Si el invitado ya tiene `asistio_evento == 1`, no se sobrescribe `checkin_at` y se retorna `already_checked: true` indicando la hora exacta de su ingreso previo.

### 5. `api/libro.php`
- **Problema previo:** Retornaba los 19 videos de Angie como fallback forzoso para cualquier celebración y emitía un mensaje de éxito con el texto fijo *"de Angie"*.
- **Solución implementada:**
  1. Condicional estricto: Los 19 videos se cargan únicamente si `$eventoId === 1`.
  2. Para cualquier otra celebración (como Sophia, `evento_id = 2`), se busca en su subcarpeta dedicada o se retorna una colección vacía `[]`.
  3. El mensaje de respuesta POST resuelve el nombre de la festejada mediante `CurrentEventContext` o consulta a `eventos`: `"¡Dedicatoria guardada en el libro de recuerdos de Sophia con éxito! ✨"`.

### 6. `api/canciones.php`
- **Problema previo:** Las sugerencias de canciones se insertaban y consultaban sin columna ni filtro de `evento_id`.
- **Solución implementada:**
  1. Resolución del evento activo y consulta filtrada: `SELECT ... FROM canciones WHERE evento_id = :evento_id`.
  2. En el guardado (POST): Se inserta obligatoriamente `evento_id` y se valida que la canción no esté duplicada *dentro de la misma celebración*.

### 7. `api/video.php`
- **Problema previo:** Aceptaba cualquier nombre de archivo y lo buscaba en las carpetas públicas sin validar el tipo de recurso.
- **Solución implementada:** Se aplica `basename()` contra Directory Traversal y se exige terminación estricta en `.mp4`:
  ```php
  if (empty($filename) || !str_ends_with(strtolower($filename), '.mp4')) {
      http_response_code(400);
      exit('Archivo no especificado o formato no permitido');
  }
  ```
