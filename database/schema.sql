-- ============================================================
-- Invitación XV Años - Angie Karolina Avendaño Rivera
-- Schema de Base de Datos MySQL/MariaDB
-- ============================================================

CREATE DATABASE IF NOT EXISTS invitacion_xv
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE invitacion_xv;

-- Tabla principal de invitados que confirman asistencia
CREATE TABLE IF NOT EXISTS invitados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    asistira TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Sí asistirá, 0 = No podrá asistir',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_asistira (asistira),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de acompañantes vinculados a un invitado
CREATE TABLE IF NOT EXISTS acompanantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invitado_id INT NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    CONSTRAINT fk_invitado
        FOREIGN KEY (invitado_id) REFERENCES invitados(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
