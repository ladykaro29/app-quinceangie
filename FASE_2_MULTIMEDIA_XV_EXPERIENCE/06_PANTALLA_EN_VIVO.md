# FASE 2: PANTALLA EN VIVO Y PROYECTOR

---

## 1. Diseño y Adaptabilidad 16:9

La pantalla de presentación en vivo (`public/en-vivo.php`) está concebida para operar conectada de forma desatendida a proyectores HD, televisores y pantallas LED de eventos:

- **Resolución y Proporciones:** Diseñada para 1920×1080 (Full HD) y 4K en formato 16:9.
- **Tratamiento de Fotos Verticales (Efecto Backdrop Blur):** Cuando una fotografía tomada en modo retrato (vertical 9:16) se proyecta en una pantalla horizontal (16:9), en lugar de dejar barras negras o recortar las caras, el sistema genera dinámicamente un fondo desenfocado con la misma imagen al 40% de brillo, mientras la fotografía principal se muestra nítida y completa en el centro con `object-fit: contain`.
- **Transiciones Suaves:** Efecto de crossfade sutil con escala de entrada (0.96 → 1.0) para un aspecto cinematográfico y elegante.

---

## 2. Controles para Operadores en Vivo

| Control | Acción | Propósito en el Salón |
| :--- | :--- | :--- |
| **Tecla `B` (Blackout Switch)** | Alterna overlay negro con dorado | **Seguridad crítica:** Si por error se proyecta una foto inapropiada o se apaga la luz del salón para el vals, presionar `B` oscurece la pantalla de inmediato mostrando un elegante logo de la quinceañera. |
| **Barra Espaciadora (`Espacio`)** | Pausa / Reanuda la rotación | Permite dejar fija una fotografía específica si los familiares o amigos quieren observarla con más detenimiento. |
| **Botón Pantalla Completa** | Toggle Fullscreen API | Oculta la barra de navegación del navegador sin requerir extensiones. |
| **Parámetro `?duracion=X`** | Configura los milisegundos | Por defecto rota cada 7000ms (7 segundos). Puede ajustarse con `?duracion=5000` o `?duracion=10000`. |

---

## 3. Resiliencia de Red y Polling Asíncrono
- La pantalla realiza sondeos cada 10 segundos al endpoint `api/fotos.php?evento_id=X&limit=500&order=asc`.
- **Cero Parpadeo:** La actualización de nuevas fotos aprobadas ocurre en memoria en segundo plano mediante `fetch()` JSON, sin recargas de página (`F5`).
- Si se pierde la conexión a Internet en el salón, la pantalla continúa rotando en bucle las fotos ya descargadas sin mostrar pantallas de error ni detenerse.
- En cuanto una fotografía es rechazada en el panel de moderación, la próxima sincronización la descarta automáticamente del carrusel de reproducción.
