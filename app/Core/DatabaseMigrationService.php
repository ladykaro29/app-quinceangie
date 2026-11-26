<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Servicio de Auto-Migración y Auto-Reparación de Base de Datos
 * Plataforma: XV Experience
 * 
 * Garantiza que ante cualquier contenedor Docker nuevo, volumen persistente montado en Easypanel,
 * o base de datos previa, las tablas requeridas y las dos celebraciones (Angie y Sophia)
 * existan y estén configuradas automáticamente sin intervención manual.
 */
class DatabaseMigrationService
{
    private static bool $migrated = false;

    public static function ensureMigrated(PDO $pdo): void
    {
        if (self::$migrated) {
            return;
        }

        try {
            $isSqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');

            // 1. Verificar si la tabla eventos existe
            $hasEventos = false;
            if ($isSqlite) {
                $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='eventos'");
                $hasEventos = (bool)$stmt->fetchColumn();
            } else {
                $stmt = $pdo->query("SHOW TABLES LIKE 'eventos'");
                $hasEventos = (bool)$stmt->fetchColumn();
            }

            // 2. Si no existe, crear la estructura completa
            if (!$hasEventos) {
                self::createTables($pdo, $isSqlite);
            }

            // 3. Garantizar columnas evento_id en tablas auxiliares
            self::ensureEventoIdColumns($pdo, $isSqlite);

            // 3b. Garantizar tabla de recompensas del minijuego (Ganadores del Cofre)
            self::ensureJuegoRecompensasTable($pdo, $isSqlite);

            // 4. Garantizar catálogo base (roles, paquetes, plantillas)
            self::seedCatalog($pdo);

            // 5. Garantizar Evento 1 (Angie Karolina)
            self::seedAngie($pdo);

            // 6. Garantizar Evento 2 (Sophia Valentina) con datos oficiales
            self::seedSophia($pdo);

            self::$migrated = true;
        } catch (\Exception $e) {
            error_log("DatabaseMigrationService Error: " . $e->getMessage());
        }
    }

    private static function createTables(PDO $pdo, bool $isSqlite): void
    {
        $pk = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

        $pdo->exec("CREATE TABLE IF NOT EXISTS clientes (
            id $pk,
            nombre_contacto VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            telefono VARCHAR(30) NOT NULL,
            documento_identidad VARCHAR(40) NULL,
            pais VARCHAR(60) NOT NULL DEFAULT 'Venezuela',
            ciudad VARCHAR(80) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS paquetes (
            id $pk,
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
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS plantillas (
            id $pk,
            codigo VARCHAR(50) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT NULL,
            ruta_directorio VARCHAR(100) NOT NULL,
            activo TINYINT NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
            id $pk,
            nombre VARCHAR(50) NOT NULL UNIQUE,
            descripcion VARCHAR(255) NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id $pk,
            rol_id INT NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            activo TINYINT NOT NULL DEFAULT 1,
            ultimo_login DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS eventos (
            id $pk,
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
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS configuraciones_evento (
            id $pk,
            evento_id INT NOT NULL,
            clave VARCHAR(80) NOT NULL,
            valor TEXT NOT NULL,
            tipo VARCHAR(30) NOT NULL DEFAULT 'string',
            UNIQUE (evento_id, clave)
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS permisos_evento_usuario (
            id $pk,
            usuario_id INT NOT NULL,
            evento_id INT NOT NULL,
            nivel_acceso VARCHAR(30) NOT NULL DEFAULT 'anfitrion',
            UNIQUE (usuario_id, evento_id)
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS invitados (
            id $pk,
            evento_id INT NOT NULL DEFAULT 1,
            nombre_completo VARCHAR(150) NOT NULL,
            asistira TINYINT NOT NULL DEFAULT 1,
            codigo_rifa VARCHAR(50) UNIQUE,
            asistio_evento TINYINT NOT NULL DEFAULT 0,
            checkin_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS acompanantes (
            id $pk,
            invitado_id INT NOT NULL,
            nombre_completo VARCHAR(150) NOT NULL,
            codigo_rifa VARCHAR(50) UNIQUE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS fotos_fiesta (
            id $pk,
            evento_id INT NOT NULL DEFAULT 1,
            nombre_invitado VARCHAR(100) NOT NULL,
            mensaje TEXT NULL,
            archivo VARCHAR(255) NOT NULL,
            likes INT NOT NULL DEFAULT 0,
            ip VARCHAR(45) NULL,
            visible TINYINT NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS canciones (
            id $pk,
            evento_id INT NOT NULL DEFAULT 1,
            cancion VARCHAR(255) NOT NULL,
            nombre_invitado VARCHAR(100) NULL,
            ip VARCHAR(45) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS libro_recuerdos (
            id $pk,
            evento_id INT NOT NULL DEFAULT 1,
            nombre VARCHAR(100) NOT NULL,
            mensaje TEXT NOT NULL,
            icono VARCHAR(20) DEFAULT '✨',
            color VARCHAR(30) DEFAULT 'gold',
            likes INT NOT NULL DEFAULT 0,
            media_ref VARCHAR(255) NULL,
            ip VARCHAR(45) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        self::ensureJuegoRecompensasTable($pdo, $isSqlite);
    }

    public static function ensureJuegoRecompensasTable(PDO $pdo, ?bool $isSqlite = null): void
    {
        if ($isSqlite === null) {
            $isSqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        }
        $pk = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

        $pdo->exec("CREATE TABLE IF NOT EXISTS juego_recompensas (
            id $pk,
            evento_id INT NOT NULL DEFAULT 1,
            nombre_invitado VARCHAR(150) NOT NULL,
            recompensa_cofre TINYINT NOT NULL DEFAULT 0,
            recompensa_secreta TINYINT NOT NULL DEFAULT 0,
            capturas INT NOT NULL DEFAULT 0,
            puntos INT NOT NULL DEFAULT 0,
            combo_max INT NOT NULL DEFAULT 0,
            modo_accesible TINYINT NOT NULL DEFAULT 0,
            ip VARCHAR(45) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        if ($isSqlite) {
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_juego_recompensas_evento ON juego_recompensas (evento_id, created_at);");
        }
    }

    private static function ensureEventoIdColumns(PDO $pdo, bool $isSqlite): void
    {
        $tables = ['invitados', 'fotos_fiesta', 'libro_recuerdos', 'canciones'];
        foreach ($tables as $t) {
            try {
                if ($isSqlite) {
                    $cols = $pdo->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_ASSOC);
                    $names = array_column($cols, 'name');
                    if (!in_array('evento_id', $names)) {
                        $pdo->exec("ALTER TABLE $t ADD COLUMN evento_id INTEGER NOT NULL DEFAULT 1;");
                    }
                    if ($t === 'fotos_fiesta') {
                        if (!in_array('aprobada', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN aprobada INTEGER NOT NULL DEFAULT 1;");
                        }
                        if (!in_array('estado', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN estado TEXT NOT NULL DEFAULT 'aprobada';");
                        }
                        if (!in_array('subida_uuid', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN subida_uuid TEXT NULL;");
                        }
                        if (!in_array('hash_sha256', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN hash_sha256 TEXT NULL;");
                        }
                        if (!in_array('peso_bytes', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN peso_bytes INTEGER NULL;");
                        }
                        if (!in_array('ancho_px', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN ancho_px INTEGER NULL;");
                        }
                        if (!in_array('alto_px', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN alto_px INTEGER NULL;");
                        }
                        if (!in_array('mime_type', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN mime_type TEXT NULL;");
                        }
                        if (!in_array('moderada_at', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN moderada_at DATETIME NULL;");
                        }
                        if (!in_array('moderador_nombre', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN moderador_nombre TEXT NULL;");
                        }
                        if (!in_array('es_destacada', $names)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN es_destacada INTEGER NOT NULL DEFAULT 0;");
                        }
                        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_fotos_evento_estado ON fotos_fiesta (evento_id, estado, visible);");
                        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_fotos_evento_hash ON fotos_fiesta (evento_id, hash_sha256);");
                    }
                } else {
                    $cols = $pdo->query("SHOW COLUMNS FROM $t LIKE 'evento_id'")->fetchAll();
                    if (empty($cols)) {
                        $pdo->exec("ALTER TABLE $t ADD COLUMN evento_id INT NOT NULL DEFAULT 1;");
                    }
                    if ($t === 'fotos_fiesta') {
                        $colsAp = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'aprobada'")->fetchAll();
                        if (empty($colsAp)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN aprobada TINYINT NOT NULL DEFAULT 1;");
                        }
                        $colsEst = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'estado'")->fetchAll();
                        if (empty($colsEst)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN estado VARCHAR(20) NOT NULL DEFAULT 'aprobada';");
                        }
                        $colsUuid = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'subida_uuid'")->fetchAll();
                        if (empty($colsUuid)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN subida_uuid VARCHAR(64) NULL;");
                        }
                        $colsHash = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'hash_sha256'")->fetchAll();
                        if (empty($colsHash)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN hash_sha256 VARCHAR(64) NULL;");
                        }
                        $colsBytes = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'peso_bytes'")->fetchAll();
                        if (empty($colsBytes)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN peso_bytes INT NULL;");
                        }
                        $colsAncho = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'ancho_px'")->fetchAll();
                        if (empty($colsAncho)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN ancho_px INT NULL;");
                        }
                        $colsAlto = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'alto_px'")->fetchAll();
                        if (empty($colsAlto)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN alto_px INT NULL;");
                        }
                        $colsMime = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'mime_type'")->fetchAll();
                        if (empty($colsMime)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN mime_type VARCHAR(50) NULL;");
                        }
                        $colsModAt = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'moderada_at'")->fetchAll();
                        if (empty($colsModAt)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN moderada_at DATETIME NULL;");
                        }
                        $colsModNom = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'moderador_nombre'")->fetchAll();
                        if (empty($colsModNom)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN moderador_nombre VARCHAR(100) NULL;");
                        }
                        $colsDest = $pdo->query("SHOW COLUMNS FROM fotos_fiesta LIKE 'es_destacada'")->fetchAll();
                        if (empty($colsDest)) {
                            $pdo->exec("ALTER TABLE fotos_fiesta ADD COLUMN es_destacada TINYINT NOT NULL DEFAULT 0;");
                        }
                    }
                }
            } catch (\Exception $e) {}
        }
    }

    private static function seedCatalog(PDO $pdo): void
    {
        // Roles
        $pdo->exec("INSERT OR IGNORE INTO roles (id, nombre, descripcion) VALUES 
            (1, 'superadmin', 'Administrador Global'),
            (2, 'anfitrion', 'Anfitrión del Evento'),
            (3, 'staff', 'Personal de Puerta / Check-in')");

        // Paquetes
        $pdo->exec("INSERT OR IGNORE INTO paquetes (id, codigo, nombre, precio, moneda, max_invitados, max_fotos, max_videos, incluye_libro_3d, incluye_proyector_envivo, incluye_rifa_sorteo, incluye_checkin_qr, activo) VALUES 
            (1, 'royal_diamond', 'Royal Diamond Experience', 180.00, 'USD', 300, 250, 25, 1, 1, 1, 1, 1),
            (2, 'crystal_luxury', 'Crystal Luxury', 120.00, 'USD', 150, 100, 10, 1, 1, 1, 1, 1),
            (3, 'emerald_classic', 'Emerald Classic', 75.00, 'USD', 80, 50, 0, 0, 0, 1, 1, 1)");

        // Plantillas
        $pdo->exec("INSERT OR IGNORE INTO plantillas (id, codigo, nombre, descripcion, ruta_directorio, activo) VALUES 
            (1, 'emerald-prestige', 'Esmeralda Real & Dorado Glam', 'Diseño verde esmeralda con partículas doradas', 'emerald-prestige', 1),
            (2, 'rose-gold-princess', 'Rose Gold Princess & Flores', 'Diseño romántico en oro rosa, destellos suaves', 'rose-gold-princess', 1)");

        // Clientes
        $pdo->exec("INSERT OR IGNORE INTO clientes (id, nombre_contacto, email, telefono, pais, ciudad) VALUES 
            (1, 'Edwin Johan Avendaño y Lady Rivera', 'familia.angie@xvexperience.com', '+58 412 1234567', 'Venezuela', 'Mérida'),
            (2, 'Ramón Eduardo Rivas Avendaño y Estefany Yamileth Sánchez Ángulo', 'familia.rivas.sanchez@xvexperience.com', '+58 412 0000000', 'Venezuela', 'Mérida')");

        // Usuarios
        $adminHash = password_hash('admin2026', PASSWORD_BCRYPT);
        $angieHash = password_hash('angie2026', PASSWORD_BCRYPT);
        $sophiaHash = password_hash('sophia2026', PASSWORD_BCRYPT);
        $pdo->exec("INSERT OR IGNORE INTO usuarios (id, rol_id, nombre, email, password_hash, activo) VALUES 
            (1, 1, 'Super Administrador', 'admin@xvexperience.com', '$adminHash', 1),
            (2, 2, 'Anfitrión Angie Karolina', 'angie@xvexperience.com', '$angieHash', 1),
            (3, 2, 'Familia Rivas Sánchez', 'padres.sophia@xvexperience.com', '$sophiaHash', 1)");
    }

    private static function seedAngie(PDO $pdo): void
    {
        // Evento 1: Angie Karolina
        $stmtCheck = $pdo->query("SELECT id FROM eventos WHERE id = 1 OR slug = 'angie-xv'");
        $exists = $stmtCheck->fetchColumn();

        if (!$exists) {
            $pdo->exec("INSERT INTO eventos (id, cliente_id, paquete_id, plantilla_id, slug, nombre_celebracion, tipo_evento, festejada_nombre, fecha_evento, hora_evento, lugar_nombre, lugar_direccion, codigo_vestimenta, color_reserva, musica_url, portada_url, avatar_url, activo, publicado) VALUES 
                (1, 1, 1, 1, 'angie-xv', 'Mis XV Años', 'xv_anos', 'Angie Karolina Avendaño Rivera', '2026-10-03', '19:30:00', 'Colegio de Médicos', 'Colegio de Médicos del Estado Mérida, Av. Urdaneta, Mérida, Venezuela', 'Formal / Elegante: Por favor reserva el color verde esmeralda para la quinceañera.', 'Verde Esmeralda', 'musica.mp3', 'angie-portada.jpg', 'angie-xv.jpg', 1, 1)");
        }

        // Configuraciones de Angie
        $itinerarioAngie = [
            ["hora" => "7:30 PM", "actividad" => "Recepción de Invitados", "icono" => "fas fa-champagne-glasses"],
            ["hora" => "8:30 PM", "actividad" => "Entrada Triunfal de la Quinceañera", "icono" => "fas fa-wand-magic-sparkles"],
            ["hora" => "9:00 PM", "actividad" => "El Vals con su Padre", "icono" => "fas fa-music"],
            ["hora" => "9:30 PM", "actividad" => "Brindis Familiar & Palabras de Honor", "icono" => "fas fa-wine-glass-empty"],
            ["hora" => "10:00 PM", "actividad" => "Cena de Gala & Sesión de Fotos", "icono" => "fas fa-camera-retro"],
            ["hora" => "11:30 PM", "actividad" => "Apertura de Pista & La Hora Loca", "icono" => "fas fa-sparkles"]
        ];

        $triviaAngie = [
            ["question" => "¿Cuál es el color favorito de Angie para sus XV?", "options" => ["Verde Esmeralda", "Rosa Pastel", "Azul Marino", "Dorado"], "correct" => 0, "correctMsg" => "¡Correcto! El verde esmeralda es su color de gala 👑✨", "wrongMsg" => "¡Casi! Su color oficial es Verde Esmeralda 💚"],
            ["question" => "¿Qué fecha especial celebra Angie sus 15 años?", "options" => ["3 de Octubre de 2026", "15 de Noviembre", "20 de Diciembre", "5 de Enero"], "correct" => 0, "correctMsg" => "¡Exacto! El sábado 3 de Octubre de 2026 🥂✨", "wrongMsg" => "¡El 3 de Octubre de 2026! 📅"]
        ];

        $tokensAngie = [
            "--verde-oscuro" => "#062E25",
            "--verde-medio" => "#006B4F",
            "--verde-claro" => "#2F8F68",
            "--dorado" => "#C8A24A",
            "--dorado-claro" => "#E7D49A",
            "--crema" => "#FFF8EC"
        ];

        $configs = [
            'nombre_corto' => 'Angie Karolina',
            'padres_nombres' => 'Edwin Johan Avendaño y Lady Rivera',
            'nombre_padre' => 'Edwin Johan Avendaño',
            'nombre_madre' => 'Lady Rivera',
            'padrinos_nombres' => 'Padrinos de Angie',
            'cancion_titulo' => 'Valiente - Con toda libertad',
            'tematica' => 'Esmeralda Real & Dorado Glam',
            'frase_biblica' => 'Dicen que el destino está entrelazado en cada paso que damos, y que solo hace falta tener un corazón valiente para descubrir la magia de nuestra propia historia...',
            'frase_bendicion' => 'Con la bendición de Dios y el cariño de mi familia, tengo el honor de invitarte a celebrar conmigo esta fecha tan esperada y dar la bienvenida a mis quince años.',
            'frase_agradecimiento' => 'Tu presencia es el mejor regalo. ¡Celebremos juntos esta noche mágica! ✨ Con todo mi cariño, Angie Karolina.',
            'fecha_nacimiento' => '2026-09-23',
            'google_calendar_url' => 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=Mis+XV+A%C3%B1os+-+Angie+Karolina&dates=20261004T000000Z%2F20261004T060000Z&details=%C2%A1Celebremos+juntos+los+XV+A%C3%B1os+de+Angie+Karolina!+Una+noche+m%C3%A1gica+llena+de+alegr%C3%ADa+y+momentos+inolvidables.&location=Colegio+de+M%C3%A9dicos+del+Estado+M%C3%A9rida%2C+Av.+Urdaneta%2C+M%C3%A9rida%2C+Venezuela',
            'itinerario_json' => json_encode($itinerarioAngie, JSON_UNESCAPED_UNICODE),
            'trivia_json' => json_encode($triviaAngie, JSON_UNESCAPED_UNICODE),
            'design_tokens' => json_encode($tokensAngie, JSON_UNESCAPED_UNICODE)
        ];

        $stmtCfg = $pdo->prepare("INSERT OR REPLACE INTO configuraciones_evento (evento_id, clave, valor, tipo) VALUES (1, :c, :v, :t)");
        foreach ($configs as $k => $v) {
            $stmtCfg->execute([
                ':c' => $k,
                ':v' => $v,
                ':t' => str_ends_with($k, '_json') || $k === 'design_tokens' ? 'json' : 'string'
            ]);
        }
    }

    private static function seedSophia(PDO $pdo): void
    {
        // Evento 2: Sophia Valentina
        $stmtCheck = $pdo->query("SELECT id FROM eventos WHERE id = 2 OR slug = 'sophia-xv'");
        $exists = $stmtCheck->fetchColumn();

        if (!$exists) {
            $pdo->exec("INSERT INTO eventos (id, cliente_id, paquete_id, plantilla_id, slug, nombre_celebracion, tipo_evento, festejada_nombre, fecha_evento, hora_evento, lugar_nombre, lugar_direccion, codigo_vestimenta, color_reserva, musica_url, portada_url, avatar_url, activo, publicado) VALUES 
                (2, 2, 2, 2, 'sophia-xv', 'Mis XV Años - Sophia Valentina', 'xv_anos', 'Sophia Valentina Rivas Sánchez', '2026-12-21', '19:30:00', 'Salón Nova Roma', 'Dirección pendiente de confirmación', 'Formal de Gala: sugerimos tonos suaves y elegantes. Por favor reserva los tonos rosa y dorado champagne para la quinceañera.', 'Rosa y Dorado Champagne', 'storage/media/2/audio/dance_sugar_plum_fairy.mp3', 'sophia-portada.jpg', 'sophia-xv.jpg', 1, 1)");
        } else {
            // Actualizar para asegurar datos oficiales si ya existía
            $pdo->exec("UPDATE eventos SET 
                festejada_nombre = 'Sophia Valentina Rivas Sánchez',
                nombre_celebracion = 'Mis XV Años - Sophia Valentina',
                fecha_evento = '2026-12-21',
                hora_evento = '19:30:00',
                lugar_nombre = 'Salón Nova Roma',
                lugar_direccion = 'Dirección pendiente de confirmación',
                codigo_vestimenta = 'Formal de Gala: sugerimos tonos suaves y elegantes. Por favor reserva los tonos rosa y dorado champagne para la quinceañera.',
                color_reserva = 'Rosa y Dorado Champagne',
                musica_url = 'storage/media/2/audio/dance_sugar_plum_fairy.mp3',
                portada_url = 'sophia-portada.jpg',
                avatar_url = 'sophia-xv.jpg',
                activo = 1,
                publicado = 1
                WHERE id = 2");
        }

        $tokensSophia = [
            "--color-rosa-blush"     => "#EBC5CC",
            "--color-rosa-empolvado" => "#D9A5B5",
            "--color-lavanda"        => "#C6AEDB",
            "--color-dorado-champagne" => "#C5A16A",
            "--color-marfil-calido"  => "#FFF3E7",
            "--color-marron-rosado"  => "#79544D",
            "--verde-oscuro"         => "#4A2E35",
            "--verde-medio"          => "#6E4550",
            "--verde-claro"          => "#D9A5B5",
            "--dorado"               => "#C5A16A",
            "--dorado-claro"         => "#EAD8B8",
            "--dorado-brillante"     => "#FBF3E3",
            "--crema"                => "#FFF3E7",
            "--marron"               => "#79544D",
            "--rosa-suave"           => "#EBC5CC",
            "--lavanda"              => "#C6AEDB",
            "--lavanda-suave"        => "#EAE2F3"
        ];

        $itinerarioSophia = [
            ["hora" => "7:30 PM", "actividad" => "Recepción de Invitados & Cóctel de Bienvenida", "icono" => "fas fa-champagne-glasses"],
            ["hora" => "8:30 PM", "actividad" => "Entrada Mágica de Sophia Valentina", "icono" => "fas fa-wand-magic-sparkles"],
            ["hora" => "9:00 PM", "actividad" => "El Vals de la Princesa con sus Padres", "icono" => "fas fa-music"],
            ["hora" => "9:30 PM", "actividad" => "Brindis Familiar & Palabras de Honor", "icono" => "fas fa-wine-glass-empty"],
            ["hora" => "10:00 PM", "actividad" => "Cena de Gala & Fotografías del Recuerdo", "icono" => "fas fa-camera-retro"],
            ["hora" => "11:30 PM", "actividad" => "Apertura de la Pista & Gran Celebración", "icono" => "fas fa-sparkles"]
        ];

        $triviaSophia = [
            ["question" => "¿Cómo se llama la hermosa quinceañera?", "options" => ["Sophia Valentina", "Victoria Sofía", "Isabella Valentina", "Sofía Camila"], "correct" => 0, "correctMsg" => "¡Exacto! Sophia Valentina, nuestra bella princesa 👑🌸", "wrongMsg" => "Su nombre completo es Sophia Valentina ✨"],
            ["question" => "¿Qué mes especial celebra sus 15 años?", "options" => ["Diciembre", "Octubre", "Noviembre", "Septiembre"], "correct" => 0, "correctMsg" => "¡Muy bien! Noche mágica de diciembre ❄️🌸", "wrongMsg" => "¡El 21 de Diciembre de 2026! 🎄✨"]
        ];

        $configsSophia = [
            'nombre_corto' => 'Sophia Valentina',
            'padres_nombres' => 'Ramón Eduardo Rivas Avendaño y Estefany Yamileth Sánchez Ángulo',
            'nombre_padre' => 'Ramón Eduardo Rivas Avendaño',
            'nombre_madre' => 'Estefany Yamileth Sánchez Ángulo',
            'padrinos_nombres' => 'Padrinos de Sophia Valentina',
            'cancion_titulo' => 'Danza del Hada de Azúcar - Tchaikovsky (Fantasía)',
            'tematica' => 'Mariposas rosadas, flores, lavanda y dorado champagne',
            'frase_biblica' => 'Como una mariposa que despliega sus alas al viento, hoy comienzo a escribir el más hermoso capítulo de mi historia...',
            'frase_bendicion' => 'Con la bendición de Dios y el amor infinito de nuestros corazones, los invitamos a celebrar los quince años de nuestra amada hija.',
            'frase_agradecimiento' => 'Tu cariño, compañía y alegría hacen que esta noche de mariposas y sueños sea verdaderamente inolvidable. ¡Gracias de corazón!',
            'fecha_nacimiento' => '2026-12-21',
            'google_calendar_url' => 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=Mis+XV+A%C3%B1os+-+Sophia+Valentina&dates=20261221T233000Z%2F20261222T060000Z&details=%C2%A1Acomp%C3%A1%C3%B1anos+a+celebrar+los+XV+A%C3%B1os+de+Sophia+Valentina!+Una+noche+m%C3%A1gica+de+mariposas+y+flores.&location=Sal%C3%B3n+Nova+Roma',
            'itinerario_json' => json_encode($itinerarioSophia, JSON_UNESCAPED_UNICODE),
            'trivia_json' => json_encode($triviaSophia, JSON_UNESCAPED_UNICODE),
            'design_tokens' => json_encode($tokensSophia, JSON_UNESCAPED_UNICODE)
        ];

        $stmtConfig = $pdo->prepare("INSERT OR REPLACE INTO configuraciones_evento (evento_id, clave, valor, tipo) VALUES (2, :clave, :valor, :tipo)");
        foreach ($configsSophia as $k => $v) {
            $stmtConfig->execute([
                ':clave' => $k,
                ':valor' => $v,
                ':tipo' => str_ends_with($k, '_json') || $k === 'design_tokens' ? 'json' : 'string'
            ]);
        }

        // Dedicatoria oficial en libro_recuerdos
        $stmtDed = $pdo->query("SELECT id FROM libro_recuerdos WHERE evento_id = 2 AND id = 100");
        if (!$stmtDed->fetchColumn()) {
            $pdo->exec("INSERT OR IGNORE INTO libro_recuerdos (id, evento_id, nombre, mensaje, icono, color, likes, created_at) VALUES 
                (100, 2, 'Ramón y Estefany (Tus Padres)', 'Hija amada Sophia Valentina, eres la bendición más grande que Dios nos entregó. Despliega tus alas con valentía, nobleza y alegría siempre. ¡Te amamos con toda el alma princesa! 🦋🌸💖', '🦋', 'rose', 45, '2026-12-21 19:30:00')");
        }
    }
}
