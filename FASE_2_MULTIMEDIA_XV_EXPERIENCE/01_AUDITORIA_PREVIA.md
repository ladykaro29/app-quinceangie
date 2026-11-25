# FASE 2: AUDITORÍA PREVIA Y LÍNEA BASE

**Objetivo:** Verificar el estado de la plataforma, integridad de almacenamiento y riesgos antes de las intervenciones de la Fase 2.

---

## 1. Verificación de Entorno y Git
- **Rama Base:** `main` (commit merge `d8e7b0e`).
- **Rama de Trabajo Independiente:** `fase-2-multimedia` creada y aislada.
- **Estado de Pruebas Previas:** 32 pruebas aprobadas sobre 32 ejecutadas (100.0%).
- **Base de Datos Activa:**
  - Desarrollo local y suite de pruebas: SQLite (`database/invitacion_xv.sqlite`).
  - Producción en Easypanel: Conector preparado para SQLite persistente y MySQL/MariaDB mediante variables de entorno (`DB_CONNECTION=mysql`, `DB_HOST`, `DB_USER`, `DB_PASS`).
  - Servicio de migración automática: `DatabaseMigrationService::ensureMigrated()` operativo y auto-reparador.

---

## 2. Inventario de Almacenamiento Multimedia Previo
Se realizó una inspección completa del árbol de directorios para detectar archivos y duplicidades:

```
storage/
└── media/
    └── 2/  (Sophia Valentina)
        ├── audio/
        ├── covers/
        │   ├── sophia_portada.jpg   (913 KB)
        │   ├── sophia_portada.webp  (160 KB)
        │   ├── sophia_xv.jpg        (770 KB)
        │   └── sophia_xv.webp       (144 KB)
        └── fotos/

database/
└── uploads/
    └── fotos/ (Almacén plano legacy donde se recibían fotos de Angie y pruebas)

public/
├── assets/
│   └── fotos/ (160 fotografías históricas oficiales de Angie Karolina: angie_xv_recuerdo_001.jpg a 160)
├── sophia-portada.webp
├── sophia-xv.webp
├── portada.jpg
└── angie-xv.jpg
```

### Hallazgos de Almacenamiento:
1. **Dispersión de Recursos:** Las fotos de Angie residían en `public/assets/fotos/`, mientras que las subidas de invitados caían en `database/uploads/fotos/`.
2. **Duplicidad Controlada de Portadas:** Sophia tenía sus portadas tanto en `public/` (para acceso estático directo de alta velocidad vía Nginx/Apache) como en `storage/media/2/covers/` (para archivo persistente). Se mantiene esta estructura para no quebrar enlaces en clientes móviles con caché.
3. **Preservación Estricta:** Las 160 fotografías oficiales de Angie Karolina y sus 19 recuerdos audiovisuales se encuentran 100% intactos y protegidos contra cualquier operación de sobreescritura.

---

## 3. Vulnerabilidades y Riesgos Identificados en Fase 1
- **Riesgo 1 (Saturación de Red Celular):** Si 50 invitados subían fotos directas de 15 MB tomadas con iPhone/Samsung, la red Wi-Fi o datos móviles del salón de fiestas colapsaba, arrojando errores de timeout.
- **Riesgo 2 (Inyección de Payloads en Imágenes):** Endpoints anteriores confiaban en `$_FILES['foto']['type']`, lo que permitía enviar archivos PHP camuflados con extensión `.jpg`.
- **Riesgo 3 (Exposición de Fotos Inapropiadas):** Sin cola de moderación, cualquier imagen enviada aparecía instantáneamente en el proyector de la fiesta.
- **Riesgo 4 (Duplicación por Reintentos):** Si un invitado perdía señal a mitad del envío y presionaba repetidamente "Enviar", se registraban 3 o 4 copias idénticas en la base de datos.

Todos estos riesgos fueron neutralizados en la arquitectura de Fase 2.
