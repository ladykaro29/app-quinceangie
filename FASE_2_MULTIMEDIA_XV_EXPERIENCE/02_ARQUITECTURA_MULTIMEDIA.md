# FASE 2: ARQUITECTURA MULTIMEDIA MULTI-CELEBRACIÓN

---

## 1. Principios de Diseño
El subsistema multimedia fue rediseñado bajo los siguientes axiomas arquitectónicos:

1. **Aislamiento por Tenant (`evento_id`):** Ninguna fotografía, miniatura o archivo subido pertenece a un espacio global sin asignar; cada registro y ruta física se asocia estrictamente al `evento_id` correspondiente.
2. **Abstracción Desacoplada (`App\Services\StorageService`):** El código de los controladores y APIs no interactúa con llamadas crudas al sistema de archivos (`move_uploaded_file`, `unlink`, `copy`). Toda la lógica pasa por un servicio centralizado que gestiona rutas, miniaturas, cuotas y permite cambiar fácilmente a almacenamiento en la nube (S3 / Cloudflare R2) en el futuro.
3. **Retrocompatibilidad Total:** Las 160 fotografías preexistentes de Angie en `public/assets/fotos/` y los recuerdos en `database/uploads/fotos/` se resuelven de forma transparente sin quebrar URLs antiguas.

---

## 2. Diagrama de Flujo de Procesamiento Multimedia

```mermaid
flowchart TD
    A["Dispositivo Móvil del Invitado"] -->|"Selecciona 1 o más fotos (8-25 MB)"| B["Canvas / WebP Compressor en Navegador"]
    B -->|"Redimensión máx. 2048px + Calidad 0.82"| C["Foto Optimizada (350-700 KB)"]
    C -->|"Genera UUID + Hash SHA-256"| D["Cola de Envío (Concurrencia: 2)"]
    D -->|"POST multipart/form-data"| E["Endpoint API /api/fotos.php"]
    
    subgraph Servidor Backend
        E --> F{"Validación Seguridad"}
        F -->|"MIME real finfo + getimagesize"| G{"¿Pasa Filtros?"}
        G -->|No| H["HTTP 400/415 Error"]
        G -->|Sí| I{"¿UUID o SHA-256 Duplicado?"}
        I -->|Sí| J["HTTP 200 (Devuelve existente sin duplicar)"]
        I -->|No| K["StorageService::storeGuestPhoto()"]
        
        K --> L["Guarda en storage/media/{evento_id}/fotos/"]
        K --> M["Genera Miniatura en storage/media/{evento_id}/thumbs/"]
        K --> N["Sincroniza en database/uploads/fotos/"]
        
        K --> O{"¿Moderación Activa en Evento?"}
        O -->|Sí (Sophia)| P["Estado: Pendiente (aprobada=0, visible=0)"]
        O -->|No (Angie)| Q["Estado: Aprobada (aprobada=1, visible=1)"]
        
        P --> R["Base de Datos fotos_fiesta"]
        Q --> R
    end
    
    subgraph Consumidores
        R --> S["Admin Moderación: admin/moderacion-fotos.php"]
        S -->|"Aprobar individual / lote"| T["Actualiza: aprobada=1, visible=1"]
        T --> U["Pantalla Proyector en Vivo: public/en-vivo.php"]
        R -->|"Fotos Aprobadas"| U
    end
```

---

## 3. Jerarquía de Almacenamiento Particionado

```
storage/media/
├── 1/                     # Celebración Angie Karolina
│   ├── covers/            # Portadas específicas
│   ├── fotos/             # Fotos subidas por invitados de Angie
│   ├── thumbs/            # Miniaturas optimizadas (máx. 400px)
│   ├── audio/             # Música de celebración
│   └── videos/            # Recuerdos en video
└── 2/                     # Celebración Sophia Valentina
    ├── covers/            # sophia_portada.webp, sophia_xv.webp
    ├── fotos/             # Fotos colaborativas de Sophia
    ├── thumbs/            # Miniaturas ligeras para panel y móvil
    ├── audio/             # Princesa Sofía y temas oficiales
    └── videos/            # Videos exclusivos de Sophia
```

Esta separación garantiza que las operaciones de respaldo, limpieza o migración hacia buckets S3/R2 se puedan ejecutar por evento de forma completamente independiente.
