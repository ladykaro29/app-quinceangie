# FASE 2: SEGURIDAD Y ALMACENAMIENTO MULTIMEDIA

---

## 1. Defensa en Profundidad en Endpoints de Recepción

La compresión en el cliente es una optimización de experiencia de usuario y consumo de ancho de banda, pero **nunca se asume como medida de seguridad perimetral**. El servidor valida exhaustivamente cada payload:

### A. Validación Estricta de MIME Real (Magic Bytes)
- No se confía en la extensión del archivo enviada por el navegador (`$_FILES['foto']['name']`).
- No se confía en la cabecera `Content-Type` enviada por el cliente (`$_FILES['foto']['type']`).
- Se utiliza `finfo(FILEINFO_MIME_TYPE)` sobre el archivo temporal físico (`$file['tmp_name']`) para verificar los magic bytes reales del binario.
- Formatos estrictamente autorizados:
  - `image/jpeg`
  - `image/png`
  - `image/webp`
  - `image/heic` / `image/heif`
- Cualquier otro tipo (scripts, ejecutables, PDFs, SVGs con tags `<script>`, etc.) es rechazado de inmediato con `HTTP 400 Bad Request` o `HTTP 415 Unsupported Media Type`.

### B. Detección Heurística de Inyección PHP en Imágenes (Polyglots)
Los atacantes a menudo incrustan código PHP (`<?php system(...) ?>` o `<?=`) dentro de los comentarios o metadatos EXIF de archivos JPEG legítimos.
- Se analiza una muestra de los primeros 100 KB del binario buscando patrones de ejecución:
  ```php
  $headSample = file_get_contents($tempPath, false, null, 0, min(100000, filesize($tempPath)));
  if (stripos($headSample, '<?php') !== false || stripos($headSample, '<?=') !== false || stripos($headSample, '<script') !== false) {
      http_response_code(400);
      exit(json_encode(['success' => false, 'error' => 'Archivo no permitido por contenido inválido.']));
  }
  ```

### C. Validación de Dimensiones Reales
- Verificación con `getimagesize()`: La imagen debe tener dimensiones válidas entre 1×1 px y 6000×6000 px para prevenir bombas de descompresión (Zip bombs o pixel bombs).

### D. Nombres de Archivo Seguros y Prevención de Path Traversal
- Los nombres originales de los archivos subidos son descartados.
- Se genera un nombre criptográfico pseudoaleatorio:
  ```php
  $uniqueName = 'xv_' . $eventoId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $cleanExt;
  ```
- El endpoint de entrega `api/foto.php` utiliza `basename()` de forma obligatoria, neutralizando cualquier intento de *Directory Traversal* (`../../`).

### E. Rate Limiting por IP
- Máximo 40 fotografías por dirección IP en una ventana de 5 minutos, protegiendo al servidor contra flooding o ataques de denegación de servicio en el salón.

### F. Control de Cuota por Celebración
- Cada celebración cuenta con una cuota asignada de 2048 MB (2 GB).
- `StorageService::getEventStorageUsage($eventoId)` calcula el espacio en disco en tiempo real. Si la cuota se supera, se bloquean nuevas cargas informando al operador.
