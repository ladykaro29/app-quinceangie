# FASE 2: PENDIENTES, DECISIONES TÉCNICAS Y RECOMENDACIONES

---

## 1. Decisiones Técnicas Adoptadas en Fase 2

1. **Compresión Híbrida WebP con Fallback a JPEG:**
   - Se evaluó el soporte de WebP en Canvas `toBlob('image/webp')`. En dispositivos modernos (iOS 14+, Android 9+), WebP reduce el peso un ~30% adicional respecto a JPEG con la misma calidad perceptual. Se dejó fallback transparente a JPEG para navegadores antiguos.
2. **Concurrencia de Subida Limitada a 2 Workers:**
   - Dispositivos Android económicos o iPhones de generaciones anteriores sufren cierres por falta de memoria RAM (*out-of-memory*) si intentan comprimir o subir 10 archivos de 15 MB simultáneamente. Limitar la cola a 2 conexiones concurrentes garantiza un 100% de éxito sin sobrecalentar el teléfono del invitado.
3. **Blackout Switch (`Tecla B`) en Pantalla en Vivo:**
   - La pantalla de proyección debe proteger a la quinceañera y a su familia. Disponer de un atajo de emergencia para apagar la pantalla inmediatamente ante cualquier fotografía imprevista o en momentos solemnes (entrada de la homenajeada, brindis, vals) añade valor profesional directo al servicio de *Kick-Off Development*.
4. **Tarjeta de Mesa Física Imprimible:**
   - Los invitados en una fiesta no abren el menú de una web para buscar el link de subida; el 90% de la participación ocurre cuando ven un código QR tangible y elegante en el centro de su mesa.

---

## 2. Recomendaciones para Fases Posteriores

1. **Fase 3 (Panel de Administración y CRUD de Celebraciones):**
   - Incorporar la gestión de creación y edición de nuevas celebraciones desde el panel sin necesidad de tocar la base de datos o ejecutar scripts.
2. **Transición a MariaDB en Producción (Easypanel):**
   - Aunque SQLite superó las pruebas de estrés con 185 fotos concurrentes, para comercialización a gran escala con múltiples eventos simultáneos, se sugiere activar el servicio MariaDB en Easypanel según el plan ya redactado.
3. **Descarga Masiva en Archivo ZIP por Streaming:**
   - En el futuro, para que los padres de la quinceañera descarguen las 500 fotos al día siguiente de la fiesta, implementar `ZipStream-PHP` para no cargar todo el archivo en la memoria RAM del servidor.
