-- ============================================================================
-- SCRIPT DE MIGRACIÓN: MULTI-TENANCY CORE PARA XV EXPERIENCE
-- COMPATIBILIDAD DUAL: MySQL 8.0+ / MariaDB 10.5+ / SQLite 3.35+
-- ============================================================================

-- 1. TABLA: clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre_contacto VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(30) NOT NULL,
    documento_identidad VARCHAR(40) NULL,
    pais VARCHAR(60) NOT NULL DEFAULT 'Venezuela',
    ciudad VARCHAR(80) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABLA: paquetes
CREATE TABLE IF NOT EXISTS paquetes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    moneda VARCHAR(3) NOT NULL DEFAULT 'USD',
    max_invitados INT NOT NULL DEFAULT 100,
    max_fotos INT NOT NULL DEFAULT 50,
    max_videos INT NOT NULL DEFAULT 5,
    incluye_libro_3d TINYINT NOT NULL DEFAULT 1,
    incluye_proyector_envivo TINYINT NOT NULL DEFAULT 1,
    incluye_rifa_sorteo TINYINT NOT NULL DEFAULT 1,
    incluye_checkin_qr TINYINT NOT NULL DEFAULT 1,
    activo TINYINT NOT NULL DEFAULT 1
);

-- 3. TABLA: plantillas
CREATE TABLE IF NOT EXISTS plantillas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    ruta_directorio VARCHAR(100) NOT NULL,
    activo TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 4. TABLA: roles
CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL
);

-- 5. TABLA: usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    rol_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    activo TINYINT NOT NULL DEFAULT 1,
    ultimo_login DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 6. TABLA: eventos
CREATE TABLE IF NOT EXISTS eventos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id INT NOT NULL,
    paquete_id INT NOT NULL,
    plantilla_id INT NOT NULL,
    slug VARCHAR(80) NOT NULL UNIQUE,
    nombre_celebracion VARCHAR(150) NOT NULL,
    tipo_evento VARCHAR(50) NOT NULL DEFAULT 'xv_anos',
    festejada_nombre VARCHAR(100) NOT NULL,
    fecha_evento DATE NOT NULL,
    hora_evento TIME NOT NULL,
    lugar_nombre VARCHAR(150) NOT NULL,
    lugar_direccion TEXT NOT NULL,
    lugar_coordenadas VARCHAR(60) NULL,
    lugar_mapa_url TEXT NULL,
    codigo_vestimenta VARCHAR(100) NULL DEFAULT 'Formal / Elegante',
    color_reserva VARCHAR(50) NULL DEFAULT 'Verde Esmeralda',
    musica_url VARCHAR(255) NULL,
    portada_url VARCHAR(255) NULL,
    avatar_url VARCHAR(255) NULL,
    activo TINYINT NOT NULL DEFAULT 1,
    publicado TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
);

-- 7. TABLA: configuraciones_evento
CREATE TABLE IF NOT EXISTS configuraciones_evento (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evento_id INT NOT NULL,
    clave VARCHAR(80) NOT NULL,
    valor TEXT NOT NULL,
    tipo VARCHAR(30) NOT NULL DEFAULT 'string',
    UNIQUE (evento_id, clave)
);

-- 8. TABLA: permisos_evento_usuario
CREATE TABLE IF NOT EXISTS permisos_evento_usuario (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INT NOT NULL,
    evento_id INT NOT NULL,
    nivel_acceso VARCHAR(30) NOT NULL DEFAULT 'anfitrion',
    UNIQUE (usuario_id, evento_id)
);

-- 9. TABLA: logs_auditoria
CREATE TABLE IF NOT EXISTS logs_auditoria (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evento_id INT NULL,
    usuario_id INT NULL,
    accion VARCHAR(80) NOT NULL,
    detalles TEXT NULL,
    ip_origen VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
