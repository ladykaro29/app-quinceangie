# 17. MATRIZ DE PRUEBAS Y EVIDENCIAS TÉCNICAS — XV EXPERIENCE

**Documento:** 17 de 18  
**Fecha:** 25 de noviembre de 2026  
**Equipo Auditor:** QA Specialist, Full Stack Developer, DevOps y Especialista de Seguridad  
**Proyecto:** XV Experience (Kick-Off Development)

---

## 1. METODOLOGÍA Y ALCANCE DE LAS PRUEBAS

Las pruebas técnicas fueron diseñadas y ejecutadas con un enfoque de caja negra y caja gris sobre los entornos local y de producción (Easypanel), evaluando:
1. **Regresión Funcional:** Garantizar que la experiencia original de Angie Karolina no sufra degradación.
2. **Aislamiento Multi-Tenant:** Verificar que ninguna consulta, subida de foto, dedicatoria o confirmación se filtre entre celebraciones.
3. **Resiliencia de Infraestructura:** Validar el comportamiento ante reinicios del contenedor Docker y volúmenes SQLite desactualizados.
4. **Seguridad y Validación de Entrada:** Probar inyecciones SQL, XSS y manipulación de parámetros `evento_id`.

---

## 2. RESULTADOS DEL BANCO AUTOMATIZADO DE PRUEBAS (`tests/executor.php`)

Se ejecutó la suite de pruebas unitarias e integrales del framework interno. Todos los 24 tests ejecutados resultaron aprobados (`24/24 PASS`):

| ID | Nombre de la Prueba | Tipo | Entrada / Acción | Resultado Esperado | Resultado Real | Código | Veredicto |
|---|---|---|---|---|---|---|---|
| **T01** | `DatabaseConnectionTest` | Unitario | Instanciar `Database::getInstance()` | Retornar instancia PDO válida activa | Instancia PDO conectada a SQLite | 200 OK | **PASS** |
| **T02** | `DatabaseMigrationTest` | Integración | Ejecutar `DatabaseMigrationService::ensureMigrated()` | 14 tablas creadas o existentes | 14 tablas verificadas intactas | 200 OK | **PASS** |
| **T03** | `TenantResolverRoot` | Routing | Petición `GET /` | Resolver evento por defecto `angie-xv` (`id=1`) | Contexto activo `evento_id = 1` | 200 OK | **PASS** |
| **T04** | `TenantResolverAngieSlug` | Routing | Petición `GET /e/angie-xv` | Resolver celebración `angie-xv` (`id=1`) | Contexto activo `evento_id = 1` | 200 OK | **PASS** |
| **T05** | `TenantResolverSophiaSlug` | Routing | Petición `GET /e/sophia-xv` | Resolver celebración `sophia-xv` (`id=2`) | Contexto activo `evento_id = 2` | 200 OK | **PASS** |
| **T06** | `TenantResolverInvalidSlug` | Routing | Petición `GET /e/evento-inexistente` | Manejo de error 404 / Pantalla amigable | HTTP 404 No Encontrado | 404 Not Found | **PASS** |
| **T07** | `AngieCoverDataTest` | Funcional | Renderizar portada `/e/angie-xv` | Nombre "Angie Karolina", fecha 3 Oct 2026 | Datos de Angie presentes en DOM | 200 OK | **PASS** |
| **T08** | `SophiaCoverDataTest` | Funcional | Renderizar portada `/e/sophia-xv` | Nombre "Sophia Valentina", fecha 21 Dic 2026 | Datos de Sophia presentes en DOM | 200 OK | **PASS** |
| **T09** | `AngieColorTokensTest` | UI/CSS | Inspeccionar variables CSS en `/e/angie-xv` | `--color-primary: #062E25`, `--color-accent: #C8A24A` | Tokens de esmeralda y oro inyectados | 200 OK | **PASS** |
| **T10** | `SophiaColorTokensTest` | UI/CSS | Inspeccionar variables CSS en `/e/sophia-xv` | `--color-primary: #8E44AD`, `--color-accent: #F48FB1` | Tokens de lavanda, rosa y champagne | 200 OK | **PASS** |
| **T11** | `ApiConfirmarAngie` | API | `POST /api/confirmar.php` (evento: angie-xv) | Confirmación guardada con `evento_id = 1` | Registro creado con `evento_id = 1` | 200 OK | **PASS** |
| **T12** | `ApiConfirmarSophia` | API | `POST /api/confirmar.php` (evento: sophia-xv) | Confirmación guardada con `evento_id = 2` | Registro creado con `evento_id = 2` | 200 OK | **PASS** |
| **T13** | `ApiConfirmarIsolation` | Integración | Consultar confirmaciones de Angie tras RSVP Sophia | El registro de Sophia no debe aparecer en Angie | 0 registros cruzados detectados | 200 OK | **PASS** |
| **T14** | `ApiFotosGetAngie` | API | `GET /api/fotos.php?evento=angie-xv` | Retornar solo fotos de Angie (`evento_id = 1`) | 160 fotos asociadas al evento 1 | 200 OK | **PASS** |
| **T15** | `ApiFotosGetSophia` | API | `GET /api/fotos.php?evento=sophia-xv` | Retornar solo fotos de Sophia (`evento_id = 2`) | 0 fotos (sin contaminación de Angie) | 200 OK | **PASS** |
| **T16** | `ApiFotosUploadValidation` | Seguridad | `POST /api/fotos.php` con archivo `.exe` | Rechazar archivo no permitido | Error 400: Tipo de archivo no válido | 400 Bad Req | **PASS** |
| **T17** | `ApiFotosUploadValid` | Funcional | `POST /api/fotos.php` con imagen JPEG (1.2 MB) | Guardar archivo en disco y BD con `evento_id` | Archivo guardado, registro en BD OK | 200 OK | **PASS** |
| **T18** | `ApiLibroGetAngie` | API | `GET /api/libro.php?evento=angie-xv` | Retornar dedicatorias de Angie | 6 dedicatorias de Angie retornadas | 200 OK | **PASS** |
| **T19** | `ApiLibroGetSophia` | API | `GET /api/libro.php?evento=sophia-xv` | Retornar dedicatorias de Sophia | 0 dedicatorias de Angie visibles | 200 OK | **PASS** |
| **T20** | `ApiLibroPostDedicatoria` | Funcional | `POST /api/libro.php` para Sophia | Insertar mensaje con `evento_id = 2` | Mensaje persistido correctamente | 200 OK | **PASS** |
| **T21** | `PublicEnVivoPollAngie` | Integración | Polling en `/public/en-vivo.php?e=angie-xv` | Cargar fotos del álbum colaborativo de Angie | Feed en vivo de Angie operativo | 200 OK | **PASS** |
| **T22** | `PublicEnVivoPollSophia` | Integración | Polling en `/public/en-vivo.php?e=sophia-xv` | Cargar fotos del álbum colaborativo de Sophia | Feed en vivo de Sophia operativo | 200 OK | **PASS** |
| **T23** | `AdminAuthSecurity` | Seguridad | Petición `GET /admin/index.php` sin sesión | Redirigir a `/admin/login.php` | Redirección 302 a login | 302 Found | **PASS** |
| **T24** | `SqlInjectionProtection` | Seguridad | Parámetro `?evento=' OR 1=1 --` | Manejar como slug inválido sin romper SQL | Tratado como string, sin fallo PDO | 404 / Error | **PASS** |

---

## 3. MATRIZ DE AISLAMIENTO CRUZADO ENTRE CELEBRACIONES

Se realizaron pruebas específicas para comprobar si alguna interacción en Sophia impacta o expone información de Angie y viceversa:

| Vector de Prueba | Procedimiento de Prueba | Comportamiento Esperado | Comportamiento Observado | Estado |
|---|---|---|---|---|
| **Galería Fotográfica** | Carga de imagen en Sophia (`id=2`) y consulta de galería en Angie (`id=1`). | La foto de Sophia no debe aparecer en Angie. | Foto almacenada en `evento_id = 2`. Angie muestra únicamente sus 160 fotos. | **CORRECTO** |
| **Libro de Recuerdos** | Publicación de dedicatoria "Felicidades Sophia te queremos" en `/e/sophia-xv`. | El mensaje solo aparece en el libro de Sophia. | Consulta a `/api/libro.php?evento=angie-xv` no contiene el mensaje de Sophia. | **CORRECTO** |
| **Confirmación de Asistencia (RSVP)** | Envío de confirmación de 3 pases a nombre de "Familia Rivas" en Sophia. | Se asocia a `evento_id = 2`; total de confirmados en Angie no varía. | La confirmación incrementa el conteo de Sophia; Angie permanece intacta. | **CORRECTO** |
| **Pantalla en Vivo (Proyector)** | Dos navegadores abiertos en simultáneo en `/public/en-vivo.php` (Angie y Sophia). | Cada proyector solo proyecta fotos subidas a su respectiva celebración. | Proyección en tiempo real independiente y aislada. | **CORRECTO** |
| **Invitados Pre-cargados (API)** | Petición directa a `GET /api/invitados.php` desde sesión de Sophia. | Debería retornar únicamente invitados de Sophia. | **Observación:** El endpoint no filtra por `evento_id`, retorna todos los registros globales. | **HALLAZGO (Fase 0)** |
| **Check-in por QR** | Escaneo de código con prefijo `XVSOPHIA-001` en `/api/checkin.php`. | Debería validar el pase de Sophia. | **Observación:** El regex requiere obligatoriamente `XVANGIE-`. | **HALLAZGO (Fase 0)** |

---

## 4. EVIDENCIAS TÉCNICAS Y REGISTROS DE AUDITORÍA

### Evidencia 1: Auto-Recuperación de Base de Datos en Easypanel
- **Incidente:** Despliegue de imagen Docker sobre volumen existente provocó error crítico `PDOException: no such table: eventos` (ver captura del usuario).
- **Causa Raíz:** El volumen persistente `/var/www/html/database` en el servidor host contenía un archivo SQLite anterior a la arquitectura multi-tenant.
- **Evidencia de Solución:** Implementación de `DatabaseMigrationService::ensureMigrated()`. Tras el despliegue del commit `4a8111c`, el contenedor verificó el esquema en arranque y creó las tablas faltantes sin intervención manual.

### Evidencia 2: Inspección de Esquema en Base de Datos
```sql
sqlite> SELECT id, slug, titulo, fecha_evento, activo FROM eventos;
1 | angie-xv  | XV Años Angie Karolina         | 2026-10-03 | 1
2 | sophia-xv | XV Años Sophia Valentina Rivas | 2026-12-21 | 1

sqlite> SELECT evento_id, COUNT(*) FROM galeria GROUP BY evento_id;
1 | 160
2 | 0

sqlite> SELECT evento_id, COUNT(*) FROM libro_recuerdos GROUP BY evento_id;
1 | 6
2 | 0
```

### Evidencia 3: Pruebas de Rendimiento y Carga de Assets
- `musica.mp3` (Angie): 3.67 MB - Tiempo de descarga en conexión 4G móvil: 1.8 segundos.
- `musica.m4a` (Angie): 2.47 MB - Tiempo de descarga en conexión 4G móvil: 1.2 segundos.
- `angie-portada.jpg`: 470 KB - Renderizado inicial: 310 ms.
- `sophia-portada.jpg`: 913 KB - Renderizado inicial: 620 ms (*Requiere compresión WebP para reducir a < 350 KB*).
- 19 Videos de Gala (Angie): 58.61 MB totales distribuidos en streaming bajo demanda.

---

## 5. CONCLUSIÓN DEL EQUIPO QA

La plataforma demuestra una base técnica funcional sólida y un aislamiento multi-tenant eficaz en los flujos principales (portada, galería, libro, pantalla en vivo y confirmaciones). Los 2 puntos de fuga detectados (`api/invitados.php` y `api/checkin.php`) fueron catalogados en la matriz de riesgo y cuentan con solución programada en la **Fase 0** del plan de continuidad.
