# ARQUITECTURA DE SOFTWARE — PLATAFORMA "XV EXPERIENCE"
## DOCUMENTO 03: MODELO DE DATOS MULTI-CELEBRACIÓN

---

### 1. ESTÁNDARES DEL MODELO RELACIONAL

- **Motor:** MySQL 8.0+ / MariaDB 10.5+ (`ENGINE=InnoDB`).
- **Juego de caracteres:** `utf8mb4`.
- **Cotejamiento (Collation):** `utf8mb4_unicode_ci`.
- **Convención de nomenclatura:** `snake_case` para tablas y campos. Claves primarias siempre denominadas `id` (`BIGINT UNSIGNED AUTO_INCREMENT` o `INT UNSIGNED AUTO_INCREMENT`).
- **Marcas temporales:** Cada tabla incluye `created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` y las tablas transaccionales incorporan `updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP`.

---

### 2. DICCIONARIO DE DATOS DETALLADO

#### 2.1. Tabla: `clientes`
Almacena la información de contacto y facturación del comprador de la invitación (padre, madre o anfitrión).

| Campo | Tipo | Nulo | Default | Restricciones / Índices | Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT UNSIGNED` | NO | *Auto* | **PRIMARY KEY** | Identificador único del cliente |
| `nombre_contacto` | `VARCHAR(150)` | NO | - | - | Nombre completo del comprador o representante |
| `email` | `VARCHAR(150)` | NO | - | **UNIQUE** (`uk_cliente_email`) | Correo electrónico principal de facturación |
| `telefono` | `VARCHAR(30)` | NO | - | `INDEX` (`idx_cliente_telefono`) | Teléfono móvil con código de país para WhatsApp |
| `documento_identidad`| `VARCHAR(40)` | SÍ | `NULL` | - | Cédula, DNI o NIF para emisión de recibos |
| `pais` | `VARCHAR(60)` | NO | `'Venezuela'` | - | País de residencia del cliente |
| `ciudad` | `VARCHAR(80)` | NO | - | - | Ciudad |
| `created_at` | `DATETIME` | NO | `CURRENT_TIMESTAMP` | - | Fecha de registro en el sistema |

---

#### 2.2. Tabla: `paquetes`
Catálogo de planes comerciales que determinan límites técnicos y módulos habilitados.

| Campo | Tipo | Nulo | Default | Restricciones / Índices | Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT UNSIGNED` | NO | *Auto* | **PRIMARY KEY** | Identificador del paquete comercial |
| `codigo` | `VARCHAR(30)` | NO | - | **UNIQUE** (`uk_paquete_codigo`) | Código interno (`basico`, `premium`, `royal_diamond`) |
| `nombre` | `VARCHAR(100)` | NO | - | - | Nombre público del paquete |
| `precio` | `DECIMAL(10,2)` | NO | `0.00` | - | Precio de venta |
| `moneda` | `VARCHAR(3)` | NO | `'USD'` | - | Código ISO de moneda |
| `max_invitados` | `INT` | NO | `100` | - | Cupo máximo de confirmados |
| `max_fotos` | `INT` | NO | `50` | - | Límite de fotos para el álbum en vivo |
| `max_videos` | `INT` | NO | `5` | - | Límite de videos en el Libro 3D |
| `incluye_libro_3d` | `TINYINT(1)` | NO | `1` | - | `1` = Sí incluye grimorio 3D |
| `incluye_proyector_envivo` | `TINYINT(1)` | NO | `1` | - | `1` = Sí incluye pantalla para TV / Video Beam |
| `incluye_rifa_sorteo` | `TINYINT(1)` | NO | `1` | - | `1` = Incluye módulo de ruleta y boletos de rifa |
| `incluye_checkin_qr` | `TINYINT(1)` | NO | `1` | - | `1` = Incluye escáner WebRTC en puerta |
| `activo` | `TINYINT(1)` | NO | `1` | `INDEX` (`idx_paquete_activo`) | Visibilidad en catálogo de ventas |

---

#### 2.3. Tabla: `pedidos` y Tabla: `pagos`
Gestión de órdenes de compra y conciliación financiera de las suscripciones.

```sql
CREATE TABLE IF NOT EXISTS pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    paquete_id INT UNSIGNED NOT NULL,
    codigo_pedido VARCHAR(30) NOT NULL UNIQUE,
    estado ENUM('pendiente', 'pagado', 'en_diseno', 'aprobado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    total DECIMAL(10,2) NOT NULL,
    fecha_expiracion DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pedido_paquete FOREIGN KEY (paquete_id) REFERENCES paquetes(id) ON DELETE RESTRICT,
    INDEX idx_pedido_estado (estado),
    INDEX idx_pedido_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    referencia_transaccion VARCHAR(100) NOT NULL UNIQUE,
    pasarela ENUM('stripe', 'paypal', 'mercadopago', 'transferencia', 'efectivo') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    moneda VARCHAR(3) NOT NULL DEFAULT 'USD',
    estado ENUM('pendiente', 'completado', 'fallido', 'reembolsado') NOT NULL DEFAULT 'completado',
    fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pago_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE RESTRICT,
    INDEX idx_pago_pedido (pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

#### 2.4. Tabla: `plantillas`
Registro de temas visuales disponibles en la plataforma.

| Campo | Tipo | Nulo | Default | Restricciones | Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT UNSIGNED` | NO | *Auto* | **PRIMARY KEY** | Identificador de la plantilla |
| `codigo` | `VARCHAR(40)` | NO | - | **UNIQUE** | Código del tema (`esmeralda-real`, `rose-gold`, etc.) |
| `nombre` | `VARCHAR(100)` | NO | - | - | Nombre del tema |
| `version` | `VARCHAR(10)` | NO | `'1.0.0'` | - | Versión de la plantilla |
| `ruta_vistas` | `VARCHAR(150)` | NO | - | - | Ruta en disco hacia `/templates/themes/{codigo}` |
| `preview_thumbnail`| `VARCHAR(255)` | SÍ | `NULL` | - | Imagen de previsualización para el catálogo |
| `activo` | `TINYINT(1)` | NO | `1` | - | Disponibilidad de selección |

---

#### 2.5. Tabla: `eventos` (Entidad Principal de la Celebración)
Almacena el registro maestro de cada fiesta individual.

| Campo | Tipo | Nulo | Default | Restricciones / Índices | Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT UNSIGNED` | NO | *Auto* | **PRIMARY KEY** | Identificador único del evento |
| `pedido_id` | `INT UNSIGNED` | NO | - | **UNIQUE** (`uk_evento_pedido`), FK | Pedido que financió la creación |
| `plantilla_id` | `INT UNSIGNED` | NO | - | FK -> `plantillas(id)` | Plantilla visual activa |
| `slug` | `VARCHAR(80)` | NO | - | **UNIQUE** (`uk_evento_slug`) | Slug en URL (`angie-xv`, `sophia-xv`) |
| `subdominio` | `VARCHAR(50)` | SÍ | `NULL` | **UNIQUE** (`uk_evento_subdominio`) | Subdominio opcional (`angie`, `sophia`) |
| `tipo_evento` | `ENUM(...)` | NO | `'xv_anos'` | - | `'xv_anos', 'boda', 'graduacion', 'aniversario'` |
| `nombre_festejado`| `VARCHAR(150)` | NO | - | - | Nombre de la festejada ("Angie Karolina", etc.) |
| `titulo_evento` | `VARCHAR(180)` | NO | - | - | "Mis XV Años", "Nuestra Boda", etc. |
| `fecha_evento` | `DATETIME` | NO | - | `INDEX` (`idx_evento_fecha`) | Fecha y hora de inicio de la celebración |
| `fecha_nacimiento`| `DATETIME` | SÍ | `NULL` | - | Fecha de nacimiento real para cálculo de edad/trivia |
| `timezone` | `VARCHAR(50)` | NO | `'America/Caracas'` | - | Zona horaria del evento para cuenta regresiva |
| `estado` | `ENUM(...)` | NO | `'borrador'` | `INDEX` (`idx_evento_estado`) | `'borrador', 'publicado', 'pausado', 'finalizado'` |
| `contrasena_acceso`| `VARCHAR(255)`| SÍ | `NULL` | - | Clave opcional para eventos privados |
| `created_at` | `DATETIME` | NO | `CURRENT_TIMESTAMP` | - | Fecha de alta |

---

#### 2.6. Tabla: `configuraciones_evento`
Estructura híbrida: columnas tipadas de uso frecuente + columnas `JSON` para máxima flexibilidad estética.

```sql
CREATE TABLE IF NOT EXISTS configuraciones_evento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL UNIQUE,
    nombre_padre VARCHAR(150) NULL,
    nombre_madre VARCHAR(150) NULL,
    nombre_padrinos VARCHAR(200) NULL,
    direccion_salon VARCHAR(255) NOT NULL,
    nombre_salon VARCHAR(150) NOT NULL,
    url_google_maps TEXT NULL,
    dress_code_titulo VARCHAR(80) NOT NULL DEFAULT 'ELEGANTE',
    dress_code_descripcion TEXT NULL,
    dress_code_colores_json JSON NULL,
    lluvia_sobres_texto TEXT NULL,
    buzon_instrucciones TEXT NULL,
    audio_musica_url VARCHAR(255) NOT NULL DEFAULT 'musica.mp3',
    foto_portada_url VARCHAR(255) NOT NULL,
    foto_despedida_url VARCHAR(255) NOT NULL,
    itinerario_json JSON NOT NULL,
    trivia_preguntas_json JSON NOT NULL,
    tokens_estilo_css_json JSON NOT NULL,
    modulos_habilitados_json JSON NOT NULL,
    CONSTRAINT fk_config_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Estructura de los campos JSON:
1. **`itinerario_json`:**
   ```json
   [
     {"hora": "8:00 PM", "actividad": "Recepción de invitados", "icono": "fa-clock"},
     {"hora": "9:00 PM", "actividad": "Entrada de la Quinceañera", "icono": "fa-crown"},
     {"hora": "9:30 PM", "actividad": "El tradicional Vals y Brindis", "icono": "fa-glass-cheers"},
     {"hora": "10:15 PM", "actividad": "Pasapalos & Cócteles especiales", "icono": "fa-cocktail"},
     {"hora": "11:00 PM", "actividad": "Sorpresa & Baile", "icono": "fa-music"},
     {"hora": "12:00 AM", "actividad": "Hora Loca & Fiesta", "icono": "fa-birthday-cake"}
   ]
   ```
2. **`trivia_preguntas_json`:**
   ```json
   [
     {
       "pregunta": "¿Cuál es el color favorito de Angie?",
       "opciones": ["Rosa", "Verde esmeralda", "Azul cielo", "Morado"],
       "correcta": 1,
       "feedback_correcto": "¡Exacto! El verde esmeralda es su favorito.",
       "feedback_incorrecto": "La respuesta correcta es Verde esmeralda."
     }
   ]
   ```
3. **`tokens_estilo_css_json`:**
   ```json
   {
     "--color-primary": "#062E25",
     "--color-secondary": "#006B4F",
     "--color-gold": "#C8A24A",
     "--color-gold-light": "#E7D49A",
     "--color-bg": "#021410",
     "--font-script": "'Great Vibes', cursive",
     "--font-serif": "'Playfair Display', serif",
     "--font-sans": "'Montserrat', sans-serif"
   }
   ```
4. **`modulos_habilitados_json`:**
   ```json
   {
     "tarjeta_historia": true,
     "cuenta_regresiva": true,
     "trivia": true,
     "playlist": true,
     "lluvia_sobres": true,
     "rsvp": true,
     "fotos_envivo": true,
     "libro_3d": true,
     "proyector_tv": true,
     "sorteo_rifa": true,
     "checkin_qr": true
   }
   ```

---

#### 2.7. Tablas Operativas del Evento (`invitados`, `acompanantes`, `fotografias_evento`, `videos_evento`, `canciones_evento`, `dedicatorias_recuerdos`)

Todas las tablas operativas garantizan el aislamiento por evento mediante la presencia obligatoria de `evento_id` y claves foráneas en cascada.

```sql
-- 1. Tabla de Invitados (Aislada por evento_id)
CREATE TABLE IF NOT EXISTS invitados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    asistira TINYINT(1) NOT NULL DEFAULT 1,
    codigo_rifa VARCHAR(20) NULL,
    asistio_evento TINYINT(1) NOT NULL DEFAULT 0,
    checkin_at DATETIME NULL,
    hash_pase_vip VARCHAR(64) NULL,
    telefono VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    notas TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invitado_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_evento_codigo_rifa (evento_id, codigo_rifa),
    INDEX idx_evento_asistira (evento_id, asistira),
    INDEX idx_evento_checkin (evento_id, asistio_evento),
    INDEX idx_evento_nombre (evento_id, nombre_completo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Acompañantes
CREATE TABLE IF NOT EXISTS acompanantes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    invitado_id INT UNSIGNED NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    codigo_rifa VARCHAR(20) NULL,
    asistio_evento TINYINT(1) NOT NULL DEFAULT 0,
    checkin_at DATETIME NULL,
    CONSTRAINT fk_acomp_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    CONSTRAINT fk_acomp_invitado FOREIGN KEY (invitado_id) REFERENCES invitados(id) ON DELETE CASCADE,
    UNIQUE KEY uk_evento_acomp_rifa (evento_id, codigo_rifa),
    INDEX idx_acomp_invitado (invitado_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Fotografías de la Celebración
CREATE TABLE IF NOT EXISTS fotografias_evento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    nombre_autor VARCHAR(150) NOT NULL,
    mensaje TEXT NULL,
    archivo_nombre VARCHAR(255) NOT NULL,
    url_recurso VARCHAR(500) NOT NULL,
    storage_provider ENUM('local', 's3', 'r2') NOT NULL DEFAULT 'local',
    tamano_bytes INT UNSIGNED NOT NULL DEFAULT 0,
    likes INT UNSIGNED NOT NULL DEFAULT 0,
    ip_origen VARCHAR(45) NULL,
    visible TINYINT(1) NOT NULL DEFAULT 1,
    es_oficial TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_foto_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    INDEX idx_foto_evento_visible (evento_id, visible, id),
    INDEX idx_foto_evento_created (evento_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla de Videos de la Celebración
CREATE TABLE IF NOT EXISTS videos_evento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    archivo_nombre VARCHAR(255) NOT NULL,
    url_recurso VARCHAR(500) NOT NULL,
    storage_provider ENUM('local', 's3', 'r2') NOT NULL DEFAULT 'local',
    tamano_bytes INT UNSIGNED NOT NULL DEFAULT 0,
    duracion_segundos INT UNSIGNED NOT NULL DEFAULT 0,
    orden INT UNSIGNED NOT NULL DEFAULT 1,
    visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_video_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    INDEX idx_video_evento (evento_id, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla de Playlist Sugerida
CREATE TABLE IF NOT EXISTS canciones_evento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    cancion VARCHAR(255) NOT NULL,
    artista VARCHAR(150) NULL,
    nombre_invitado VARCHAR(150) NULL,
    ip_origen VARCHAR(45) NULL,
    estado ENUM('pendiente', 'aprobada', 'reproducida', 'rechazada') NOT NULL DEFAULT 'aprobada',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cancion_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    INDEX idx_cancion_evento (evento_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla de Dedicatorias y Libro de Recuerdos
CREATE TABLE IF NOT EXISTS dedicatorias_recuerdos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    nombre_firmante VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    icono_emoji VARCHAR(20) NOT NULL DEFAULT '✨',
    color_tema VARCHAR(30) NOT NULL DEFAULT 'gold',
    likes INT UNSIGNED NOT NULL DEFAULT 0,
    media_adjunta_url VARCHAR(500) NULL,
    ip_origen VARCHAR(45) NULL,
    visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dedic_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    INDEX idx_dedic_evento (evento_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

#### 2.8. Tablas de Seguridad: `roles`, `usuarios` y `logs_auditoria`

```sql
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(80) NOT NULL,
    descripcion VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rol_id INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NULL,
    evento_id INT UNSIGNED NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_acceso DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usr_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT,
    CONSTRAINT fk_usr_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    CONSTRAINT fk_usr_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    INDEX idx_usr_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logs_auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    evento_id INT UNSIGNED NULL,
    accion VARCHAR(60) NOT NULL,
    detalle_json JSON NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_usr FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_log_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE SET NULL,
    INDEX idx_log_evento_fecha (evento_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
