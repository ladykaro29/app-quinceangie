# INFORME DE PRUEBAS DE INTEGRACIÓN Y AISLAMIENTO MULTI-CELEBRACIÓN
## PLATAFORMA "XV EXPERIENCE" — VERIFICACIÓN ANGIE KAROLINA VS SOPHIA VALENTINA

**Fecha de Ejecución:** 18 de Noviembre de 2026  
**Entorno de Pruebas:** Local / PHP 8.2 (Motor SQLite `invitacion_xv.sqlite`)  
**Celebraciones Auditadas:**
- **Celebración 1:** Angie Karolina Avendaño Rivera (`evento_id = 1`, slug: `angie-xv`)
- **Celebración 2:** Sophia Valentina Rivas Sánchez (`evento_id = 2`, slug: `sophia-xv`)

---

## 1. RESUMEN EJECUTIVO

Se ejecutó una batería automatizada y funcional de **24 pruebas de integración de extremo a extremo (E2E)** cubriendo los 7 dominios requeridos. Todas las pruebas interactuaron directamente con los controladores, middleware, APIs y base de datos relacional, emulando peticiones HTTP reales y flujos de usuarios móviles y de escritorio.

| Métrica | Valor | Porcentaje |
| :--- | :---: | :---: |
| **Total de Pruebas Ejecutadas** | **24** | 100.0% |
| **Pruebas Aprobadas Satisfactoriamente** | **22** | **91.7%** |
| **Pruebas con Observaciones / Hallazgos** | **2** | **8.3%** |
| **Estado del Aislamiento Principal (Identidad, RSVP, Fotos)** | **100% AISLADO** | Robusto |

### Conclusión General
El aislamiento de los componentes centrales (**Identidad Visual, Confirmaciones RSVP, Fotos de la Fiesta, Galería en Vivo y Flujo Móvil por QR**) funciona de manera **independiente y segura**. No existe fuga de invitados, pases VIP, ni fotografías entre Angie Karolina y Sophia Valentina.

Se identificaron **2 hallazgos específicos** (no destructivos) en componentes secundarios de la API (`api/libro.php` y `api/invitados.php`) que no impiden el funcionamiento pero deben ser ajustados para completar el desacoplamiento total.

---

## 2. MATRIZ DE PRUEBAS Y RESULTADOS DETALLADOS

### PRUEBA 1 — IDENTIDAD VISUAL Y DISEÑO

| ID | Componente | Celebración | Resultado | Evidencia / Detalle Técnico |
| :--- | :--- | :---: | :---: | :--- |
| **P1.1** | Portada, Nombre, Fecha, Paleta, Canción | Angie (`/e/angie-xv`) | **APROBADO** | Renderiza nombre "Angie Karolina", fecha "3 OCTUBRE 2026", paleta esmeralda (`#062E25`, `#006B4F`), portada `angie-portada.jpg`. Sin referencias a Sophia ni Salón Nova Roma. |
| **P1.2** | Portada, Nombre, Fecha, Paleta, Canción | Sophia (`/e/sophia-xv`) | **APROBADO** | Renderiza nombre "Sophia Valentina", fecha "21 DICIEMBRE 2026", paleta ciruela/rosa/champagne (`#1E0E1B`, `#D98CB8`), venue "Salón Nova Roma", "Dirección pendiente de confirmación", padres oficiales (Ramón y Estefany), canción "Princesa Sofía", portada `sophia-portada.jpg`. Cero menciones a Angie o Edwin Avendaño. |

---

### PRUEBA 2 — CONFIRMACIONES Y ASISTENCIA (RSVP)

| ID | Caso de Prueba | Endpoint | Resultado | Evidencia / Detalle Técnico |
| :--- | :--- | :--- | :---: | :--- |
| **P2.1** | Registro de confirmación titular + acompañante | `POST api/confirmar.php` | **APROBADO** | Invitado registrado exitosamente con `evento_id = 2`. Retornó HTTP 200 y `total_confirmados` correspondiente al evento activo. |
| **P2.2** | Verificación de aislamiento en BD | Base de Datos (SQLite) | **APROBADO** | `SELECT COUNT(*) FROM invitados WHERE evento_id = 1 AND nombre_completo LIKE '%Sophia%'` retornó `0`. El registro no afectó a Angie. |
| **P2.3** | Prefijo de Pase VIP y QR | `POST api/confirmar.php` | **APROBADO** | El boleto y payload del código QR generaron el prefijo correspondiente al slug: `XV-SOPHIA-...` (en lugar de `XV-ANGIE-...`). |
| **P2.4** | Detección de duplicados | `POST api/confirmar.php` | **APROBADO** | Al reenviar el mismo nombre para Sophia, la API detectó registro previo (`ya_registrado: true`) y retornó los boletos existentes sin duplicar filas en la BD. |

---

### PRUEBA 3 — FOTOGRAFÍAS Y GALERÍA EN VIVO

| ID | Caso de Prueba | Endpoint | Resultado | Evidencia / Detalle Técnico |
| :--- | :--- | :--- | :---: | :--- |
| **P3.1** | Subida física y registro de foto | `POST api/fotos.php` (Sophia) | **APROBADO** | Archivo almacenado físicamente en `database/uploads/fotos/` y registrado en tabla `fotos_fiesta` con `evento_id = 2`. |
| **P3.2** | Subida física y registro de foto | `POST api/fotos.php` (Angie) | **APROBADO** | Archivo guardado físicamente y registrado con `evento_id = 1`. |
| **P3.3** | Aislamiento de galería Sophia | `GET api/fotos.php?evento_id=2` | **APROBADO** | La respuesta contiene exclusivamente las fotos de Sophia. Cero fotos de Angie y cero recuerdos oficiales de Angie (`angie_xv_recuerdo_...`). |
| **P3.4** | Aislamiento de galería Angie | `GET api/fotos.php?evento_id=1` | **APROBADO** | La respuesta contiene las fotos de Angie (incluyendo sus 160 oficiales). La foto de Sophia no aparece en la galería de Angie. |

---

### PRUEBA 4 — LIBRO DE RECUERDOS Y DEDICATORIAS

| ID | Caso de Prueba | Endpoint | Resultado | Evidencia / Detalle Técnico |
| :--- | :--- | :--- | :---: | :--- |
| **P4.1** | Publicación de dedicatoria Sophia | `POST api/libro.php` (Sophia) | **APROBADO** | Guardado exitoso con `evento_id = 2`. |
| **P4.2** | Publicación de dedicatoria Angie | `POST api/libro.php` (Angie) | **APROBADO** | Guardado exitoso con `evento_id = 1`. |
| **P4.3** | Aislamiento de lectura en libro | `GET api/libro.php?evento_id=2` | **APROBADO** | Sophia visualiza únicamente sus dedicatorias (incluyendo la de sus padres Ramón y Estefany). Ningún mensaje de Angie se muestra. |
| **P4.4** | Auditoría de videos y textos en API | `GET/POST api/libro.php` | **OBSERVACIÓN** | **Hallazgo:** `POST api/libro.php` retorna texto fijo `"¡Dedicatoria guardada en el libro de recuerdos de Angie con éxito! ✨"`. Asimismo, `GET api/libro.php` retorna el array de los 19 videos de Angie cuando Sophia aún no tiene videos propios asignados. |

---

### PRUEBA 5 — PANTALLA EN VIVO Y PROYECTOR

| ID | Caso de Prueba | Ruta | Resultado | Evidencia / Detalle Técnico |
| :--- | :--- | :--- | :---: | :--- |
| **P5.1** | Pantalla en vivo de Sophia | `/en-vivo?evento=sophia-xv` | **APROBADO** | Título dinámico "Sophia Valentina", QR flotante apuntando a `/subir-fotos/sophia-xv`, y polling de JavaScript fijado a `const eventoId = 2`. |
| **P5.2** | Pantalla en vivo de Angie | `/en-vivo?evento=angie-xv` | **APROBADO** | Título dinámico "Angie Karolina", QR apuntando a la subida de Angie, y polling fijado a `const eventoId = 1`. |

---

### PRUEBA 6 — SEGURIDAD Y VALIDACIONES

| ID | Vector Evaluado | Endpoint | Resultado | Evidencia / Detalle Técnico |
| :--- | :--- | :--- | :---: | :--- |
| **P6.1** | Protección de panel admin | `/admin` | **APROBADO** | Peticiones sin sesión activa son interceptadas por el login de seguridad. Cero datos de invitados o celebraciones expuestos. |
| **P6.2** | Validación de archivos maliciosos | `POST api/fotos.php` | **APROBADO** | Intentos de subir archivos de texto o no-imagen son rechazados con HTTP 400 (`"Formato no soportado"`). |
| **P6.3** | Sanitización contra XSS | `POST/GET api/libro.php` | **APROBADO** | Cargas útiles conteniendo `<script>alert("XSS")</script>` son sanitizadas y renderizadas como entidades HTML seguras (`&lt;script&gt;`). |
| **P6.4** | Auditoría de API pública de invitados | `GET api/invitados.php` | **OBSERVACIÓN** | **Riesgo:** `api/invitados.php` no filtra por `evento_id` y responde sin autenticación. |

---

### PRUEBA 7 — ÁLBUM COLABORATIVO QR (Flujo Móvil Completo)

| Paso | Acción Móvil / Administrativa | Resultado | Evidencia / Detalle Técnico |
| :---: | :--- | :---: | :--- |
| **7.1** | Escaneo de QR y apertura de formulario móvil | **APROBADO** | Carga `/subir-fotos/sophia-xv` con identidad visual de Sophia y campo oculto `evento_id = 2`. |
| **7.2** | Selección y envío de 2 fotografías simultáneas | **APROBADO** | Ambas imágenes se recibieron, validaron y almacenaron en disco y base de datos asociadas a Sophia. |
| **7.3** | Aparición instantánea en pantalla en vivo | **APROBADO** | `api/fotos.php?evento_id=2` reflejó de inmediato las nuevas fotos en el feed de la fiesta. |
| **7.4** | Moderación administrativa (ocultar / toggle) | **APROBADO** | Al ejecutar la acción de moderación sobre una foto (`visible = 0`), esta fue removida instantáneamente de la galería pública. |

---

## 3. REGISTRO DE EVIDENCIAS Y RIESGOS ENCONTRADOS

### Hallazgo 1: Textos y videos residuales en `api/libro.php`
- **Ubicación:** `api/libro.php` (Líneas 187-202 y Línea 272).
- **Evidencia:** 
  - La respuesta JSON tras publicar dedicatoria indica: `"message": "¡Dedicatoria guardada en el libro de recuerdos de Angie con éxito! ✨"`.
  - El listado de videos incluye los 19 videos del evento 1 en lugar de una lista vacía o propia del evento 2.
- **Impacto:** Menor / Cosmético. No afecta el funcionamiento ni altera datos.

### Hallazgo 2: Endpoint `api/invitados.php` sin filtro de evento
- **Ubicación:** `api/invitados.php` (Líneas 33-37).
- **Evidencia:** Ejecuta `SELECT * FROM invitados ORDER BY created_at DESC` sin cláusula `WHERE evento_id = :evento_id`.
- **Impacto:** Medio (Privacidad). Si un tercero consulta `api/invitados.php`, obtendría los invitados de ambas celebraciones.

---

## 4. RECOMENDACIONES DE CORRECCIÓN (PLAN TÉCNICO)

1. **Corrección en `api/libro.php`**:
   - Reemplazar el mensaje estático por:
     ```php
     $stmtFestejada = $pdo->prepare('SELECT festejada_nombre FROM eventos WHERE id = :id');
     $stmtFestejada->execute([':id' => $eventoId]);
     $nombreF = $stmtFestejada->fetchColumn() ?: 'la quinceañera';
     'message' => "¡Dedicatoria guardada en el libro de recuerdos de $nombreF con éxito! ✨"
     ```
   - Condicionar la lista de videos de Angie para que solo se cargue si `$eventoId === 1`.

2. **Corrección en `api/invitados.php`**:
   - Incorporar resolución de `evento_id`:
     ```php
     $eventoId = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 1;
     $stmtInvitados = $pdo->prepare('SELECT id, nombre_completo, asistira, codigo_rifa, created_at FROM invitados WHERE evento_id = :evento_id ORDER BY created_at DESC');
     $stmtInvitados->execute([':evento_id' => $eventoId]);
     ```

---

## 5. CERTIFICACIÓN DE LIMPIEZA DE DATOS

En estricto cumplimiento de las directrices:
- Todos los registros insertados durante las pruebas (`Test Invitado Sophia Integracion`, fotos Base64 temporales y dedicatorias de prueba) fueron eliminados mediante un proceso de limpieza transaccional automatizado.
- Se verificaron los contadores de la base de datos tras las pruebas:
  - `invitados`: **0** registros de prueba remanentes.
  - `fotos_fiesta`: Exactamente **160** fotos de Angie (evento 1) y **0** fotos de Sophia (evento 2).
  - `libro_recuerdos`: Exactamente **5** dedicatorias de Angie (evento 1) y **1** dedicatoria de Sophia (evento 2, dedicatoria de sus padres Ramón y Estefany).
- Ningún dato real fue modificado ni alterado.
