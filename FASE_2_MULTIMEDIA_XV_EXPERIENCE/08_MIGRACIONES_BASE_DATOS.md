# FASE 2: MIGRACIONES Y ESQUEMA DE BASE DE DATOS

---

## 1. Esquema de la Tabla `fotos_fiesta`

La tabla existente `fotos_fiesta` fue ampliada de forma aditiva y segura para soportar la moderación y la metadata técnica de la Fase 2 sin alterar los registros existentes:

```sql
CREATE TABLE IF NOT EXISTS fotos_fiesta (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evento_id INTEGER NOT NULL DEFAULT 1,
    nombre_invitado TEXT NOT NULL,
    mensaje TEXT,
    archivo TEXT NOT NULL,
    likes INTEGER NOT NULL DEFAULT 0,
    ip TEXT,
    visible INTEGER NOT NULL DEFAULT 1,
    aprobada INTEGER NOT NULL DEFAULT 1,
    estado TEXT NOT NULL DEFAULT 'aprobada',    -- 'pendiente', 'aprobada', 'rechazada'
    subida_uuid TEXT NULL,                     -- UUID enviado por cliente para idempotencia
    hash_sha256 TEXT NULL,                     -- Hash SHA-256 de contenido para deduplicación
    peso_bytes INTEGER NULL,                   -- Peso exacto en bytes
    ancho_px INTEGER NULL,                     -- Ancho en píxeles
    alto_px INTEGER NULL,                      -- Alto en píxeles
    mime_type TEXT NULL,                       -- MIME verificado (image/jpeg, image/webp, etc.)
    moderada_at DATETIME NULL,                 -- Fecha y hora de moderación
    moderador_nombre TEXT NULL,                -- Usuario/Rol que moderó la fotografía
    es_destacada INTEGER NOT NULL DEFAULT 0,   -- Flag para proyector (1 = momento especial)
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Índices de alto rendimiento
CREATE INDEX IF NOT EXISTS idx_fotos_evento_estado ON fotos_fiesta (evento_id, estado, visible);
CREATE INDEX IF NOT EXISTS idx_fotos_evento_hash ON fotos_fiesta (evento_id, hash_sha256);
CREATE INDEX IF NOT EXISTS idx_fotos_uuid ON fotos_fiesta (evento_id, subida_uuid);
```

---

## 2. Idempotencia y Auto-Reparación (`DatabaseMigrationService`)

El servicio `DatabaseMigrationService::ensureMigrated($pdo)` se encarga de aplicar los cambios automáticamente tanto en **SQLite** como en **MySQL/MariaDB**:

- **Verificación no destructiva:** Revisa si cada columna existe antes de intentar agregarla mediante `ALTER TABLE ... ADD COLUMN`.
- **Valores por defecto garantizados:**
  - Todas las fotos existentes de Angie (`evento_id = 1`) mantienen `aprobada = 1`, `visible = 1`, `estado = 'aprobada'` y `es_destacada = 0`. Cero regresiones históricas.
  - Las nuevas fotos en celebraciones con `moderacion_fotos_activa = 1` (Sophia) se inicializan automáticamente con `aprobada = 0`, `visible = 0` y `estado = 'pendiente'`.
