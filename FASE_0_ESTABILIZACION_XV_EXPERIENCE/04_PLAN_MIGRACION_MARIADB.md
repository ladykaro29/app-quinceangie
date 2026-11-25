# 04. PLAN TÉCNICO DE MIGRACIÓN: SQLITE A MARIADB — XV EXPERIENCE

**Documento:** 04 de 07  
**Fecha:** 25 de noviembre de 2026  
**Área:** Arquitectura de Datos, Base de Datos y DevOps  
**Proyecto:** XV Experience (Kick-Off Development)

---

## 1. OBJETIVO Y JUSTIFICACIÓN TÉCNICA

Actualmente la plataforma opera sobre **SQLite 3** (`database/invitacion_xv.sqlite`) montado en un volumen persistente de Docker en Easypanel. Si bien SQLite ofrece portabilidad total y cero configuración para 1 o 2 celebraciones con tráfico moderado, las pruebas de estrés ejecutadas evidenciaron el error:
```
DatabaseMigrationService Error: SQLSTATE[HY000]: General error: 5 database is locked
```
Este bloqueo a nivel de archivo ocurre cuando múltiples invitados suben fotos simultáneamente o confirman asistencia al mismo tiempo, ya que SQLite bloquea la base de datos completa para operaciones de escritura (`WAL mode` mitiga pero no elimina la contención en alta concurrencia).

**Objetivo:** Diseñar un plan exhaustivo, determinista y seguro para migrar hacia **MariaDB 10.11+ / MySQL 8.0** en producción sin tiempo de inactividad perceptivo ni pérdida de integridad referencial.

---

## 2. ESQUEMA DE DESTINO EN MARIADB (DDL COMPLETO)

El esquema de destino implementa el motor de almacenamiento `InnoDB`, codificación `utf8mb4_unicode_ci` y claves foráneas reales con `ON DELETE CASCADE`:

```sql
-- 1. Tabla de Celebraciones / Eventos
CREATE TABLE IF NOT EXISTS `eventos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(80) NOT NULL UNIQUE,
  `titulo` VARCHAR(255) NOT NULL,
  `festejada_nombre` VARCHAR(255) NOT NULL,
  `nombre_celebracion` VARCHAR(255) DEFAULT 'Mis XV Años',
  `fecha_evento` DATE NOT NULL,
  `hora_evento` TIME NOT NULL DEFAULT '19:30:00',
  `lugar_nombre` VARCHAR(255) NOT NULL,
  `lugar_direccion` VARCHAR(255) NULL,
  `coordenadas_gps` VARCHAR(100) NULL,
  `color_primario` VARCHAR(20) DEFAULT '#062E25',
  `color_secundario` VARCHAR(20) DEFAULT '#C8A24A',
  `color_acento` VARCHAR(20) DEFAULT '#D4AF37',
  `color_fondo` VARCHAR(20) DEFAULT '#021410',
  `musica_url` VARCHAR(255) DEFAULT 'musica.mp3',
  `portada_url` VARCHAR(255) DEFAULT 'angie-portada.jpg',
  `avatar_url` VARCHAR(255) DEFAULT 'angie-xv.jpg',
  `tema_plantilla` VARCHAR(50) DEFAULT 'emerald_luxury',
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_eventos_slug` (`slug`),
  INDEX `idx_eventos_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Configuraciones Dinámicas del Evento
CREATE TABLE IF NOT EXISTS `configuraciones_evento` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `evento_id` INT UNSIGNED NOT NULL,
  `clave` VARCHAR(80) NOT NULL,
  `valor` LONGTEXT NULL,
  `tipo` VARCHAR(20) DEFAULT 'string',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_evento_clave` (`evento_id`, `clave`),
  CONSTRAINT `fk_cfg_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Invitados
CREATE TABLE IF NOT EXISTS `invitados` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `evento_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `nombre_completo` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50) NULL,
  `email` VARCHAR(150) NULL,
  `asistira` TINYINT(1) NOT NULL DEFAULT 1,
  `cantidad_pases` INT NOT NULL DEFAULT 1,
  `codigo_rifa` VARCHAR(30) NULL,
  `codigo_qr` VARCHAR(255) NULL,
  `asistio_evento` TINYINT(1) NOT NULL DEFAULT 0,
  `checkin_at` DATETIME NULL,
  `ip_registro` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_invitados_evento` (`evento_id`),
  INDEX `idx_invitados_codigo_rifa` (`codigo_rifa`),
  INDEX `idx_invitados_asistio` (`asistio_evento`),
  CONSTRAINT `fk_inv_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Acompañantes
CREATE TABLE IF NOT EXISTS `acompanantes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `invitado_id` INT UNSIGNED NOT NULL,
  `nombre_completo` VARCHAR(255) NOT NULL,
  `codigo_rifa` VARCHAR(30) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_acomp_invitado` (`invitado_id`),
  CONSTRAINT `fk_acomp_invitado` FOREIGN KEY (`invitado_id`) REFERENCES `invitados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Galería Oficial de la Quinceañera
CREATE TABLE IF NOT EXISTS `galeria` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `evento_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `archivo` VARCHAR(255) NOT NULL,
  `titulo` VARCHAR(150) NULL,
  `seccion` VARCHAR(50) DEFAULT 'estudio',
  `orden` INT NOT NULL DEFAULT 0,
  `visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_galeria_evento` (`evento_id`, `visible`),
  CONSTRAINT `fk_gal_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Álbum Colaborativo (Fotos de Invitados en Vivo)
CREATE TABLE IF NOT EXISTS `fotos_fiesta` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `evento_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `archivo` VARCHAR(255) NOT NULL,
  `nombre_invitado` VARCHAR(150) NULL,
  `mensaje` TEXT NULL,
  `visible` TINYINT(1) NOT NULL DEFAULT 1,
  `aprobada` TINYINT(1) NOT NULL DEFAULT 1,
  `ip` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_fotos_evento_envivo` (`evento_id`, `visible`, `aprobada`, `created_at`),
  CONSTRAINT `fk_ff_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Libro de Recuerdos y Dedicatorias
CREATE TABLE IF NOT EXISTS `libro_recuerdos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `evento_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `nombre` VARCHAR(150) NOT NULL,
  `mensaje` TEXT NOT NULL,
  `icono` VARCHAR(50) DEFAULT '✨',
  `color` VARCHAR(30) DEFAULT 'gold',
  `likes` INT NOT NULL DEFAULT 0,
  `media_ref` VARCHAR(255) NULL,
  `ip` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_libro_evento` (`evento_id`, `created_at`),
  CONSTRAINT `fk_lr_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Canciones Pedidas
CREATE TABLE IF NOT EXISTS `canciones` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `evento_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `cancion` VARCHAR(255) NOT NULL,
  `nombre_invitado` VARCHAR(150) NULL,
  `ip` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_canciones_evento` (`evento_id`),
  CONSTRAINT `fk_canc_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Usuarios Administrativos
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `rol_id` INT UNSIGNED NOT NULL DEFAULT 2,
  `nombre` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `ultimo_login` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Permisos Usuario-Evento
CREATE TABLE IF NOT EXISTS `permisos_evento_usuario` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED NOT NULL,
  `evento_id` INT UNSIGNED NOT NULL,
  `nivel_acceso` VARCHAR(20) DEFAULT 'editor',
  UNIQUE KEY `uk_usr_ev` (`usuario_id`, `evento_id`),
  CONSTRAINT `fk_prm_usr` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prm_ev` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. COMPATIBILIDAD DE CONSULTAS SQL

Todas las consultas en el código de XV Experience utilizan PDO con sentencias preparadas (`$pdo->prepare()`) y sintaxis ANSI SQL compatible:
- No se utilizan funciones exclusivas de SQLite como `strftime()` en hot-paths (se utiliza PHP `date()` o SQL estándar `CURRENT_TIMESTAMP`).
- Las inserciones utilizan parámetros con nombres `:clave` compatibles con el driver `pdo_mysql`.
- El manejo de auto-incremento funciona idénticamente con `$pdo->lastInsertId()`.

---

## 4. SCRIPT AUTOMATIZADO DE MIGRACIÓN Y CONCILIACIÓN (`tools/migrate_sqlite_to_mariadb.php`)

Se diseñó la arquitectura del script extractor y cargador:
1. Conecta simultáneamente a `sqlite:database/invitacion_xv.sqlite` y a `mysql:host=$host;dbname=$db`.
2. Lee los metadatos de las 14 tablas en SQLite.
3. Desactiva temporalmente las comprobaciones de claves foráneas: `SET FOREIGN_KEY_CHECKS = 0;`.
4. Transfiere tabla por tabla en bloques de 200 filas (`chunked inserts`) para optimizar memoria.
5. Reactiva claves foráneas: `SET FOREIGN_KEY_CHECKS = 1;`.
6. **Conciliación Estricta:** Compara `COUNT(*)` entre SQLite y MariaDB para cada tabla. Si existe discrepancia de incluso 1 registro, detiene la operación y emite alerta.

---

## 5. CONFIGURACIÓN DOCKER Y VARIABLES DE ENTORNO EN EASYPANEL

### Variables de Entorno en el Servicio Web de Easypanel:
```env
DB_CONNECTION=mysql
DB_HOST=mariadb-service
DB_PORT=3306
DB_DATABASE=xvexperience_prod
DB_USERNAME=xv_user
DB_PASSWORD=Strong_Generated_Pass_2026!
```

### Eliminación del Fallback Silencioso en Producción:
En `config/database.php` y `app/Core/Database.php`:
- Si `DB_CONNECTION === 'mysql'`, el sistema **DEBE intentar conectar exclusivamente a MySQL/MariaDB**.
- Si la conexión falla, **NO DEBE realizar un fallback silencioso a SQLite**, ya que esto provocaría lecturas de una base de datos desincronizada.
- En su lugar, debe lanzar un error explícito `DatabaseConnectionException` y registrar el fallo en el log de Easypanel para que DevOps sea alertado de inmediato.

---

## 6. PROCEDIMIENTO DE REVERSIÓN (ROLLBACK)

Si durante la puesta en marcha de MariaDB ocurre una inconsistencia en producción:
1. Revertir la variable en Easypanel: `DB_CONNECTION=sqlite`.
2. Reiniciar el contenedor en 1 clic.
3. El sistema vuelve a leer inmediatamente del archivo `/var/www/html/database/invitacion_xv.sqlite`, el cual permanece intacto en el volumen persistente como respaldo frío.
