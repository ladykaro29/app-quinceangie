# 06 — AUDITORÍA DEL ÁLBUM COLABORATIVO MEDIANTE QR
**Plataforma:** XV Experience  
**Fecha:** 25 de Noviembre de 2026  
**Especialidad:** Full Stack, UX Móvil y QA

---

## 1. EVALUACIÓN DEL FLUJO INTEGRAL DE 23 PASOS

```mermaid
flowchart TD
    A["1. Pantalla en Vivo (en-vivo.php)"] -->|Muestra QR dinámico| B["2. Invitado escanea QR con celular"]
    B -->|3. Resuelve /subir-fotos/{slug}| C["4. Carga subir-fotos.php"]
    C -->|5. Ingresa nombre y nota| D["6. Abre cámara / Galería"]
    D -->|7. Selección múltiple| E["8. Vista previa local (Canvas)"]
    E -->|9. Envío HTTP POST| F["10. Barra de Progreso"]
    F -->|11-13. Valida MIME real| G["api/fotos.php / CollaborativePhotoController"]
    G -->|16-17. Guarda en disco y BD| H[("fotos_fiesta (evento_id)")]
    H -->|18. Moderación / Visible=1| I["19-20. Galería en Vivo y Proyector"]
    H -->|21. Descarga ZIP| J["admin/index.php (Operador)"]
    H -->|22. Eliminación física| K["Limpieza / Purga"]
```

| Paso | Acción del Flujo | Estado Técnico | Archivos / Componentes Involucrados | Hallazgos y Evaluación |
| :---: | :--- | :---: | :--- | :--- |
| **1** | Generación de QR por celebración | **OK** | [public/en-vivo.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/en-vivo.php#L34-L36) | URL del QR es dinámica: `/subir-fotos/{slug}`. |
| **2** | Escaneo desde dispositivos móviles | **OK** | Cámara nativa iOS / Android | Abre directamente el navegador del sistema sin instalar apps. |
| **3** | Resolución de celebración correcta | **OK** | `TenantResolverMiddleware` | Resuelve slug `sophia-xv` y carga tokens ciruela/rosa. |
| **4** | Página de recepción de fotografías | **OK** | [public/subir-fotos.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/subir-fotos.php) | Responsive, mobile-first, inputs táctiles grandes. |
| **5** | Identificación del invitado | **OK** | Campo de texto en formulario | Permite escribir nombre del familiar/amigo (máx. 100 caracteres). |
| **6** | Acceso a la cámara del teléfono | **OK** | `<input type="file" capture="environment">`| Invoca la cámara nativa en iOS Safari y Android Chrome. |
| **7** | Selección múltiple de imágenes | **OK** | Atributo `multiple` en input file | Permite seleccionar lotes de fotos en la galería del celular. |
| **8** | Vista previa en cliente | **OK** | FileReader API en JS | Genera miniaturas en cuadrícula antes de enviar. |
| **9** | Envío de fotografías | **OK** | `fetch()` POST multipart/form-data | Envía archivo, nombre, mensaje y `evento_id`. |
| **10**| Indicadores de progreso | **PARCIAL** | Spinner CSS | Hay animación de carga, pero no barra de progreso con porcentaje real (XHR `upload.onprogress`). |
| **11**| Manejo de errores | **OK** | Respuestas JSON con alertas visuales | Muestra modales en caso de fallo de red o archivo inválido. |
| **12**| Reintentos ante fallas | **NO** | No implementado en cliente | Si la conexión se corta, el usuario debe pulsar enviar nuevamente. |
| **13**| Validación de archivos | **OK** | `finfo` en backend | Valida tamaño (<12MB) y MIME real (JPEG, PNG, WEBP, HEIC). |
| **14**| Compresión y optimización | **PARCIAL** | Backend no redimensiona | Las fotos se guardan en el tamaño original subido. |
| **15**| Compatibilidad HEIC (iPhone) | **PARCIAL** | Mapeado a extensión jpg | Se acepta MIME `image/heic`, pero requiere Imagick para convertir a JPEG si el proyector no soporta HEIC nativo. |
| **16**| Almacenamiento físico | **OK** | `database/uploads/fotos/` | Nombres únicos sanitizados con hash criptográfico aleatorio. |
| **17**| Registro en base de datos | **OK** | Tabla `fotos_fiesta` | Inserción con `evento_id`, `nombre_invitado`, `mensaje`, `ip`. |
| **18**| Moderación administrativa | **OK** | [admin/index.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/admin/index.php#L528-L531) | Permite conmutar `visible = 0` para retirar fotos inadecuadas. |
| **19**| Visualización en el álbum | **OK** | [public/libro-recuerdos.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/libro-recuerdos.php) | Se integran en el feed interactivo. |
| **20**| Visualización en pantalla en vivo | **OK** | [public/en-vivo.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/en-vivo.php) | Polling cada 6 segundos; nuevas fotos aparecen con animación. |
| **21**| Descarga masiva ZIP | **OK** | [admin/index.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/admin/index.php#L430-L469) | Genera ZIP filtrado exclusivamente por `evento_id` activo. |
| **22**| Eliminación física de archivos | **OK** | `unlink()` en admin | Al eliminar de BD, desvincula y borra el archivo de disco. |
| **23**| Aislamiento entre celebraciones | **OK** | Verificado en prueba funcional | **Cero fotos de Angie en Sophia y viceversa.** |

---

## 2. EXPERIENCIA EN ANDROID, IPHONE Y REDES INESTABLES
- **En iPhone (iOS Safari):** El selector de cámara funciona correctamente. Sin embargo, cuando los usuarios toman fotos en formato *Live Photo* o *HEIC de alta resolución (48MP)*, el archivo puede superar los 8 MB y tardar hasta 15 segundos en conexiones 3G/LTE lentas de salón de fiestas.
- **En Android (Chrome/Samsung Internet):** Comportamiento muy fluido. La compresión nativa del sistema operativo entrega archivos JPEG de 2 a 4 MB.
- **En Redes Inestables:** Si el salón no tiene Wi-Fi de alta velocidad o la red móvil se satura por concurrencia de invitados, la ausencia de un compresor en el navegador del cliente (vía HTML5 Canvas o `browser-image-compression`) representa un cuello de botella.

---

## 3. PROPUESTA DE FLUJO MEJORADO (SIN IMPLEMENTAR)
1. **Compresión en el Navegador del Cliente (Client-side Resizing):** Redimensionar las fotos a un máximo de 2048px en su lado mayor y comprimirlas a WebP/JPEG al 82% de calidad mediante Canvas antes de transmitir la petición HTTP. Esto reduciría el peso de 8 MB a 600 KB (92% de ahorro de ancho de banda).
2. **Cola de Subida con Barra de Progreso Porcentual:** Utilizar `XMLHttpRequest` con listener `progress` para mostrar barra visual del 0% al 100%.
3. **Conversión Automática HEIC a JPEG en Servidor:** Integrar procesamiento con GD o Imagick para garantizar que cualquier formato de Apple se proyecte en pantallas y navegadores antiguos.
