# MODELO DE DATOS Y PERSISTENCIA — ÁLBUM COLABORATIVO
## DOCUMENTO: MODELO_DATOS_ALBUM.md

---

### 1. MAPEO DE EVOLUCIÓN: `fotos_fiesta` VS. NUEVO ESQUEMA

| Campo en `fotos_fiesta` Actual | Destino en Nuevo Modelo | Justificación del Cambio |
| :--- | :--- | :--- |
| `id INT AUTO_INCREMENT` | `id BIGINT UNSIGNED AUTO_INCREMENT` | Escalabilidad para miles de fotos a lo largo del tiempo. |
| *No existía* | `evento_id INT UNSIGNED NOT NULL` | **Aislamiento Multi-Tenant obligatorio**. |
| *No existía* | `colaborador_id INT UNSIGNED NULL` | Vincula múltiples fotos subidas por un mismo invitado durante la noche. |
| `nombre_invitado VARCHAR(150)` | `nombre_remitente VARCHAR(150)` | Nombre o apodo capturado en el formulario móvil. |
| `mensaje TEXT` | `pie_foto TEXT` | Dedicatoria o mensaje que acompaña la captura. |
| `archivo VARCHAR(255)` | `archivo_nombre VARCHAR(255)` | Nombre físico del archivo sanitizado. |
| *No existía* | `hash_sha256 CHAR(64) NOT NULL` | **Detección inmediata de duplicados a nivel binario**. |
| *No existía* | `ancho_px INT UNSIGNED`, `alto_px INT UNSIGNED` | Permite al frontend reservar el espacio visual exacto antes de cargar la imagen. |
| *No existía* | `tamano_bytes INT UNSIGNED` | Auditoría de cuota de almacenamiento por evento. |
| *No existía* | `storage_driver ENUM('local', 's3', 'r2')` | Soporte nativo para migrar a Cloudflare R2 sin tocar URLs. |
| `visible TINYINT(1)` | Reemplazado por `estado ENUM('pendiente', 'aprobada', 'rechazada', 'eliminada')` | **Control total del flujo de moderación** frente al simple booleano anterior. |
| *No existía* | `motivo_rechazo VARCHAR(255)` | Justificación de rechazo por el anfitrión (ej. "Foto borrosa / no adecuada"). |
| *No existía* | `es_oficial TINYINT(1) DEFAULT 0` | Distingue las 160 fotos del álbum base de Angie de las fotos tomadas por invitados. |
| `likes INT` | `likes_count INT UNSIGNED DEFAULT 0` | Conteo atómico de likes de los invitados. |
| `ip VARCHAR(45)` | `ip_remitente VARCHAR(45)` | Trazabilidad y auditoría de abusos. |
| `created_at DATETIME` | `created_at DATETIME` | Marca de tiempo del envío. |

---

### 2. SENTENCIAS DDL COMPLETAS DEL SUBSISTEMA DEL ÁLBUM

A continuación se definen las 3 tablas relacionales especializadas para el Álbum Colaborativo:

```sql
-- ============================================================================
-- 1. TABLA: album_colaboradores (Sesiones de invitados que comparten fotos)
-- ============================================================================
CREATE TABLE IF NOT EXISTS album_colaboradores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    token_sesion VARCHAR(64) NOT NULL,
    nombre_remitente VARCHAR(150) NOT NULL,
    telefono_contacto VARCHAR(30) NULL,
    total_fotos_subidas INT UNSIGNED NOT NULL DEFAULT 0,
    ip_registro VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_colab_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_evento_colab_token (evento_id, token_sesion),
    INDEX idx_colab_evento (evento_id, nombre_remitente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. TABLA: album_fotografias (Registro maestro de imágenes procesadas)
-- ============================================================================
CREATE TABLE IF NOT EXISTS album_fotografias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    colaborador_id INT UNSIGNED NULL,
    nombre_remitente VARCHAR(150) NOT NULL DEFAULT 'Invitado Especial',
    pie_foto TEXT NULL,
    archivo_nombre VARCHAR(255) NOT NULL,
    archivo_original_nombre VARCHAR(255) NULL,
    hash_sha256 CHAR(64) NOT NULL,
    mime_type VARCHAR(50) NOT NULL,
    ancho_px INT UNSIGNED NOT NULL DEFAULT 0,
    alto_px INT UNSIGNED NOT NULL DEFAULT 0,
    tamano_bytes INT UNSIGNED NOT NULL DEFAULT 0,
    storage_driver ENUM('local', 's3', 'r2') NOT NULL DEFAULT 'local',
    ruta_original VARCHAR(500) NOT NULL,
    ruta_display VARCHAR(500) NOT NULL,
    ruta_thumb VARCHAR(500) NOT NULL,
    estado ENUM('pendiente', 'aprobada', 'rechazada', 'eliminada') NOT NULL DEFAULT 'pendiente',
    motivo_rechazo VARCHAR(255) NULL,
    moderado_por_usuario_id INT UNSIGNED NULL,
    moderado_at DATETIME NULL,
    es_oficial TINYINT(1) NOT NULL DEFAULT 0,
    destacada_proyector TINYINT(1) NOT NULL DEFAULT 0,
    likes_count INT UNSIGNED NOT NULL DEFAULT 0,
    ip_remitente VARCHAR(45) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_foto_alb_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    CONSTRAINT fk_foto_alb_colab FOREIGN KEY (colaborador_id) REFERENCES album_colaboradores(id) ON DELETE SET NULL,
    CONSTRAINT fk_foto_alb_moderador FOREIGN KEY (moderado_por_usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    -- Índices optimizados para consultas en vivo de alta velocidad
    INDEX idx_alb_evento_estado (evento_id, estado, id),
    INDEX idx_alb_evento_hash (evento_id, hash_sha256),
    INDEX idx_alb_evento_proyector (evento_id, estado, destacada_proyector),
    INDEX idx_alb_evento_fecha (evento_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. TABLA: album_moderacion_logs (Historial de auditoría de moderación)
-- ============================================================================
CREATE TABLE IF NOT EXISTS album_moderacion_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    fotografia_id BIGINT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    accion ENUM('aprobar', 'rechazar', 'eliminar', 'restaurar', 'destacar') NOT NULL,
    estado_anterior VARCHAR(30) NOT NULL,
    estado_nuevo VARCHAR(30) NOT NULL,
    motivo VARCHAR(255) NULL,
    ip_moderador VARCHAR(45) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_modlog_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    CONSTRAINT fk_modlog_foto FOREIGN KEY (fotografia_id) REFERENCES album_fotografias(id) ON DELETE CASCADE,
    CONSTRAINT fk_modlog_usr FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_modlog_evento (evento_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 3. VISTAS DE CONSULTA RÁPIDA (OPTIMIZACIÓN DE DB)

Para alimentar la pantalla de proyección y la galería sin sobrecargar el motor con joins pesados, se define la siguiente vista optimizada:

```sql
CREATE OR REPLACE VIEW view_fotos_aprobadas_publicas AS
SELECT 
    f.id,
    f.evento_id,
    f.nombre_remitente,
    f.pie_foto,
    f.ruta_display,
    f.ruta_thumb,
    f.ancho_px,
    f.alto_px,
    f.likes_count,
    f.es_oficial,
    f.destacada_proyector,
    f.created_at
FROM album_fotografias f
WHERE f.estado = 'aprobada'
ORDER BY f.id DESC;
```
*Garantía de Privacidad:* Esta vista garantiza a nivel de motor SQL que **ninguna fotografía con estado `pendiente`, `rechazada` o `eliminada` pueda filtrarse jamás a la galería pública ni a la pantalla de proyector**.
