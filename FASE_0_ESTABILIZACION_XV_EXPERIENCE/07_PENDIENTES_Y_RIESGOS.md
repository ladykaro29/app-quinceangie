# 07. REGISTRO DE PENDIENTES, RIESGOS RESIDUALES Y TRANSICIÓN A FASE 1

**Documento:** 07 de 07  
**Fecha:** 25 de noviembre de 2026  
**Rama:** `fase-0-seguridad-aislamiento`  
**Proyecto:** XV Experience

---

## 1. RESUMEN DE RIESGOS RESIDUALES TRAS LA FASE 0

Tras la implementación de los controles de seguridad y aislamiento, se evaluaron los riesgos operativos residuales:

| Riesgo Residual | Nivel | Causa Raíz | Mitigación Recomendada | Fase Asignada |
|---|---|---|---|---|
| **Concurrencia de Escritura en SQLite** | Medio | SQLite bloquea el archivo completo en escrituras simultáneas (`database is locked` detectado en pruebas rápidas). | Migración a MariaDB en Easypanel según el plan técnico documentado en `04_PLAN_MIGRACION_MARIADB.md`. | Fase 2 |
| **Fotografías Oficiales de Sophia Pendientes** | Medio-Alto | `/e/sophia-xv` tiene 0 fotos en su galería oficial y portada provisional generada digitalmente. | Solicitar paquete oficial de fotos de estudio a la familia Rivas Sánchez e ingresarlas a `galeria`. | Fase 1 |
| **Ubicación Física de Salón Nova Roma Incompleta** | Medio | Campo figura como *"pendiente de confirmación"*; botones de Google Maps y Waze no tienen destino GPS. | Obtener dirección exacta y coordenadas para parametrizar en `configuraciones_evento`. | Fase 1 |
| **Moderación Previa del Álbum Colaborativo** | Medio | Las fotos subidas por QR entran a la pantalla gigante sin aprobación humana intermedia. | Desarrollar el modo dual de moderación propuesto en `05_PROPUESTA_MODERACION_FOTOGRAFIAS.md`. | Fase 2 |
| **Tamaño Excesivo de Fotos Móviles (48 MP+)** | Medio | Fotos tomadas desde smartphones de gama alta pesan entre 15 y 25 MB, saturando el ancho de banda del salón. | Implementar compresión WebP en el navegador con HTML5 Canvas en `public/subir-fotos.php`. | Fase 2 |
| **Gestión Administrativa Manual de Nuevos Eventos** | Bajo-Medio | `admin/index.php` carece de un wizard visual para dar de alta un tercer evento; requiere insertar registros en BD. | Implementar CRUD de celebraciones y selector de tenant en sesión en `admin/eventos.php`. | Fase 3 |

---

## 2. ACTIVIDADES PENDIENTES PARA LA FASE 1 (COMPLETITUD DE SOPHIA)

La **Fase 1 (Días 4 a 7)** se enfocará exclusivamente en llevar la celebración de Sophia Valentina a un estándar de entrega impecable:

1. **Recepción y Carga de Fotos Oficiales:**
   - 1 fotografía de estudio principal para la portada oficial (`/assets/img/sophia-portada.webp`).
   - 1 fotografía para el avatar de despedida (`/assets/img/sophia-xv.webp`).
   - 8 a 15 fotografías de alta calidad para la galería fotográfica interactiva (`seccion = 'estudio'`).
2. **Parametrización de Ubicación:**
   - Dirección física completa (Calle, sector, referencia).
   - Coordenadas de latitud y longitud (`coordenadas_gps`) para enlazar directamente con Google Maps y Waze.
3. **Optimización de Audio Oficial:**
   - Convertir y normalizar la canción oficial (*Princesa Sofía*) en formatos AAC/M4A (128 kbps) y MP3 para arranque instantáneo sin retrasos en redes móviles.
4. **Validación con la Familia Rivas Sánchez:**
   - Envío del enlace privado `/e/sophia-xv` para revisión y visto bueno formal de los padres (Ramón Eduardo y Estefany Yamileth).

---

## 3. CHECKLIST PARA LA APROBACIÓN DE FASE 0 Y FUSIÓN A MAIN

Para que el liderazgo técnico autorice la fusión (`git merge`) de la rama `fase-0-seguridad-aislamiento` a la rama `main` y su posterior despliegue en Easypanel, se debe ratificar el cumplimiento de los siguientes 5 criterios:

- [x] **Cero fugas de datos en APIs:** Invitados, check-in, videos y canciones operan con aislamiento comprobado.
- [x] **Pases VIP validados por celebración:** Un pase de Angie es rechazado en Sophia (403) y viceversa.
- [x] **Videos aislados:** Sophia recibe colección vacía `[]` y no los 19 videos de Angie.
- [x] **100% de Pruebas Aprobadas:** 32 de 32 pruebas aprobadas en el runner automatizado.
- [x] **Datos reales preservados:** Base de datos original respaldada y sin alteraciones destructivas.
