# INFORME DE AUDITORÍA DE CONSISTENCIA MULTI-CELEBRACIÓN
## PLATAFORMA "XV EXPERIENCE" — DIAGNÓSTICO PROFUNDO ANGIE VS. SOPHIA

> **FECHA DE AUDITORÍA:** 18 de Noviembre de 2026  
> **OBJETIVO:** Certificar el aislamiento estricto entre celebraciones (`angie-xv` y `sophia-xv`) y erradicar cualquier fuga de datos o acoplamiento (*hardcoding*).  
> **CONDICIÓN:** Auditoría analítica estricta sin modificación de archivos ni base de datos durante la evaluación.

---

## 1. RESUMEN EJECUTIVO DEL DIAGNÓSTICO

La plataforma ha avanzado significativamente en su capa estructural (Base de datos relacional de 15 tablas, Autoloader PSR-4, `TenantResolverMiddleware` y RBAC en el Panel Administrativo).

Sin embargo, la auditoría exhaustiva del código fuente revela una **asimetría técnica crítica**:
- **El Backend y Base de Datos:** Tienen soporte multi-tenant con columnas `evento_id` y aislamiento implementado.
- **Los Archivos Frontend Históricos y Endpoints Tradicionales (`public/index.php`, `public/libro-recuerdos.php`, `public/en-vivo.php`, `api/confirmar.php`, `api/fotos.php`):** Aún conservan **63 coincidencias fijas (*hardcoded*)** de Angie Karolina, y los endpoints de confirmación RSVP y fotos todavía insertan y leen datos de forma global sin filtrar por `evento_id`.

**Conclusión Principal:** Si bien el commit `ef5b67b` dinamizó la portada en `public/index.php`, **las tarjetas internas (historia, fecha en JS, trivia, itinerario, confirmación RSVP, pases VIP, fotos y libro de recuerdos) siguen operando con la lógica y datos de Angie Karolina**, lo que provoca que un invitado que visite `/e/sophia-xv` vea la historia familiar de Angie, preguntas de trivia de Angie y descargue un pase VIP que dice "XV Angie".

---

## 2. MATRIZ COMPARATIVA Y DE CONSISTENCIA: ANGIE VS. SOPHIA

| Módulo / Funcionalidad | Celebración Angie (`/e/angie-xv`) | Celebración Sophia (`/e/sophia-xv`) | Estado de Consistencia | Riesgo Detectado |
| :--- | :--- | :--- | :---: | :--- |
| **Resolución de Tenant (Core)** | Resuelve `evento_id = 1` | Resuelve `evento_id = 2` | ✅ **Correcto** | El middleware resuelve el slug adecuadamente. |
| **Tarjeta 0: Portada** | Muestra "Angie Karolina" y "3 OCTUBRE 2026" | Muestra "Sophia Valentina" y "14 NOVIEMBRE 2026" | ✅ **Correcto** | Dinamizado en commit `ef5b67b`. |
| **Tarjeta 1: Historia Familiar** | Historia de Angie con padres "Edwin y Lady" | ⚠️ **Muestra "Edwin y Lady"** | ❌ **HARDCODED** | Texto fijo en línea 2874 de `public/index.php`. |
| **Tarjeta 2: Cuenta Regresiva** | 3 de Octubre de 2026 | ⚠️ **Calcula hacia Octubre 2026** | ❌ **HARDCODED** | JS usa `Date('2026-10-03T20:00:00-04:00')` (Línea 3663). |
| **Tarjeta 3: Nombres Padres** | Edwin Johan Avendaño y Lady Rivera | Carlos Mendoza y Mariana Silva | ✅ **Correcto** | Dinamizado en commit `ef5b67b`. |
| **Tarjeta 4: Salón y Fecha** | Colegio de Médicos (Mérida) | Hotel Eurobuilding (Caracas) | ⚠️ **Parcial** | Salón es dinámico, pero la fecha en texto dice "Sábado, 3 de Octubre" y "(Cumpleaños de Angie: 23 de Septiembre)" en líneas 2994-2996. |
| **Tarjeta 6: Itinerario** | 6 momentos de Angie | ⚠️ **Muestra itinerario de Angie** | ❌ **HARDCODED** | No consume `itinerario_json` de la base de datos. |
| **Tarjeta 7: Trivia Interactiva** | Preguntas sobre Angie (Repostería, color, cumple) | ⚠️ **Preguntas sobre Angie** | ❌ **HARDCODED** | Arreglo JS con las 5 preguntas de Angie fijas en líneas 3717-3749. |
| **Tarjeta 10: RSVP (Confirmar)** | Registra asistencia | ⚠️ **Registra a nombre de Angie** | 🚨 **MEZCLA DE DATOS** | `api/confirmar.php` no recibe ni guarda `evento_id`. Todos los invitados van a una sola lista global. |
| **Pase VIP con QR** | Genera código `XVANGIE-XXXX` | ⚠️ **Genera `XVANGIE-XXXX`** | ❌ **HARDCODED** | Prefijo fijo en `api/confirmar.php` y en script de canvas de `public/index.php`. |
| **Libro de Recuerdos 3D** | Dedicatorias de Angie (5) | ⚠️ **Muestra fotos y videos de Angie** | 🚨 **MEZCLA DE DATOS** | `public/libro-recuerdos.php` no está conectado a `CurrentEventContext`. Lista las 160 fotos y 19 videos de Angie fijos en JS. |
| **Subida de Fotos Móvil (QR)** | Sube a álbum de Angie | ⚠️ **Sube a la raíz global** | 🚨 **MEZCLA DE DATOS** | `public/subir-fotos.php` envía a `../api/fotos.php`, el cual guarda sin `evento_id`. |
| **Pantalla en Vivo (`/en-vivo`)** | Proyecta fotos de Angie | ⚠️ **Dice "Los XV de Angie" y muestra fotos de Angie** | ❌ **HARDCODED** | Cabecera fija y consulta `api/fotos.php` que no filtra por evento. |
| **Panel de Administración** | Permite filtrar por Angie (#1) | Permite filtrar por Sophia (#2) | ✅ **Correcto** | Selector dinámico implementado en Sprint 6 con aislamiento RBAC. |

---

## 3. INVENTARIO DETALLADO DE DATOS HARDCODED Y ERRORES

### A. Datos Críticos en `public/index.php` (Frontend)
1. **Historia Familiar (Tarjeta 1, Línea 2874):**
   - *Texto:* `...padres, <strong>Edwin y Lady</strong>.`
   - *Clasificación:* **Dato que debe ser dinámico** (extraer de `configuraciones_evento.frase_biblica` o `familia.padre / madre`).
2. **Fecha de la Cuenta Regresiva (JavaScript, Línea 3663):**
   - *Código:* `const eventDate = new Date('2026-10-03T20:00:00-04:00').getTime();`
   - *Efecto:* En Sophia, el reloj regresivo calcula hacia el 3 de octubre en lugar del 14 de noviembre.
   - *Clasificación:* **Dato que debe ser dinámico** (inyectar `<?= $timestampEvento * 1000 ?>`).
3. **Tarjeta de Lugar y Cumpleaños (Líneas 2994-2996):**
   - *Texto:* `<div class="info-value">Sábado, 3 de Octubre de 2026</div>` y `(Cumpleaños de Angie: 23 de Septiembre 🎂)`
   - *Clasificación:* **Dato que debe ser dinámico** (usar fecha del evento y `configuraciones_evento.fecha_nacimiento`).
4. **Google Calendar Button (Línea 2928):**
   - *Enlace:* URL fija con textos de Angie y Colegio de Médicos Mérida.
   - *Clasificación:* **Dato que debe ser dinámico** (usar `configuraciones_evento.google_calendar_url`).
5. **Trivia "¿Cuánto me conoces?" (JavaScript, Líneas 3717-3749):**
   - *Código:* Preguntas fijas sobre color favorito, repostería, comida y cumpleaños del 23 de septiembre de Angie.
   - *Clasificación:* **Dato que debe ser dinámico** (inyectar `<?= json_encode($currentConfigs['trivia_json']) ?>`).
6. **Ticket Pase VIP QR (HTML y JS, Líneas 3276, 3305, 4181, 4212):**
   - *Textos:* `Angie Karolina`, `Colegio de Médicos, Mérida`, `XVANGIE-XXXX`, `XV-ANGIE | PASE #...`
   - *Clasificación:* **Dato que debe ser dinámico** (usar nombre de la festejada, salón y slug del evento para el prefijo QR).
7. **Mensajes Rápidos de Galería y Lightbox (Líneas 3523, 3559, 5048):**
   - *Textos:* `"¡Felicidades Angie! 🎉"`, `"✨ ¡Celebrando los XV de Angie Karolina!"`, download `"foto_angie_xv.jpg"`.
   - *Clasificación:* **Dato que debe ser dinámico**.

---

### B. Endpoints de API sin Aislamiento Multi-Tenant
1. **`api/confirmar.php` (Confirmación RSVP):**
   - *Fallo:* La consulta `INSERT INTO invitados (nombre_completo, asistira, codigo_rifa)` **no incluye la columna `evento_id`**.
   - *Consecuencia Gravísima:* Todas las confirmaciones de invitados (sean de Angie o de Sophia) se mezclan en la misma tabla sin distinguir la fiesta.
   - *Fallo en Código QR:* Hardcodea el prefijo `XVANGIE-` y la sal `'angie2026'` para el hash criptográfico (Líneas 173 y 263).
2. **`api/fotos.php` (Galería y Carga de Fotos):**
   - *Fallo:* En el `GET`, consulta `SELECT ... FROM fotos_fiesta WHERE visible = 1` **sin cláusula `WHERE evento_id = :evento_id`**. Sophia ve las 160 fotos de Angie.
   - *Fallo en `POST`:* Inserta sin `evento_id` y nombra los archivos como `'angie_xv_' . date(...)`.
3. **`api/checkin.php` (Escáner de Puerta):**
   - *Fallo:* Busca pases únicamente con el patrón regex `/XVANGIE-(\d+)/i`. Si se escanea un pase de Sophia con prefijo propio, el escáner fallará.

---

### C. Páginas Satélite sin Integración Multi-Celebración
1. **`public/en-vivo.php` (Pantalla para Proyector):**
   - Título, h1 y mensajes dicen fijos: `"📸 En Vivo · Los XV de Angie Karolina"`.
   - Llama a `api/fotos.php` sin pasar el parámetro `?evento=...`. Proyecta fotos de Angie en cualquier celebración.
2. **`public/libro-recuerdos.php` (Grimorio 3D):**
   - El archivo público no consume el componente desacoplado `templates/components/book-3d/grimoire-book.php` ni invoca `TenantResolverMiddleware`.
   - Tiene en su script interno (Líneas 1785-1803) los 19 videos y las 160 fotos de Angie escritos en código duro.

---

## 4. ARCHIVOS QUE REQUIEREN MODIFICACIÓN Y PRIORIDAD

| Prioridad | Archivo Responsable | Modificación Necesaria | Riesgo si no se corrige |
| :---: | :--- | :--- | :--- |
| 🚨 **CRÍTICA (P1)** | `api/confirmar.php` | Capturar `evento_id`, asociarlo al `INSERT` de invitados y dinamizar el prefijo QR (`XV-SLUG`). | **Corrupción y mezcla de listas de invitados.** |
| 🚨 **CRÍTICA (P1)** | `api/fotos.php` | Filtrar `SELECT` por `evento_id` y guardar en `INSERT` el `evento_id` activo. | **Fotos de Angie proyectándose en la fiesta de Sophia.** |
| 🔥 **ALTA (P2)** | `public/index.php` (JS) | Dinamizar en JavaScript la fecha de cuenta regresiva (`eventDate`), las preguntas de trivia y los textos del pase VIP. | La cuenta regresiva y trivia muestran datos incorrectos de otra fiesta. |
| 🔥 **ALTA (P2)** | `public/index.php` (HTML) | Sustituir textos fijos de Tarjeta 1 (Historia), Tarjeta 4 (Fecha/Cumpleaños) y Tarjeta 6 (Itinerario) por las variables de `CurrentEventContext`. | La festejada ve nombres de familiares y fechas que no son suyas. |
| ⚡ **MEDIA (P3)** | `public/en-vivo.php` | Conectar con `TenantResolverMiddleware` y filtrar fotos por evento. | El video beam del evento proyecta fotos ajenas. |
| ⚡ **MEDIA (P3)** | `public/libro-recuerdos.php` | Conectar con `MemoryBookController` y cargar fotos/videos del `evento_id` activo. | El grimorio de Sophia carga el contenido de Angie. |
| ⚡ **MEDIA (P3)** | `public/subir-fotos.php` | Enviar `evento_id` en el `FormData` al llamar a la API de fotos. | Las fotos subidas por QR se asignan al evento incorrecto. |
| 🟢 **BAJA (P4)** | `api/checkin.php` | Admitir códigos QR con prefijo dinámico `XV-{SLUG}-XXXX`. | El escáner de puerta rechaza pases de nuevas quinceañeras. |

---

## 5. PLAN DE PRUEBAS DE VERIFICACIÓN (DoD)

Una vez aplicadas las correcciones, se deberá ejecutar el siguiente protocolo de validación:

```text
[TEST 1] Visitar /e/sophia-xv:
         - Portada: "Sophia Valentina" (14 Noviembre 2026).
         - Historia: Mención a sus padres Carlos y Mariana (sin rastro de Edwin y Lady).
         - Cuenta regresiva: Calculando hacia 14/11/2026 (diferencia de días visible).
         - Trivia: Preguntas de Ballet y Cheesecake (no de repostería de Angie).
         - Salón: Hotel Eurobuilding Caracas.

[TEST 2] Confirmación RSVP en /e/sophia-xv:
         - Confirmar a "Invitado Prueba Sophia" con 1 acompañante.
         - Verificar en Base de Datos: SELECT evento_id FROM invitados WHERE nombre_completo = 'Invitado Prueba Sophia' -> DEBE SER 2.
         - Pase VIP: El QR descargable debe decir "XVSOPHIA-XXXX".

[TEST 3] Galería y Álbum QR en /subir-fotos/sophia-xv:
         - Subir una foto de prueba.
         - Verificar que aparezca en /en-vivo?evento=sophia-xv.
         - Verificar que NO aparezca en /e/angie-xv ni en /en-vivo de Angie.

[TEST 4] Panel de Administración:
         - Ingresar como Anfitrión de Sophia (padres.sophia@email.com).
         - Solo debe aparecer "Invitado Prueba Sophia" y 0 fotos de Angie.
```
