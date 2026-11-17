# AUDITORÍA TÉCNICA Y FUNCIONAL — PROYECTO INVITACIONANGIE
## DOCUMENTO 05: AUDITORÍA VISUAL Y EXPERIENCIA DE USUARIO (UX/UI)

---

### 1. IDENTIDAD VISUAL Y CONCEPTO ARTÍSTICO

La dirección de arte de `InvitacionAngie` se fundamenta en una estética de **Cuento de Hadas Real y Fantasía Medieval/Celta**, fuertemente inspirada en la película *Valiente* (Brave) de Disney/Pixar, fusionada con la elegancia protocolar de una gala de quinceañera aristocrática:

- **Atmósfera:** Noctuidad mágica, mística esmeralda, destellos de oro viejo y pergamino antiguo.
- **Motivos gráficos recurrentes:** Tiara real con perlas y diamantes, filigranas doradas en las esquinas de cada tarjeta, mariposas doradas en vuelo perpetuo, destellos luminosos (*sparkles*) y grimorio encuadernado en cuero esmeralda con broches dorados.

---

### 2. PALETA CROMÁTICA OFICIAL

El sistema visual utiliza una paleta rica y coherente definida en variables CSS (`:root`):

| Tono | Código Hex | Rol en la Interfaz | Sensación que Transmite |
| :--- | :---: | :--- | :--- |
| **Verde Reino / Profundo** | `#021410` / `#041B16` | Fondo principal del viewport y cubiertas | Sobriedad, misterio, profundidad nocturna |
| **Verde Esmeralda Base** | `#062E25` | Fondo de las tarjetas del deck y lomo del libro | Realeza, naturaleza mágica, color temático de Angie |
| **Verde Esmeralda Medio** | `#006B4F` / `#0B4538` | Gradientes, botones hover y bordes de acento | Vivacidad, riqueza cromática |
| **Dorado Puro / Brillante** | `#FFD700` / `#FFE89E` | Títulos caligráficos, coronas, destellos y acentos VIP | Lujo, celebración, distinción |
| **Dorado Base (Antique Gold)** | `#C8A24A` | Bordes decorativos, divisores, iconos y textos de botón | Distinción aristocrática, calidez |
| **Dorado Claro / Champán** | `#E7D49A` / `#F5E6C4` | Subtítulos en serif, etiquetas y bordes sutiles | Suavidad, contraste armónico |
| **Crema / Blanco Marfil** | `#FFF8EC` / `#FFFDF5` | Textos de lectura sobre fondo oscuro | Máxima legibilidad, pureza |
| **Pergamino Antiguo** | `#FBF4E4` | Fondo de las páginas del Libro Mágico 3D | Textura orgánica, antigüedad, calidez |
| **Tinta Sepia** | `#2C2216` / `#5D4A36` | Textos impresos dentro de las hojas del grimorio | Simulación de caligrafía con pluma |

---

### 3. TIPOGRAFÍAS Y SISTEMA EDITORIAL

| Familia Tipográfica | Categoría | Uso en la Aplicación | Justificación Estética |
| :--- | :--- | :--- | :--- |
| **Great Vibes** | Script / Caligráfica | Nombre de Angie, titulares de tarjetas ("Érase una vez...", "Confirma tu Asistencia", "¡Te Espero!") | Trazos fluidos y elegantes que emulan invitaciones de alta gala manuscritas |
| **Playfair Display** | Serif Clásica | Subtítulos, encabezados secundarios de tarjeta, etiquetas de fecha y boletos | Aporta el peso protocolar y la seriedad editorial requerida en eventos formales |
| **Montserrat** | Sans-Serif Geométrica | Cuerpo de texto, formularios, inputs, botones de acción y datos de fecha/hora | Garantiza legibilidad óptima en pantallas pequeñas de smartphones |
| **Cinzel Decorative** | Serif Romana Ornamental | Encabezados de capítulos del Libro 3D y subtítulos en la pantalla en vivo | Añade un carácter heráldico y ceremonial |
| **Caveat** | Cursiva Informal | Mensajes del Muro de Dedicatorias en el libro | Brinda apariencia de notas personales dejadas a mano por los invitados |

---

### 4. ANIMACIONES, EFECTOS VISUALES Y MULTISENSORIALES

1. **Vuelo de Mariposas en Tiempo Real:**
   - Ubicadas en las 4 esquinas de la pantalla mediante CSS y JavaScript. Presentan oscilación sinusoidal de trayectoria, rotación tridimensional en eje Y para simular el aleteo de alas y emisión de destellos luminosos al pasar el cursor o el dedo.
2. **Paso de Página Tridimensional (3D Page Flip):**
   - El libro de recuerdos utiliza `transform-style: preserve-3d` y `perspective: 2000px`. Al avanzar, la hoja rota de $0^\circ$ a $-180^\circ$ con una sombra dinámica que se intensifica en el ángulo de $90^\circ$, logrando un realismo físico sobresaliente en navegadores de escritorio y móviles.
3. **Efectos Acústicos Sintetizados (Web Audio API):**
   - **Crujido de pergamino:** Simulado con ruido blanco modulado al voltear hojas.
   - **Sonido de ruleta mecánica:** Ticks metálicos periódicos con frecuencias pseudoaleatorias (450 a 650 Hz).
   - **Fanfarria de premio:** Acorde sintetizado en notas C5, E5, G5 y C6.
   - **Beep de escaneo QR:** Tono alegre ascendente si el pase es válido, o doble beep grave si el invitado ya había ingresado.
4. **Lluvia de Confeti y Partículas:**
   - Integrado en las felicitaciones del RSVP y al responder correctamente preguntas de la trivia, disparando ráfagas radiales doradas y esmeraldas sobre el viewport.

---

### 5. DISEÑO RESPONSIVE Y COMPATIBILIDAD

#### A. Dispositivos Móviles (Smartphones: 320px – 480px):
- **Comportamiento:** La tarjeta ocupa el 92% del ancho de pantalla con márgenes laterales estrechos. El deck deslizante se controla con gestos naturales de desplazamiento vertical (swipe up / swipe down).
- **Adaptaciones Especiales:** Los tamaños de fuente en títulos caligráficos se reducen dinámicamente mediante `clamp()` y media queries (`@media (max-width: 380px)` y `@media (max-width: 480px)`). El libro 3D se transforma automáticamente en vista de una sola página en pantallas angostas para evitar recortes.

#### B. Tablets y Escritorio (768px – 1920px+):
- **Comportamiento:** La invitación principal se presenta en un marco tipo tarjeta vertical centrada (estilo teléfono móvil o invitación de mano física de $420\text{px} \times 740\text{px}$), rodeada por un fondo de estrellas y mariposas que enmarca el contenido sin perder la proporción vertical.
- **Libro 3D en Escritorio:** Se expande a formato pliego completo de doble página abierta (página izquierda y página derecha simultáneas), recreando fielmente la sensación de tener un libro físico sobre una mesa de madera oscura.

---

### 6. ELEMENTOS ACOPLADOS A ANGIE VS. COMPONENTES REUTILIZABLES

```text
+-----------------------------------------------------------------------------------+
| ELEMENTOS HIPER-ACOPLADOS A ANGIE (Requieren dinamización)                        |
+-----------------------------------------------------------------------------------+
| • Nombre de la quinceañera: "Angie Karolina Avendaño Rivera"                       |
| • Nombres de padres: "Edwin Johan Avendaño" y "Lady Rivera"                       |
| • Fotografías físicas: "angie-portada.jpg", "angie-xv.jpg", 160 recuerdos         |
| • Fecha y Horarios: 3 de Octubre de 2026, 8:00 PM (Cumpleaños: 23 de Septiembre)  |
| • Salón: "Colegio de Médicos del Estado Mérida, Av. Urdaneta, Mérida, Venezuela"  |
| • Preguntas de la trivia: Gustos personales de Angie (repostería, pasticho, etc.) |
| • Paleta: Verde Esmeralda exclusivo para ella                                     |
| • Música de fondo: "Valiente - Con toda libertad"                                 |
+-----------------------------------------------------------------------------------+
                                         │
                                         ▼
+-----------------------------------------------------------------------------------+
| COMPONENTES ALTAMENTE REUTILIZABLES (Listos para motor comercial)                 |
+-----------------------------------------------------------------------------------+
| • Motor de Deck deslizante con gestos táctiles y transiciones CSS                 |
| • Tiara real y coronas vectoriales en SVG parametrizables                         |
| • Contador regresivo automático con exportación a Google Calendar                 |
| • Componente de Dress Code con selector de muestras cromáticas                    |
| • Motor de Trivia interactiva con confeti y retroalimentación configurable        |
| • Widget de sugerencias de playlist con deduplicación en tiempo real              |
| • Módulo RSVP completo con acompañantes dinámicos y anti-duplicados               |
| • Generador de Pase VIP con QR, hash de seguridad y descarga en imagen PNG        |
| • Asignador de códigos únicos de rifa y sorteador en ruleta con sonido            |
| • Grimorio / Libro 3D interactivo con sonido de pergamino sintetizado             |
| • Visor Lightbox para fotos y videos en streaming HTTP Range 206                  |
| • Muro de dedicatorias con stickers, colores y sistema de likes                   |
| • Pantalla de transmisión en vivo para videobeam / TV en salón                    |
| • Escáner de pases QR con cámara WebRTC para recepción en puerta                  |
+-----------------------------------------------------------------------------------+
```
