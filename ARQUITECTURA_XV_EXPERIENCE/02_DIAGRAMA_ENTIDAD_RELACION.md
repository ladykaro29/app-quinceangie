# ARQUITECTURA DE SOFTWARE — PLATAFORMA "XV EXPERIENCE"
## DOCUMENTO 02: DIAGRAMA ENTIDAD-RELACIÓN MULTI-CELEBRACIÓN

---

### 1. DIAGRAMA GLOBAL ENTIDAD-RELACIÓN (MERMAID)

A continuación se presenta el modelo conceptual y relacional completo para soportar la plataforma comercial multi-celebración **XV Experience**:

```mermaid
erDiagram
    %% =========================================================
    %% GESTIÓN COMERCIAL Y CLIENTES
    %% =========================================================
    clientes ||--o{ pedidos : "realiza (1:N)"
    paquetes ||--o{ pedidos : "define nivel (1:N)"
    pedidos ||--o{ pagos : "registra (1:N)"
    pedidos ||--|| eventos : "da origen a (1:1)"

    %% =========================================================
    %% USUARIOS, ROLES Y ACCESOS
    %% =========================================================
    roles ||--o{ usuarios : "clasifica (1:N)"
    clientes ||--o{ usuarios : "asocia cuenta (1:1 o 1:N)"
    usuarios ||--o{ logs_auditoria : "ejecuta (1:N)"
    eventos ||--o{ usuarios : "asigna anfitrión/staff (1:N)"

    %% =========================================================
    %% EVENTO Y SU CONFIGURACIÓN
    %% =========================================================
    plantillas ||--o{ eventos : "utiliza diseño (1:N)"
    eventos ||--|| configuraciones_evento : "posee personalización (1:1)"

    %% =========================================================
    %% MÓDULOS OPERATIVOS DEL EVENTO (AISLADOS POR evento_id)
    %% =========================================================
    eventos ||--o{ invitados : "incluye titulares (1:N)"
    invitados ||--o{ acompanantes : "registra acompañantes (1:N)"
    
    eventos ||--o{ fotografias_evento : "contiene fotos (1:N)"
    eventos ||--o{ videos_evento : "contiene videos (1:N)"
    eventos ||--o{ canciones_evento : "recibe sugerencias (1:N)"
    eventos ||--o{ dedicatorias_recuerdos : "reúne firmas/deseos (1:N)"

    %% =========================================================
    %% DEFINICIÓN DE ESTRUCTURAS
    %% =========================================================
    clientes {
        int id PK
        string nombre_contacto
        string email UK
        string telefono
        string documento_identidad
        string pais
        string ciudad
        datetime created_at
    }

    paquetes {
        int id PK
        string codigo UK
        string nombre
        decimal precio
        string moneda
        int max_invitados
        int max_fotos
        int max_videos
        boolean incluye_libro_3d
        boolean incluye_proyector_envivo
        boolean incluye_rifa_sorteo
        boolean incluye_checkin_qr
        boolean activo
    }

    pedidos {
        int id PK
        int cliente_id FK
        int paquete_id FK
        string codigo_pedido UK
        string estado "pendiente, pagado, en_diseno, aprobado, cancelado"
        decimal total
        datetime fecha_expiracion
        datetime created_at
    }

    pagos {
        int id PK
        int pedido_id FK
        string referencia_transaccion UK
        string pasarela "stripe, paypal, mercadopago, transferencia"
        decimal monto
        string moneda
        string estado "pendiente, completado, fallido, reembolsado"
        datetime fecha_pago
    }

    roles {
        int id PK
        string codigo UK "superadmin, anfitrion, recepcionista"
        string nombre
        string descripcion
    }

    usuarios {
        int id PK
        int rol_id FK
        int cliente_id FK "nullable"
        int evento_id FK "nullable (para anfitriones)"
        string email UK
        string password_hash
        string nombre_completo
        boolean activo
        datetime ultimo_acceso
        datetime created_at
    }

    plantillas {
        int id PK
        string codigo UK "esmeralda-real, rose-gold-princess, celestial-blue"
        string nombre
        string version
        string ruta_vistas
        string preview_thumbnail
        boolean activo
    }

    eventos {
        int id PK
        int pedido_id FK
        int plantilla_id FK
        string slug UK
        string subdominio UK "nullable"
        string tipo_evento "xv_anos, boda, graduacion, aniversario"
        string nombre_festejado
        string titulo_evento
        datetime fecha_evento
        datetime fecha_nacimiento
        string timezone
        string estado "borrador, publicado, pausado, finalizado"
        string contrasena_acceso_privado "nullable"
        datetime created_at
    }

    configuraciones_evento {
        int id PK
        int evento_id FK UK
        string nombre_padre
        string nombre_madre
        string nombre_padrinos
        string direccion_salon
        string nombre_salon
        string url_google_maps
        string dress_code_titulo
        string dress_code_descripcion
        string dress_code_colores_json
        string lluvia_sobres_texto
        string buzon_instrucciones
        string audio_musica_url
        string foto_portada_url
        string foto_despedida_url
        json itinerario_json
        json trivia_preguntas_json
        json tokens_estilo_css_json
        json modulos_habilitados_json
    }

    invitados {
        int id PK
        int evento_id FK
        string nombre_completo
        int asistira "1=si, 0=no"
        string codigo_rifa UK "aislado por evento"
        int asistio_evento "1=presente, 0=pendiente"
        datetime checkin_at
        string hash_pase_vip
        string telefono "nullable"
        string email "nullable"
        string notas "nullable"
        datetime created_at
    }

    acompanantes {
        int id PK
        int evento_id FK
        int invitado_id FK
        string nombre_completo
        string codigo_rifa UK
        int asistio_evento
        datetime checkin_at
    }

    fotografias_evento {
        int id PK
        int evento_id FK
        string nombre_autor
        string mensaje
        string archivo_nombre
        string url_recurso
        string storage_provider "local, s3, r2"
        int tamano_bytes
        int likes
        string ip_origen
        int visible "1=si, 0=oculto"
        boolean es_oficial "1=album base, 0=invitados"
        datetime created_at
    }

    videos_evento {
        int id PK
        int evento_id FK
        string titulo
        string descripcion
        string archivo_nombre
        string url_recurso
        string storage_provider
        int tamano_bytes
        int duracion_segundos
        int orden
        int visible
        datetime created_at
    }

    canciones_evento {
        int id PK
        int evento_id FK
        string cancion
        string artista
        string nombre_invitado
        string ip_origen
        string estado "pendiente, aprobada, reproducida, rechazada"
        datetime created_at
    }

    dedicatorias_recuerdos {
        int id PK
        int evento_id FK
        string nombre_firmante
        string mensaje
        string icono_emoji
        string color_tema
        int likes
        string media_adjunta_url "nullable"
        string ip_origen
        int visible
        datetime created_at
    }

    logs_auditoria {
        int id PK
        int usuario_id FK "nullable"
        int evento_id FK "nullable"
        string accion "login, checkin, delete_guest, export_csv, spin_raffle"
        string detalle_json
        string ip
        string user_agent
        datetime created_at
    }
```

---

### 2. MATRIZ DE CARDINALIDADES Y REGLAS DE AISLAMIENTO

| Relación Padre -> Hijo | Cardinalidad | Comportamiento Referencial (`ON DELETE`) | Justificación de Integridad |
| :--- | :---: | :---: | :--- |
| `clientes` -> `pedidos` | `1 : N` | `RESTRICT` | Los registros contables y pedidos no pueden eliminarse si existe un cliente registrado. |
| `pedidos` -> `eventos` | `1 : 1` | `RESTRICT` | Un pedido da origen a una celebración única garantizando el pago de la suscripción. |
| `eventos` -> `configuraciones_evento` | `1 : 1` | `CASCADE` | Si se elimina el evento, sus textos y tokens específicos se eliminan simultáneamente. |
| `eventos` -> `invitados` | `1 : N` | `CASCADE` | Todos los invitados pertenecen estrictamente a una celebración. |
| `invitados` -> `acompanantes` | `1 : N` | `CASCADE` | Si un invitado titular cancela o es eliminado, sus acompañantes son removidos. |
| `eventos` -> `fotografias_evento` | `1 : N` | `CASCADE` | Galería multimedia vinculada de forma exclusiva a la celebración. |
| `eventos` -> `videos_evento` | `1 : N` | `CASCADE` | Videos del evento asociados estrictamente al `evento_id`. |
| `eventos` -> `canciones_evento` | `1 : N` | `CASCADE` | Sugerencias musicales de la fiesta aisladas por celebración. |
| `eventos` -> `dedicatorias_recuerdos`| `1 : N` | `CASCADE` | Firmas y dedicatorias del Libro 3D pertenecientes al evento. |

> [!IMPORTANT]
> **Aislamiento Multi-Tenant (Mecanismo de Llave Compuesta e Índices de Partición):**
> Todas las tablas operativas de la fiesta incorporan `evento_id` como primer elemento en sus índices compuestos principales (`INDEX idx_evento_asistira (evento_id, asistira)`, `INDEX idx_evento_visible (evento_id, visible)`). Esto garantiza que ninguna consulta cruce datos entre clientes distintos y maximiza la velocidad de ejecución en el motor MySQL/MariaDB.
