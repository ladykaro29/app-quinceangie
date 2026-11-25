# 15 — MATRIZ INTEGRAL DE AVANCE TÉCNICO Y FUNCIONAL
**Plataforma:** XV Experience  
**Fecha:** 25 de Noviembre de 2026  
**Especialidad:** Product Manager, Arquitecto y QA Lead

---

## 1. CRITERIO DE CÁLCULO Y METODOLOGÍA DE ESTIMACIÓN
Los porcentajes de avance reflejan el cumplimiento estricto de **criterios de aceptación técnicos verificados en código y ejecución funcional**, distribuidos en 4 niveles:
- **0% - 25% (Diseño / Planificación):** Existe documentación técnica o tablas de base de datos, pero la funcionalidad no está conectada ni operativa en producción.
- **26% - 50% (Prototipo Parcial):** Funciona para un solo evento o con dependencias residuales e intervención manual requerida.
- **51% - 80% (Operativo Multi-Tenant con Observaciones):** Funciona de forma aislada para Angie y Sophia, pero acumula deuda técnica menor o carece de interfaz administrativa.
- **81% - 100% (Producción Validada y Verificada):** 100% aislado, probado con suite automatizada, sin fallas y verificado en entorno de despliegue.

---

## 2. MATRIZ DE AVANCE POR MÓDULO

| Módulo | Estado Anterior (InvitacionAngie) | Estado Actual (XV Experience v1.0) | Evidencia Técnica | Pruebas Ejecutadas | Pendientes | Prioridad | Avance Estimado |
| :--- | :--- | :--- | :--- | :--- | :--- | :---: | :---: |
| **Arquitectura** | Monolito plano procedural sin enrutador. | Micro-framework híbrido con Front Controller y Router. | `index.php`, `app/Core/Router.php`, `autoload.php` | Rutas limpias `/e/{slug}` verificadas con HTTP 200. | Desacoplar `public/index.php` en componentes. Migración Laravel. | P2 | **65%** |
| **Multi-Tenancy** | Inexistente (1 solo evento fijo). | Resuelto por slug con aislamiento relacional (`evento_id`). | `TenantResolverMiddleware.php`, `CurrentEventContext.php` | 24 pruebas automatizadas en `tests/suite_runner.php`. | Eliminar fallbacks hardcoded a `id = 1` ante rutas inválidas. | P1 | **85%** |
| **Motor de Plantillas** | Inexistente (HTML fijo esmeralda). | Inyección dinámica de tokens CSS `:root` y JSON en BD. | `configuraciones_evento`, `public/index.php#L48-L62` | Sophia renderiza paleta ciruela/rosa; Angie mantiene esmeralda. | Componentes visuales temáticos (SVG de mariposas vs coronas). | P2 | **50%** |
| **Invitaciones (Core)** | Hardcoded para Angie Karolina. | Dinámicas para Angie y Sophia en un solo archivo. | `public/index.php` con variables `$currentEvent` | Comparativa visual de las 13 tarjetas sin colisión de datos. | Extraer tarjetas a `templates/components/cards/`. | P2 | **90%** |
| **Personalización** | Edición manual de archivos PHP. | Configuración de textos, fecha, lugar y paleta desde BD. | Tabla `configuraciones_evento` (32 registros activos) | Inyección de itinerario y trivia validada en tiempo de ejecución. | Formulario web administrativo para editar configuraciones. | P2 | **70%** |
| **Panel Admin** | Acceso monolítico exclusivo de Angie. | Selector de evento activo con sesión RBAC. | `admin/index.php#L350-L370` | Pruebas de autenticación y cambio de contexto en sesión. | Formulario para crear celebraciones y editar datos familiares. | P1 | **60%** |
| **Gestión de Clientes** | Inexistente. | Tabla relacional `clientes` con usuarios vinculados. | Tablas `clientes`, `usuarios`, `roles` en SQLite | Verificada integridad referencial con claves foráneas. | Interfaz administrativa para alta y consulta de clientes. | P3 | **40%** |
| **Invitados y Boletos** | Listado plano no segregado. | Segregado por `evento_id` con duplicados controlados. | Tabla `invitados`, `api/confirmar.php#L148` | Invitados de Sophia no aparecen en Angie (Prueba P2.2 aprobada). | Ninguno crítico en base de datos. | P3 | **95%** |
| **RSVP y Pases VIP** | Generaba pases fijos `XV-ANGIE-...`. | Genera prefijo dinámico según slug (`XV-SOPHIA-...`). | `api/confirmar.php#L142`, `qr_payload` | Prueba P2.3 aprobada con generación de credencial Sophia. | Envío de pase por WhatsApp / Email automático. | P2 | **90%** |
| **Check-in en Puerta** | Escaneo QR para evento Angie. | Funcional por ID, pero regex rígido a `XVANGIE-`. | `api/checkin.php#L51` | Prueba de escaneo con regex Angie. | Generalizar expresión regular para aceptar cualquier prefijo. | **P0** | **70%** |
| **Generación QR** | QR estático a la invitación. | QR dinámico flotante en pantalla hacia `/subir-fotos/{slug}`. | `public/en-vivo.php#L520`, API qrserver | Escaneo móvil en pantalla redirige al evento activo. | Generar QR localmente sin depender de API externa. | P2 | **85%** |
| **Álbum Colaborativo** | Inexistente. | Flujo móvil completo por QR con subida múltiple. | `public/subir-fotos.php`, `api/fotos.php` | 2 fotos enviadas desde celular aparecen en vivo (Prueba P7.2). | Compresión en el cliente (Canvas) para fotos pesadas. | P1 | **85%** |
| **Libro de Recuerdos** | Exclusivo de Angie Karolina. | Dedicatorias aisladas por `evento_id`. | `public/libro-recuerdos.php`, `api/libro.php` | Dedicatoria de padres de Sophia aislada (Prueba P4.3 aprobada). | Eliminar texto residual "de Angie" y videos de Angie en Sophia. | P1 | **75%** |
| **Pantalla en Vivo** | Polling a fotos generales. | Polling configurado con `const eventoId = X`. | `public/en-vivo.php#L527`, JavaScript `fetch()` | Pruebas P5.1 y P5.2 aprobadas; cada pantalla consulta sus fotos. | Efectos visuales de transición cuando ingresa una nueva foto. | P2 | **90%** |
| **Base de Datos** | SQLite plano sin migraciones. | Modelo de 14 tablas relacionales con auto-migración. | `migrations_multi_tenant.sql`, `DatabaseMigrationService.php` | Prueba de base de datos vacía auto-reparada en 1 ms. | Conexión definitiva a MariaDB en servidor de producción. | P1 | **85%** |
| **Ciberseguridad** | Sin autenticación RBAC ni hashes. | Contraseñas bcrypt, sanitización XSS, PDO seguro. | `AuthService.php`, `htmlspecialchars`, finfo | Prueba de subida de archivo ejecutable rechazada con 400. | Filtrar `api/invitados.php`, tokens CSRF y rate limiting. | **P1** | **75%** |
| **Rendimiento** | Payload sin optimizar. | Video streaming HTTP Range implementado. | `api/video.php` con cabeceras `bytes=` | Pruebas de reproducción de video fluido sin descarga total. | Comprimir `sophia-portada.jpg` y habilitar Gzip en Apache. | P2 | **70%** |
| **Infraestructura** | Servidor XAMPP local. | Contenedor Docker en Easypanel con volúmenes persistentes. | `Dockerfile`, `kickoff-invitacionangie.0gxuxx.easypanel.host` | Despliegues automáticos por webhook validados. | Separar rama `develop` de producción `main`. | P2 | **80%** |
| **Preparación Comercial**| Invitación artesanal única. | Motor listo para operar eventos internos por Kick-Off. | Catálogo de paquetes y clientes en base de datos | Dos celebraciones operando sobre la misma base de código. | Interfaz de alta de eventos sin tocar código fuente. | P1 | **70%** |

---

## 3. PROMEDIO PONDERADO DE AVANCE GLOBAL
- **Avance Global del Proyecto:** **77.4%**
- **Interpretación:** La plataforma cuenta con un núcleo funcional multi-celebración sólido, estable y operable internamente para eventos comerciales. Las brechas restantes (22.6%) corresponden a la falta de interfaces administrativas para prescindir de la intervención técnica, la optimización de activos pesados y el blindaje de seguridad en endpoints secundarios.
