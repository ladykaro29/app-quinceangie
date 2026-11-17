# AUDITORÍA TÉCNICA Y FUNCIONAL — PROYECTO INVITACIONANGIE
## DOCUMENTO 01: RESUMEN EJECUTIVO

---

### 1. INFORMACIÓN GENERAL DEL PROYECTO

| Parámetro | Detalle |
| :--- | :--- |
| **Nombre del Proyecto** | `InvitacionAngie` |
| **Propósito Inicial** | Experiencia digital interactiva integral para los XV Años de Angie Karolina Avendaño Rivera |
| **Fecha de Celebración** | Sábado, 3 de Octubre de 2026 (Cumpleaños: 23 de Septiembre) |
| **Lugar del Evento** | Colegio de Médicos del Estado Mérida, Av. Urdaneta, Mérida, Venezuela |
| **Objetivo Estratégico Futuro** | Transformación en una plataforma SaaS comercial multicliente de invitaciones interactivas |
| **Fecha de Auditoría** | 15 de Noviembre de 2026 |
| **Entorno Evaluado** | Repositorio de código fuente local (Entorno Windows / PHP 8.2 / SQLite / MySQL) |
| **Tipo de Auditoría** | Exhaustiva, de solo lectura, técnica, arquitectónica, visual, funcional y de seguridad |

---

### 2. RESUMEN DEL ESTADO ACTUAL

El proyecto `InvitacionAngie` es una solución web monolítica en **PHP nativo (sin frameworks)**, complementada con **JavaScript Vanilla moderno (ES6+)**, **CSS3 avanzado**, **HTML5 semántico**, y una capa de persistencia dual **MySQL / SQLite** mediante **PDO**.

A pesar de haber sido concebida originalmente como una invitación digital para un evento particular, el desarrollo fue evolucionando progresivamente hacia un ecosistema mucho más complejo y rico en funcionalidades de lo habitual en este tipo de productos. El código fuente cuenta con:

1. **Una Invitación Digital Interactiva Principal (`public/index.php`):** Estructurada en un deck de **13 tarjetas deslizables** con control táctil (swipe), navegación por teclado, cuenta regresiva con sincronización a Google Calendar, mapa del evento, dress code, programa nocturno, trivia interactiva con confeti, sugerencias de playlist en vivo, lluvia de sobres con buzón, módulo de confirmación RSVP con generación dinámica de **Pase VIP con código QR y boletos de rifa únicos**, y enlace al álbum en vivo.
2. **Un Libro Mágico de Recuerdos 3D (`public/libro-recuerdos.php`):** Un componente de alta fidelidad visual y sensorial diseñado como un grimorio encuadernado en cuero esmeralda y oro, con animación de paso de páginas 3D, efectos de sonido de pergamino sintetizados en tiempo real mediante **Web Audio API**, galería polaroid con **160 fotos precargadas**, galería de **19 videos en formato MP4**, reproductor de música en vinilo flotante y sistema de dedicatorias interactivas con likes, stickers y confeti.
3. **Una Pantalla de Transmisión en Vivo para Proyector / TV (`public/en-vivo.php`):** Pantalla en tiempo real a pantalla completa para ser proyectada en el salón de eventos, exhibiendo un carrusel dinámico con las fotos que los invitados toman y envían desde sus teléfonos inteligentes.
4. **Un Panel de Administración Integral (`admin/index.php`):** Módulo administrativo protegido por contraseña que incluye visualización y conteo de asistentes, buscador en tiempo real, gestión de asistencias (Check-in), **módulo de sorteo de rifa en vivo con ruleta animada y sonido sintetizado**, **escáner de códigos QR integrado vía cámara WebRTC** para recepción de invitados, moderación de fotos y canciones, exportación a **CSV con soporte UTF-8 BOM** para Excel y exportación de fotos a archivo comprimido **ZIP**.
5. **Una Capa de Servicios API REST JSON (`api/*.php`):** Compuesta por **8 endpoints** especializados que resuelven operaciones asíncronas para RSVP, check-in, fotos, streaming de video con HTTP Range 206, libro de recuerdos, playlist e invitados.

---

### 3. MÉTRICAS CLAVE DEL REPOSITORIO

```text
Código fuente total analizado:
├── Archivos de código (PHP, SQL, CSS/JS embebidos): 15 archivos
├── Líneas de código estimadas (PHP + HTML + JS + CSS): ~11,200 líneas
├── Archivos multimedia físicos:
│   ├── Fotografías de fiesta: 160 archivos JPG (14.03 MB en public/assets/fotos + réplica en uploads)
│   ├── Videos de fiesta: 19 archivos MP4 (58.61 MB en public/assets/videos + réplica en uploads)
│   ├── Fotografías de portada/perfil: 2 archivos JPG (angie-portada.jpg, angie-xv.jpg)
│   ├── Música de fondo: 2 archivos (musica.mp3 de 3.84 MB, musica.m4a de 2.59 MB)
│   └── Vectores / Favicons: SVG, PNG, ICO (corona real personalizada)
└── Base de datos local: invitacion_xv.sqlite (64 KB, 5 tablas creadas y pobladas)
```

---

### 4. PRINCIPALES FORTALEZAS IDENTIFICADAS

1. **Experiencia de Usuario (UX) de Gran Impacto Emocional:** El proyecto destaca por un estándar visual muy superior al promedio de invitaciones web convencionales. La integración de animaciones CSS, destellos dorados, partículas en Canvas, mariposas animadas, paso de página 3D y diseño inspirado en cuentos de hadas reales crea una experiencia inmersiva única.
2. **Uso Innovador de Tecnologías Web Nativas (Zero Dependency Bloat):** Se logró simular sonido mecánico de ruletas, beeps de confirmación y crujido de pergamino utilizando la **Web Audio API nativa** del navegador, evitando cargar pesadas librerías de audio externas o archivos MP3 adicionales para efectos de sonido.
3. **Resiliencia y Portabilidad en la Base de Datos:** El archivo `config/database.php` implementa un mecanismo de autoconfiguración sobresaliente: si MySQL/MariaDB no está disponible (problema común en contenedores Docker o entornos locales sin MySQL configurado), la aplicación hace un **fallback automático e imperceptible a SQLite**, creando las tablas y migrando columnas de forma autónoma.
4. **Módulo de Check-in y Rifa de Nivel Profesional:** La generación de pases VIP descargables como imagen con QR firmado por hash MD5, acompañada de asignación de boletos para rifa y validación por cámara en puerta, representa una ventaja competitiva excepcional para eventos sociales.
5. **Compatibilidad Móvil y Streaming Optimizado:** El servidor de streaming de video (`api/video.php`) cuenta con soporte del encabezado `HTTP_RANGE` (`206 Partial Content`), lo que garantiza que los videos de 10 MB se reproduzcan fluidamente en dispositivos iOS (Safari) y Android sin obligar a descargar el archivo completo de antemano.

---

### 5. PRINCIPALES RIESGOS Y DEBILIDADES DETECTADAS

1. **Fuerte Acoplamiento Monolítico e Hiper-Personalización Hardcodeada:** Todos los datos vinculados a Angie (nombres, fechas, padres, colores, itinerario, preguntas de trivia, fotos y dedicatorias) están incrustados directamente en el código PHP, HTML y JS. No existe en este momento una capa de datos desacoplada que permita cambiar estos valores sin alterar el código fuente.
2. **Archivos Gigantescos con Mezcla de Responsabilidades (Spaghetti Code / Big Ball of Mud):**
   - `public/index.php`: **5,161 líneas** combinando HTML, CSS gigantesco (más de 2,000 líneas), lógica PHP de servidor y cientos de líneas de JavaScript.
   - `public/libro-recuerdos.php`: **2,828 líneas** con la misma estructura monolítica.
   - `admin/index.php`: **2,126 líneas** con lógica de negocio, exportaciones, modales, CSS y scripts en un solo archivo.
3. **Duplicación Redundante de Recursos en Disco (145+ MB innecesarios):** Las 160 fotos y los 19 videos existen por duplicado en `public/assets/` y en `database/uploads/`, ocupando el doble del espacio de almacenamiento necesario.
4. **Inconsistencias Funcionales en la Administración:**
   - Al eliminar fotos desde el panel de administración, el código elimina el archivo físico en `database/uploads/fotos/`, pero **no** lo elimina de `public/assets/fotos/`, por lo que el archivo sigue existiendo y sirviéndose a través de los fallbacks.
   - El archivo `database/schema.sql` está desactualizado; solo declara las tablas `invitados` y `acompanantes`, omitiendo `fotos_fiesta`, `canciones`, `libro_recuerdos` y las columnas `asistio_evento` y `checkin_at`.
5. **Vulnerabilidades y Brechas de Seguridad Significativas:**
   - Credenciales maestras fijas escritas en el código (`$validPasswords = [ADMIN_PASS, 'angie2026', 'angie15']`). Si un usuario olvida cambiar `.env`, cualquiera con la palabra `angie2026` ingresa al panel.
   - Endpoints críticos como `api/checkin.php` e `api/invitados.php` no exigen token ni sesión administrativa activa; cualquiera que conozca la URL puede consultar la lista de invitados o alternar asistencias.
   - Formularios POST del panel administrativo sin protección contra CSRF (Cross-Site Request Forgery).
   - Inexistencia de rate-limiting (protección contra inundación de peticiones) en `api/confirmar.php` o `api/fotos.php`.

---

### 6. DICTAMEN DE VIABILIDAD COMERCIAL (CONCLUSIÓN EJECUTIVA)

| Criterio | Calificación (1 a 10) | Diagnóstico |
| :--- | :---: | :--- |
| **Calidad de Diseño y UX** | **9.5 / 10** | Excelente. Nivel visual prémium, muy atractivo para clientes de XV años y bodas. |
| **Conjunto de Funcionalidades** | **9.0 / 10** | Muy completo. Va más allá de una tarjeta digital convencional (Check-in QR, Rifa, Libro 3D, Fotos en vivo). |
| **Estabilidad Funcional Actual** | **8.0 / 10** | Funciona fluidamente para el evento de Angie en entorno mono-evento. |
| **Modularidad y Arquitectura** | **3.5 / 10** | Deficiente. Monolitos sin separación de capas (MVC), sin motor de plantillas y sin abstracción. |
| **Capacidad Multicliente Inmediata** | **1.0 / 10** | Inexistente. El sistema actual no puede atender a dos clientes sin clonar físicamente todo el código y base de datos. |
| **Seguridad para Producción SaaS** | **4.0 / 10** | Riesgosa. Requiere implementar autenticación tokenizada, roles, CSRF tokens y sanitización robusta. |

**DICTAMEN FINAL:**
El proyecto posee un **valor comercial y funcional sobresaliente en cuanto a concepto, estética y funcionalidades interactivas**, lo que valida plenamente su potencial de negocio. Sin embargo, en su estado arquitectónico actual, **NO es apto para comercializarse como plataforma ni para gestionar múltiples eventos**. 

Para convertirse en un producto comercial escalable, debe someterse a una reestructuración planificada por fases que desacople los datos del diseño, unifique los componentes en un motor de plantillas dinámico y cree una capa de backend multi-inquilino (*multi-tenant*).
