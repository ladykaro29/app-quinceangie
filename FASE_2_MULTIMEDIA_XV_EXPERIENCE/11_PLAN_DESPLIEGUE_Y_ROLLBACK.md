# FASE 2: PLAN DE DESPLIEGUE Y PROCEDIMIENTO DE ROLLBACK

---

## 1. Plan de Despliegue hacia Producción

Siguiendo las restricciones generales del proyecto (*"No desplegar automáticamente sin aprobación"*):

### Pasos de Despliegue:
1. **Verificación Local Completa:**
   - Rama actual: `fase-2-multimedia`.
   - 40 de 40 pruebas unitarias y de integración aprobadas (`php tests/suite_runner.php`).
   - Todos los cambios documentados e inventariados.
2. **Fusión en Rama Principal (`main`):**
   - Fusión sin avance rápido (`--no-ff`) para mantener trazabilidad histórica:
     ```bash
     git checkout main
     git merge fase-2-multimedia --no-ff -m "merge: Integracion de Fase 2 Sistema Multimedia Profesional en main"
     ```
3. **Publicación Remota (Requiere Aprobación Explicita del Usuario):**
   - Envío al repositorio oficial:
     ```bash
     git push origin main
     ```
4. **Auto-Migración en Easypanel:**
   - Al recibir el webhook, Easypanel recompila el contenedor.
   - En la primera petición HTTP entrante, `DatabaseMigrationService` agrega las columnas y los índices a `fotos_fiesta` sin requerir acceso SSH ni alterar la base de datos de Angie.

---

## 2. Procedimiento de Rollback (Plan de Contingencia)

En caso de detectarse cualquier anomalía en el entorno de producción durante o después del despliegue:

### A. Reversión de Código en Git:
Si se requiere volver de inmediato al estado anterior (Fase 1 estable):
```bash
# Identificar commit previo a la fusion
git checkout main
git revert -m 1 HEAD --no-edit
git push origin main
```
Easypanel descargará la versión previa y restaurará el código en menos de 60 segundos.

### B. Compatibilidad de Esquema Aditivo (Zero Data Loss):
Dado que todas las migraciones de la Fase 2 fueron estrictamente **aditivas** (únicamente se agregaron columnas opcionales con valores predeterminados seguros), una reversión del código a la Fase 1 o Fase 0 funcionará perfectamente sobre la base de datos existente sin necesidad de modificar ni destruir tablas.
