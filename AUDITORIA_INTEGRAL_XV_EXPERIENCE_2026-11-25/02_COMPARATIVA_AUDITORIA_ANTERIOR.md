# 02 — COMPARATIVA CON LA AUDITORÍA INICIAL
**Plataforma:** XV Experience  
**Fecha:** 25 de Noviembre de 2026  
**Especialidad:** Arquitectura de Software y QA

---

## 1. CUADRO EVOLUTIVO: ESTADO INICIAL VS ESTADO ACTUAL

| Aspecto | Estado en Auditoría Inicial (InvitacionAngie) | Estado Actual (XV Experience v1.0) | Diferencia / Progreso Técnico |
| :--- | :--- | :--- | :---: |
| **Capacidad Multi-Evento** | **Inexistente.** Todo el código estaba acoplado a Angie Karolina (3 de Octubre de 2026). | **Operativo.** Resuelve dinámicamente `/e/angie-xv` y `/e/sophia-xv` sin colisión. | **ALTO AVANCE (85%)** |
| **Identidad Visual Sophia** | No existía. La ruta `/e/sophia-xv` mostraba marco roto y fondo verde de Angie. | **Implementada.** Paleta ciruela/rosa/champagne, retrato de alta resolución y tokens CSS dinámicos. | **COMPLETADO (100%)** |
| **Enrutamiento** | Peticiones directas a scripts en raíz (`public/index.php`, `libro-recuerdos.php`). | Front Controller centralizado ([index.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/index.php)) y clase [Router.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/app/Core/Router.php). | **COMPLETADO (100%)** |
| **Modelo de Base de Datos** | Tablas planas sin discriminador: `invitados`, `fotos_fiesta`, `libro_recuerdos`. | Esquema relacional extendido con `eventos`, `configuraciones_evento`, `clientes`, `usuarios`, `roles`. | **COMPLETADO (90%)** |
| **Despliegue Easypanel** | Fallaba con error fatal `no such table: eventos` por montaje de volúmenes Docker antiguos. | **Resuelto.** Auto-reparación en caliente implementada en [DatabaseMigrationService.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/app/Core/DatabaseMigrationService.php). | **COMPLETADO (100%)** |
| **Confirmaciones RSVP** | Boletos con prefijo estático `XV-ANGIE-...`. | Boletos con prefijo dinámico según el slug (`XV-SOPHIA-...` vs `XV-ANGIE-...`). | **COMPLETADO (100%)** |
| **Galería Fotográfica** | 160 fotos de Angie se mezclaban en todas las consultas. | Filtrado estricto por `evento_id`. Las fotos oficiales de Angie solo cargan en `evento_id = 1`. | **COMPLETADO (100%)** |
| **Libro de Recuerdos** | Código único sin soporte de contexto de evento. | Desacoplado parcialmente. Mensajes aislados por `evento_id`. | **PARCIAL (75%)** |
| **Migración a Laravel** | Planificada en documentos de arquitectura. | **No iniciada en código.** Se mantiene en PHP vanilla híbrido. | **PENDIENTE (0%)** |

---

## 2. ANÁLISIS DE MODIFICACIONES REALIZADAS

### 2.1 Qué se incorporó
1. **Middleware de Inquilinos ([TenantResolverMiddleware.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/app/Middleware/TenantResolverMiddleware.php)):** Intercepta la petición HTTP, busca el slug en la tabla `eventos`, carga todas las configuraciones asociadas y las almacena en la clase estática `CurrentEventContext`.
2. **Servicio de Auto-Migración ([DatabaseMigrationService.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/app/Core/DatabaseMigrationService.php)):** Auto-instala el esquema multi-evento si detecta que la base de datos montada no contiene la tabla `eventos`, garantizando cero caídas en despliegues con volúmenes persistentes.
3. **Inyección Dinámica de Tokens CSS ([public/index.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/index.php#L35-L65)):** Inyecta variables CSS `:root` con prioridad `!important` para sobrescribir los estilos esmeralda cuando el evento activo es Sophia Valentina.
4. **Suite de Pruebas Automatizadas ([tests/suite_runner.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/tests/suite_runner.php)):** Batería de 24 pruebas de integración que valida el aislamiento de datos, APIs y flujo móvil.

### 2.2 Qué se conservó intacto
1. **La Celebración de Angie Karolina:** Todo el contenido, paleta de colores, dedicatorias y fotos de Angie se preservaron al 100%. Ningún dato original fue sobreescrito ni eliminado.
2. **El Grimorio 3D de Recuerdos:** La animación interactiva de paso de páginas con sonido de pergamino continúa operativa en [public/libro-recuerdos.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/libro-recuerdos.php).
3. **La Pantalla de Proyección:** [public/en-vivo.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/en-vivo.php) mantiene su diseño de gala con actualización en tiempo real mediante polling.

### 2.3 Qué quedó incompleto o como solución temporal
1. **Monolito de Vistas:** Se crearon los archivos desacoplados en `templates/components/cards/`, pero [public/index.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/index.php) todavía no los incluye; sigue conteniendo las 13 tarjetas en su cuerpo monolítico.
2. **Textos y Videos Residuales en `api/libro.php`:** El endpoint de dedicatorias conserva el mensaje fijo `"¡Dedicatoria guardada en el libro de recuerdos de Angie con éxito!"` y devuelve los videos de Angie a Sophia.
3. **Regex Hardcoded en `api/checkin.php`:** El escáner solo reconoce códigos con prefijo `XVANGIE-`.
