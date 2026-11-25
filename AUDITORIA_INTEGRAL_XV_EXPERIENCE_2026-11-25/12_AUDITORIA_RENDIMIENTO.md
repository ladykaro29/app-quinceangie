# 12 — AUDITORÍA DE RENDIMIENTO Y OPTIMIZACIÓN WEB
**Plataforma:** XV Experience  
**Fecha:** 25 de Noviembre de 2026  
**Especialidad:** Full Stack Senior y Web Performance Specialist

---

## 1. MEDICIONES FÍSICAS REPRODUCIBLES DEL PAYLOAD

Se auditaron los pesos exactos en disco de los activos del sistema:

| Activo | Peso Físico | Impacto en Red Móvil (4G / 15 Mbps) | Impacto en Red 3G Lenta (1.5 Mbps) |
| :--- | :---: | :---: | :---: |
| **HTML Invitación Base (`public/index.php`)** | **204 KB** | ~0.11 s | ~1.10 s |
| **Música de Fondo (`musica.mp3`)** | **3.67 MB** | ~2.00 s | ~20.00 s |
| **Música Alternativa (`musica.m4a`)** | **2.47 MB** | ~1.35 s | ~13.50 s |
| **Retrato Portada Angie (`angie-portada.jpg`)**| **470 KB** | ~0.25 s | ~2.50 s |
| **Retrato Portada Sophia (`sophia-portada.jpg`)**| **913 KB** | ~0.50 s | ~5.00 s |
| **Lote 19 Videos de Gala (`assets/videos/*.mp4`)**| **58.61 MB** | ~32.00 s *(Streaming por demanda)*| N/A *(Inviable en descarga completa)*|

---

## 2. EVALUACIÓN DEL RENDIMIENTO POR COMPONENTE

### 2.1 Carga Inicial y Primer Renderizado (FCP / LCP)
- **Monolito HTML (204 KB):** Al no tener Vite ni Webpack que minifique y extraiga CSS/JS, el navegador descarga 204 KB de HTML plano antes de renderizar la primera tarjeta.
- **Fuentes Externas:** Carga 4 familias desde Google Fonts (*Cinzel Decorative*, *Montserrat*, *Playfair Display*, *Great Vibes*) y FontAwesome 6 desde CDN Cloudflare.
  - *Comportamiento:* Las fuentes están optimizadas con `preconnect`, pero bloquean levemente el renderizado inicial en conexiones celulares lentas.

### 2.2 Streaming de Video HTTP Range ([api/video.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/api/video.php))
- **Mecanismo:** El script implementa soporte para cabecera `HTTP_RANGE` (`bytes=0-1048576`).
- **Evaluación:** **Excelente.** Permite a los navegadores móviles reproducir los 19 videos del vals sin descargar los 58 MB completos en memoria, solicitando únicamente fragmentos de 1 MB a medida que el usuario visualiza el video.

### 2.3 Audio de Fondo y Políticas Autoplay
- **Implementación:** Web Audio API con fallback a etiqueta `<audio>`.
- **Comportamiento en Safari (iOS):** Los navegadores móviles bloquean el autoplay no interactivo. La aplicación lo maneja con acierto mediante una cortina de entrada / tap que requiere que el usuario interactúe con la pantalla para iniciar la música.
- **Audio de Sophia:** `storage/media/2/audio/princesa_sofia.mp3`. Al ser un archivo de audio estéreo de alta calidad, se recomienda comprimirlo a bitrate variable de 128 kbps (reduciría su tamaño de ~3.5 MB a ~1.2 MB).

### 2.4 Animaciones CSS y Consumo de CPU/GPU
- **Partículas y Brillos:** Las partículas flotantes doradas utilizan animaciones CSS basadas en `transform: translate3d()` y `opacity`, lo cual delega el cálculo a la GPU sin penalizar el hilo principal de JavaScript.
- **Grimorio 3D de Recuerdos ([public/libro-recuerdos.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/libro-recuerdos.php)):** Utiliza `transform: rotateY()` con perspectiva CSS 3D. En dispositivos de gama baja (Android con 2 GB RAM) puede presentar caídas a 30 FPS durante el volteo rápido de páginas.

---

## 3. OPORTUNIDADES DE MEJORA IDENTIFICADAS
1. **Compresión Gzip / Brotli:** Habilitar `mod_deflate` en el contenedor Apache para comprimir las respuestas HTML de 204 KB a ~38 KB (81% de reducción).
2. **Carga Diferida de Tarjetas:** Aplicar `loading="lazy"` en todas las imágenes posteriores a la tarjeta 2.
3. **Optimización de Portadas:** Convertir `sophia-portada.jpg` (913 KB) a formato WebP progresivo de 1600x2400px (peso estimado: ~180 KB sin pérdida perceptible de calidad).
