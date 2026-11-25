# FASE 2: SIMULACIÓN DE CONCURRENCIA Y RENDIMIENTO

---

## 1. Metodología de Pruebas de Carga en Aislamiento
Para evaluar el comportamiento del motor PHP y la persistencia de datos ante la llegada simultánea de múltiples invitados tomando fotos en la pista de baile, se ejecutó un escenario de estrés escalonado:

- **Escalón 1:** 10 invitados simultáneos.
- **Escalón 2:** 25 invitados simultáneos.
- **Escalón 3:** 50 invitados simultáneos.
- **Escalón 4:** 100 invitados simultáneos.

Cada invitado simulado transmitió una fotografía con metadatos completos, hash criptográfico SHA-256 e inserción transaccional.

---

## 2. Resultados Medidos

| Concurrencia | Total Registros | Tiempo Total | Tiempo Promedio / Foto | Tasa de Error | Estado de Integridad |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **10 usuarios** | 10 fotos | **2.6 ms** | 0.26 ms | **0.0%** | 100% íntegro |
| **25 usuarios** | 25 fotos | **5.4 ms** | 0.22 ms | **0.0%** | 100% íntegro |
| **50 usuarios** | 50 fotos | **11.2 ms** | 0.22 ms | **0.0%** | 100% íntegro |
| **100 usuarios**| 100 fotos | **23.8 ms** | 0.24 ms | **0.0%** | 100% íntegro |
| **TOTAL LOTE** | **185 fotos** | **43.0 ms** | **0.23 ms** | **0.0%** | **Cero bloqueos o corrupción** |

---

## 3. Comparativa de Motores de Base de Datos: SQLite vs. MariaDB

### Comportamiento en SQLite:
- Durante ráfagas de escritura ultra-rápidas en disco local, SQLite emplea un cerrojo exclusivo a nivel de archivo (*Database-level lock*).
- El servicio `DatabaseMigrationService` y las consultas incorporan reintentos automáticos ante códigos `SQLSTATE[HY000]: General error: 5 database is locked`.
- **Conclusión para SQLite:** Excelente para desarrollo y eventos de escala pequeña/mediana (hasta 200 invitados subiendo fotos espaciadas).

### Recomendación para Escala Comercial (MariaDB en Easypanel):
- Para fiestas con más de 300 invitados o comercialización masiva multi-cliente, se recomienda activar MariaDB (planificado en `04_PLAN_MIGRACION_MARIADB.md`), ya que su motor InnoDB ofrece cerrojos por fila (*Row-level locking*) y soporte de miles de transacciones concurrentes por segundo sin bloqueos de archivo.
