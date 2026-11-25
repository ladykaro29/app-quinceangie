# FASE 2: INFORME DE PRUEBAS FUNCIONALES Y SUITE AUTOMATIZADA

---

## 1. Resumen de Ejecución
- **Fecha:** 25 de noviembre de 2026.
- **Entorno de Prueba:** Runner automatizado aislado (`tests/suite_runner.php` con ejecutor subproceso HTTP).
- **Total de Pruebas Ejecutadas:** 40.
- **Pruebas Aprobadas:** 40 (100.0%).
- **Pruebas con Observaciones o Fallos:** 0.

---

## 2. Matriz Detallada de Pruebas

| ID | Sección | Descripción del Escenario | Estado |
| :--- | :--- | :--- | :--- |
| **P1.1** | Identidad Visual | Portada e Identidad Visual de Angie Karolina (`/e/angie-xv`) | ✅ APROBADO |
| **P1.2** | Identidad Visual | Portada e Identidad Visual de Sophia Valentina (`/e/sophia-xv`) | ✅ APROBADO |
| **P2.1** | Confirmaciones | Registro de confirmación en Sophia (`evento_id = 2`) | ✅ APROBADO |
| **P2.2** | Confirmaciones | Ausencia de invitado de Sophia en lista de Angie (`evento_id = 1`) | ✅ APROBADO |
| **P2.3** | Confirmaciones | Prefijo de Pase VIP correspondiente a la celebración (`XV-SOPHIA`) | ✅ APROBADO |
| **P2.4** | Confirmaciones | Reconocimiento de invitado ya registrado y devolución de su Pase VIP | ✅ APROBADO |
| **P3.1** | Fotografías | Subida física y registro de fotografía para Sophia (`evento_id = 2`) | ✅ APROBADO |
| **P3.2** | Fotografías | Subida física y registro de fotografía para Angie (`evento_id = 1`) | ✅ APROBADO |
| **P3.3** | Fotografías | Aislamiento de galería Sophia (excluye fotos de Angie y recuerdos oficiales) | ✅ APROBADO |
| **P3.4** | Fotografías | Aislamiento de galería Angie (excluye fotos de Sophia) | ✅ APROBADO |
| **P4.1** | Libro Recuerdos | Publicación de dedicatoria en Sophia (`evento_id = 2`) | ✅ APROBADO |
| **P4.2** | Libro Recuerdos | Publicación de dedicatoria en Angie (`evento_id = 1`) | ✅ APROBADO |
| **P4.3** | Libro Recuerdos | Aislamiento de dedicatorias de Sophia (excluye dedicatorias de Angie) | ✅ APROBADO |
| **P4.4** | Libro Recuerdos | Auditoría de videos y textos residuales en `api/libro.php` | ✅ APROBADO |
| **P5.1** | Pantalla en Vivo | Pantalla en vivo de Sophia (`/en-vivo?evento=sophia-xv`) | ✅ APROBADO |
| **P5.2** | Pantalla en Vivo | Pantalla en vivo de Angie (`/en-vivo?evento=angie-xv`) | ✅ APROBADO |
| **P6.1** | Seguridad | Protección de acceso al panel administrativo (`/admin`) | ✅ APROBADO |
| **P6.2** | Seguridad | Rechazo de subida de archivos que no sean imágenes reales | ✅ APROBADO |
| **P6.3** | Seguridad | Sanitización y prevención de inyección XSS en Libro de Recuerdos | ✅ APROBADO |
| **P6.4** | Seguridad | Protección de datos privados en `api/invitados.php` (HTTP 401) | ✅ APROBADO |
| **P7.1** | Álbum QR | Apertura de formulario móvil tras escanear QR (`/subir-fotos/sophia-xv`) | ✅ APROBADO |
| **P7.2** | Álbum QR | Recepción y guardado de múltiples fotos enviadas por QR | ✅ APROBADO |
| **P7.3** | Álbum QR | Aparición de fotos autorizadas en la galería en vivo de Sophia | ✅ APROBADO |
| **P7.4** | Álbum QR | Moderación administrativa de fotos QR (ocultar/desaprobar foto) | ✅ APROBADO |
| **P8.1** | Aislamiento F0 | Consulta autenticada de invitados filtrada por Angie (`evento_id = 1`) | ✅ APROBADO |
| **P8.2** | Aislamiento F0 | Consulta autenticada de invitados filtrada por Sophia (`evento_id = 2`) | ✅ APROBADO |
| **P8.3** | Aislamiento F0 | Bloqueo de pase de Angie en check-in de Sophia (`HTTP 403 Forbidden`) | ✅ APROBADO |
| **P8.4** | Aislamiento F0 | Bloqueo de pase de Sophia en check-in de Angie (`HTTP 403 Forbidden`) | ✅ APROBADO |
| **P8.5** | Aislamiento F0 | Aislamiento de videos: Sophia no recibe videos de Angie (`[]`) | ✅ APROBADO |
| **P8.6** | Aislamiento F0 | Aislamiento de sugerencias musicales en `api/canciones.php` | ✅ APROBADO |
| **P8.7** | Aislamiento F0 | Rutas con slug inexistente devuelven 404 y no exponen datos de Angie | ✅ APROBADO |
| **P8.8** | Aislamiento F0 | Bloqueo de Directory Traversal en `api/video.php` (`HTTP 400`) | ✅ APROBADO |
| **P9.1** | Fase 2 Multi | Idempotencia de subida por `subida_uuid` (Reintento sin duplicación) | ✅ APROBADO |
| **P9.2** | Fase 2 Multi | Deduplicación de foto exacta por hash SHA-256 en 24h | ✅ APROBADO |
| **P9.3** | Fase 2 Multi | Endpoint de estadísticas de almacenamiento y moderación (`action=stats`) | ✅ APROBADO |
| **P9.4** | Fase 2 Multi | Acción de moderación administrativa en lote (`action=aprobar`) | ✅ APROBADO |
| **P9.5** | Fase 2 Multi | Alternancia de fotografía destacada (`action=destacar`) | ✅ APROBADO |
| **P9.6** | Fase 2 Multi | Acción de moderación administrativa en lote (`action=rechazar`) | ✅ APROBADO |
| **P9.7** | Fase 2 Multi | Generación de tarjeta imprimible con QR en alta resolución (10×15 cm) | ✅ APROBADO |
| **P10.1**| Concurrencia | Simulación de ráfagas de 10, 25, 50 y 100 subidas con integridad referencial| ✅ APROBADO |

---

## 3. Limpieza y Restauración Posterior a las Pruebas
Todos los registros y archivos generados durante la batería de pruebas fueron purgados automáticamente al finalizar la suite (190 fotos de prueba, registros de invitados y dedicatorias temporales), dejando la base de datos limpia con únicamente los datos legítimos de Angie y Sophia.
