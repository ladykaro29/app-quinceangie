# FASE 2: COMPRESIÓN EN CLIENTE Y COLA DE SUBIDA MÚLTIPLE

---

## 1. Módulo de Compresión Inteligente en Navegador

Implementado en `public/subir-fotos.php`, el compresor opera localmente en el hilo de ejecución del cliente utilizando **HTML5 Canvas** y la API `toBlob()`.

### Especificaciones de Compresión:
- **Límite de Bounding Box:** 2048 × 2048 píxeles. Si el lado mayor excede 2048px, se escala proporcionalmente manteniendo la relación de aspecto exacta.
- **Sin Ampliación Artificial:** Si la imagen original mide menos de 1200px, no se altera su resolución original para evitar pixelado o distorsión.
- **Formato Objetivo:** Formato WebP con calidad `0.82`. Si el navegador del cliente no soporta compresión WebP nativa en Canvas (o si el archivo resultante en WebP es mayor que en JPEG), conmuta automáticamente a JPEG de alta fidelidad.
- **Sanitización de Metadatos EXIF / Privacidad:** Al dibujar la imagen sobre un elemento `<canvas>` y extraerla de nuevo vía `toBlob()`, el navegador re-codifica los píxeles puros y descarta automáticamente todas las etiquetas EXIF sensibles, incluyendo:
  - Coordenadas geográficas GPS de la cámara/celular.
  - Número de serie del dispositivo.
  - Miniaturas embebidas en el encabezado.
- **Orientación Automática:** Los navegadores móviles modernos normalizan automáticamente la orientación EXIF de la foto al instanciar `new Image()` o `createImageBitmap({ imageOrientation: 'from-image' })`.

---

## 2. Cola Interactiva de Subida Múltiple (UX Mobile-First)

A diferencia del flujo anterior (donde un formulario simple se congelaba con un botón de carga genérico), la nueva interfaz presenta una **cola de procesamiento visual por archivo**:

### Estados de la Cola:
1. `Seleccionada (⚪)`: El archivo fue elegido de la galería o cámara del dispositivo.
2. `Optimizando... (🟡)`: El compresor Canvas está redimensionando la imagen en memoria.
3. `Lista (🔵)`: La imagen fue optimizada (muestra reducción, ej: `8.4 MB → 460 KB`).
4. `Subiendo... (🟠)`: El archivo se está transfiriendo al servidor vía FormData.
5. `Recibida ✔ (🟢)`: Confirmación 200 OK del servidor recibida.
6. `Error ✖ (🔴)`: Interrupción de conexión; despliega botón para reintentar individualmente.

### Control de Concurrencia (Workers en Paralelo):
Para evitar que teléfonos móviles de gama de entrada se bloqueen por saturación de memoria RAM al comprimir y transmitir 10 fotos a la vez:
- La compresión se ejecuta de forma secuencial controlada.
- La subida se realiza mediante un pool de workers asíncronos con concurrencia máxima de **2 envíos simultáneos**.
- A medida que un worker termina, toma automáticamente el siguiente archivo en estado `Lista`.

---

## 3. Manejo de Errores e Idempotencia en Reintentos

Si la conexión Wi-Fi o datos móviles del invitado se interrumpe a mitad de la subida:
1. El elemento en la cola marca `Error de conexión` y ofrece un botón de reintento.
2. Cada archivo tiene asignado un identificador único en cliente:
   ```javascript
   item.uuid = crypto.randomUUID ? crypto.randomUUID() : 'c_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
   ```
3. Al presionar **Reintentar**, el cliente reenvía la petición con el mismo `subida_uuid`.
4. El backend consulta si el `subida_uuid` ya fue registrado previamente en `fotos_fiesta`:
   - Si ya existía: responde inmediatamente `200 OK`, `duplicated: true` y marca el archivo como `Recibida ✔` en el celular, evitando duplicaciones en la galería del evento.
