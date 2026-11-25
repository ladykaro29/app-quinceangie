# 06. PROCEDIMIENTO DE REVERSIÓN Y PLAN DE ROLLBACK — FASE 0

**Documento:** 06 de 07  
**Fecha:** 25 de noviembre de 2026  
**Rama de Trabajo:** `fase-0-seguridad-aislamiento`  
**Commit de Referencia (Base Estable):** `4a8111c`  
**Proyecto:** XV Experience

---

## 1. OBJETIVO DEL PROCEDIMIENTO

Garantizar que el equipo de Kick-Off Development pueda revertir cualquier cambio introducido durante la **Fase 0** en menos de **2 minutos**, restaurando el sistema a su estado exacto previo a esta intervención sin pérdida de información ni inconsistencias en la base de datos.

---

## 2. PUNTOS DE RESTAURACIÓN Y RESPALDOS CREADOS

Antes de iniciar la edición de cualquier archivo de código o esquema, se generaron los siguientes respaldos inmutables en el entorno local:

1. **Copia Fría de Base de Datos SQLite:**
   - Ruta de origen: `database/invitacion_xv.sqlite`
   - Ruta de respaldo verificado: `backups/pre_fase_0/invitacion_xv.sqlite.bak`
   - Contenido respaldado: 14 tablas intactas, eventos 1 (Angie) y 2 (Sophia), 160 fotografías de galería, 6 mensajes de recuerdos y configuraciones completas.
2. **Aislamiento en Rama Git Dedicada:**
   - Rama base: `main` (commit `4a8111c`)
   - Rama de trabajo: `fase-0-seguridad-aislamiento`
   - Estado de la rama `main`: **100% limpia y sin modificaciones.**

---

## 3. PASOS DE REVERSIÓN INMEDIATA (EN CASO DE CONTINGENCIA)

Si el liderazgo decide no aprobar los cambios de la Fase 0 o si se detecta cualquier anomalía durante la revisión:

### Paso 1: Volver a la rama principal en Git
Ejecutar en la terminal de la raíz del proyecto:
```powershell
git checkout main
```
Esto descarta inmediatamente las modificaciones de código en `api/invitados.php`, `api/checkin.php`, `api/libro.php`, `api/canciones.php`, `api/video.php`, `public/index.php`, `public/libro-recuerdos.php` y `app/Middleware/TenantResolverMiddleware.php`, regresando al commit oficial `4a8111c`.

### Paso 2: Restaurar la base de datos SQLite original
```powershell
Copy-Item -Path "backups\pre_fase_0\invitacion_xv.sqlite.bak" -Destination "database\invitacion_xv.sqlite" -Force
```

### Paso 3: Validación post-rollback
Ejecutar la verificación rápida de conectividad:
```powershell
php -r "require 'config/database.php'; \$db = getDBConnection(); echo 'Conectado a SQLite OK. Eventos: ' . \$db->query('SELECT count(*) FROM eventos')->fetchColumn() . PHP_EOL;"
```
Salida esperada: `Conectado a SQLite OK. Eventos: 2`.

---

## 4. IMPACTO DEL ROLLBACK EN PRODUCCIÓN (EASYPANEL)

Dado que se cumplió estrictamente la restricción:
> *"No hacer push ni desplegar automáticamente. Entregar primero el informe de resultados para revisión."*

**El entorno de producción en Easypanel no requiere rollback alguno**, ya que la imagen Docker activa en el servidor continúa corriendo sobre el commit `4a8111c` de la rama `main` sin haber sido alterada.
