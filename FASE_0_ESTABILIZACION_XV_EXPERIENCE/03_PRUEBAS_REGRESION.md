# 03. MATRIZ DE PRUEBAS DE REGRESIÓN Y NEGATIVAS — FASE 0

**Documento:** 03 de 07  
**Fecha:** 25 de noviembre de 2026  
**Entorno de Ejecución:** PHP 8.2.29 CLI / SQLite 3 / Windows 11  
**Runner:** `tests/suite_runner.php`  
**Resultado Global:** **32 / 32 PRUEBAS APROBADAS (100.0% PASS) — CERO REGRESIONES**

---

## 1. RESUMEN DE EJECUCIÓN

Se ejecutó la suite completa de pruebas unitarias, integrales y de regresión negativa sobre la rama `fase-0-seguridad-aislamiento`. La suite validó de forma exhaustiva los flujos de identidad visual, confirmaciones de asistencia, galerías fotográficas, libros de recuerdos, pantallas en vivo, seguridad de acceso y las 8 verificaciones específicas de aislamiento multi-tenant de la Fase 0.

---

## 2. MATRIZ COMPLETA DE PRUEBAS EJECUTADAS

| ID | Sección | Descripción del Caso de Prueba | Método / Entrada | Código Esperado | Código Real | Veredicto |
|---|---|---|---|---|---|---|
| **P1.1** | Identidad Visual | Portada y diseño de Angie Karolina (`/e/angie-xv`) | GET `/e/angie-xv` | 200 OK | 200 OK | **PASS** |
| **P1.2** | Identidad Visual | Portada y diseño de Sophia Valentina (`/e/sophia-xv`) | GET `/e/sophia-xv` | 200 OK | 200 OK | **PASS** |
| **P2.1** | Confirmaciones | Registro de confirmación en Sophia (`evento_id = 2`) | POST `/api/confirmar.php` | 200 OK | 200 OK | **PASS** |
| **P2.2** | Confirmaciones | Ausencia de invitado de Sophia en lista de Angie | SELECT count(*) WHERE ev=1 | 0 filas | 0 filas | **PASS** |
| **P2.3** | Confirmaciones | Prefijo de Pase VIP correspondiente a Sophia (`XV-SOPHIA`) | Payload QR generado | `XV-SOPHIA` | `XV-SOPHIA` | **PASS** |
| **P2.4** | Confirmaciones | Reconocimiento de duplicado y devolución de pase existente | POST `/api/confirmar.php` dup | 200 OK | 200 OK | **PASS** |
| **P3.1** | Fotografías | Subida física y registro de foto para Sophia (`evento_id = 2`) | POST `/api/fotos.php` base64 | 200 OK | 200 OK | **PASS** |
| **P3.2** | Fotografías | Subida física y registro de foto para Angie (`evento_id = 1`) | POST `/api/fotos.php` base64 | 200 OK | 200 OK | **PASS** |
| **P3.3** | Fotografías | Aislamiento de galería Sophia (excluye fotos de Angie) | GET `/api/fotos.php?evento_id=2` | 200 OK | 200 OK | **PASS** |
| **P3.4** | Fotografías | Aislamiento de galería Angie (excluye fotos de Sophia) | GET `/api/fotos.php?evento_id=1` | 200 OK | 200 OK | **PASS** |
| **P4.1** | Libro de Recuerdos | Publicación de dedicatoria en Sophia (`evento_id = 2`) | POST `/api/libro.php` | 200 OK | 200 OK | **PASS** |
| **P4.2** | Libro de Recuerdos | Publicación de dedicatoria en Angie (`evento_id = 1`) | POST `/api/libro.php` | 200 OK | 200 OK | **PASS** |
| **P4.3** | Libro de Recuerdos | Aislamiento de dedicatorias de Sophia (excluye Angie) | GET `/api/libro.php?evento_id=2` | 200 OK | 200 OK | **PASS** |
| **P4.4** | Libro de Recuerdos | Auditoría de videos y textos residuales en `api/libro.php` | GET `/api/libro.php?evento_id=2` | 0 videos | 0 videos | **PASS** |
| **P5.1** | Pantalla en Vivo | Pantalla en vivo de Sophia (`/en-vivo?evento=sophia-xv`) | GET `/public/en-vivo.php` | 200 OK | 200 OK | **PASS** |
| **P5.2** | Pantalla en Vivo | Pantalla en vivo de Angie (`/en-vivo?evento=angie-xv`) | GET `/public/en-vivo.php` | 200 OK | 200 OK | **PASS** |
| **P6.1** | Seguridad | Protección de acceso al panel administrativo (`/admin`) | GET `/admin/index.php` sin auth | 302 / Login | 302 Found | **PASS** |
| **P6.2** | Seguridad | Rechazo de archivos que no sean imágenes reales | POST `/api/fotos.php` .txt | 400 Bad Req | 400 Bad Req | **PASS** |
| **P6.3** | Seguridad | Sanitización contra XSS en dedicatorias | POST payload `<script>` | Escapado | `&lt;script&gt;` | **PASS** |
| **P6.4** | Seguridad | Protección de PII en `api/invitados.php` (Rechazo sin auth) | GET `/api/invitados.php` sin auth | 401 Unauth | 401 Unauth | **PASS** |
| **P7.1** | Álbum QR | Apertura de formulario móvil tras escanear QR Sophia | GET `/subir-fotos/sophia-xv` | 200 OK | 200 OK | **PASS** |
| **P7.2** | Álbum QR | Recepción y guardado de múltiples fotos por QR | 2 POSTs concurrentes | 200 OK | 200 OK | **PASS** |
| **P7.3** | Álbum QR | Aparición instantánea de fotos QR en la galería en vivo | GET `/api/fotos.php?evento_id=2` | Fotos presentes | Presentes | **PASS** |
| **P7.4** | Álbum QR | Moderación administrativa (ocultar foto de la proyección) | UPDATE visible=0 | Retirada feed | Retirada | **PASS** |
| **P8.1** | Regresión Fase 0 | Consulta autenticada de invitados para Angie (`evento_id = 1`) | GET `/api/invitados.php?evento=angie-xv` | 200 OK | 200 OK | **PASS** |
| **P8.2** | Regresión Fase 0 | Consulta autenticada de invitados para Sophia (`evento_id = 2`) | GET `/api/invitados.php?evento=sophia-xv` | 200 OK | 200 OK | **PASS** |
| **P8.3** | Negativa Fase 0 | Bloqueo de pase de Angie en check-in de Sophia | POST `/api/checkin.php` (cruzado) | 403 Forbid | 403 Forbid | **PASS** |
| **P8.4** | Negativa Fase 0 | Bloqueo de pase de Sophia en check-in de Angie | POST `/api/checkin.php` (cruzado) | 403 Forbid | 403 Forbid | **PASS** |
| **P8.5** | Regresión Fase 0 | Aislamiento de videos: Sophia recibe 0 videos de gala | GET `/api/libro.php?evento_id=2` | `videos: []` | `videos: []` | **PASS** |
| **P8.6** | Regresión Fase 0 | Aislamiento de playlist en `api/canciones.php` | POST/GET por evento | Aislado | Aislado | **PASS** |
| **P8.7** | Negativa Fase 0 | Slugs inexistentes devuelven 404 sin exponer a Angie | GET `/e/celebracion-falsa-404` | 404 Not Found | 404 Not Found | **PASS** |
| **P8.8** | Negativa Fase 0 | Bloqueo de Directory Traversal en `api/video.php` | GET `/api/video.php?v=../../db` | 400 Bad Req | 400 Bad Req | **PASS** |

---

## 3. VERIFICACIÓN DE REQUISITOS OBLIGATORIOS DEL USUARIO

- [x] **Angie consulta únicamente sus invitados:** Verificado en `P8.1`.
- [x] **Sophia consulta únicamente sus invitados:** Verificado en `P8.2`.
- [x] **Usuario no autorizado no puede consultar datos privados:** Verificado en `P6.4` (HTTP 401).
- [x] **Un pase de Angie no puede utilizarse en Sophia:** Verificado en `P8.3` (HTTP 403).
- [x] **Un pase de Sophia no puede utilizarse en Angie:** Verificado en `P8.4` (HTTP 403).
- [x] **Celebración sin videos no recibe videos de otra:** Verificado en `P8.5` (`[]`).
- [x] **Canciones se mantienen separadas:** Verificado en `P8.6`.
- [x] **Rutas inválidas no devuelven información de Angie:** Verificado en `P8.7` (HTTP 404).
- [x] **Archivos privados no son accesibles por manipulación de rutas:** Verificado en `P8.8` (HTTP 400).
- [x] **Funcionalidades originales de Angie continúan operativas:** Verificado en `P1.1`, `P3.2`, `P3.4`, `P4.2`, `P5.2` y `P8.1`.
