# ARQUITECTURA DE SOFTWARE — PLATAFORMA "XV EXPERIENCE"
## DOCUMENTO 11: MATRIZ DE RIESGOS TÉCNICOS Y PLAN DE MITIGACIÓN

---

### 1. MATRIZ DE RIESGOS TÉCNICOS, OPERATIVOS Y DE NEGOCIO

| ID | Riesgo Identificado | Categoría | Probabilidad | Impacto | Nivel de Riesgo | Estrategia de Mitigación / Contingencia |
| :---: | :--- | :---: | :---: | :---: | :---: | :--- |
| **RSK-01** | Saturación de Ancho de Banda y Workers de PHP por Streaming de Video | Técnico | Alta | Muy Alto | **CRÍTICO** | Migrar entrega de video a Cloudflare R2 / S3. En la fase local, mantener soporte `HTTP_RANGE 206` estricto y limitar tamaño de video a 15 MB. |
| **RSK-02** | Contaminación Cruzada de Datos entre Festejadas (*Tenant Leaks*) | Seguridad | Baja | Catastrófico | **CRÍTICO** | Inyección inmutable del `evento_id` por middleware en el contexto de sesión; validación obligatoria en capa de repositorio; pruebas unitarias automatizadas. |
| **RSK-03** | Falla de Conectividad a Internet en la Puerta del Salón durante la Recepción | Operativo | Media | Alto | **ALTO** | El escáner QR en `admin/index.php` contará con caché local en LocalStorage de la lista de invitados para validar el pase offline e informar la asistencia cuando regrese la red. |
| **RSK-04** | Bloqueo de Cámara WebRTC para el Escaneo de Códigos QR | Operativo | Media | Medio | **MEDIO** | El modal de escaneo dispone de un buscador alternativo por número de pase (`PASE #12`), código de rifa (`RIFA-4821`) o nombre del titular con teclado. |
| **RSK-05** | Restricciones de Autoplay de Audio en Dispositivos iOS (Safari) y Android | UX / Móvil | Alta | Bajo | **MEDIO** | Conservar la estrategia actual: botón flotante de audio con tooltip y desbloqueo automático del contexto sonoro tras el primer toque táctil de pantalla. |
| **RSK-06** | Subida Masiva de Fotografías Dañinas o Llenado Malicioso de Disco | Seguridad | Media | Alto | **ALTO** | Rate-limiting de 10 fotos por IP cada 5 min; validación binaria con `finfo`; redimensionamiento a máx 1920px y conversión a WebP con remoción de metadatos EXIF. |
| **RSK-07** | Bloqueo Concurrente de Base de Datos (*Database Lock*) | Técnico | Media | Muy Alto | **ALTO** | Prohibición absoluta de SQLite en producción. Uso exclusivo de MySQL/MariaDB con transacciones InnoDB cortas e índices optimizados por `evento_id`. |
| **RSK-08** | Rotura de Enlaces Previamente Compartidos de Angie por WhatsApp | Operativo | Baja | Muy Alto | **ALTO** | Reglas de redirección HTTP 301 permanentes en `.htaccess` y en el Front Controller para las rutas antiguas (`/`, `/libro-recuerdos.php`, `/en-vivo.php`). |

---

### 2. PROTOCOLOS DE CONTINGENCIA DETALLADOS

#### A. Protocolo de Emergencia ante Fallo de Internet en el Salón de Fiesta:
Si el salón de fiestas carece de señal móvil o el Wi-Fi local colapsa durante la entrada de los 200 invitados:
1. El recepcionista puede descargar previamente desde el panel de anfitrión un archivo PDF o imprimir la **Lista Oficial de Pases VIP y Códigos de Rifa** generada por el sistema.
2. La aplicación web del escáner implementa una copia local en memoria del navegador (*Offline Cache* vía PWA / LocalStorage) que valida la firma criptográfica MD5 del QR sin necesidad de consultar el servidor en tiempo real.
3. Al restablecerse la conexión, la aplicación envía por lotes (*batch sync*) los registros de asistencia al backend.

#### B. Protocolo de Aislamiento de Medios Pesados:
Para evitar que un video viral de 50 MB en la fiesta de Angie degrade el servidor web de la fiesta de Sophia:
1. Las cargas de fotos y videos se enrutan a través de un directorio aislado por ID de celebración (`storage/media/{evento_id}/`).
2. El servidor web aplica límites de concurrencia por host virtual (`mpm_event` o `mpm_worker` en Apache).
3. En la Fase 7, los enlaces multimedia se generan apuntando directamente a dominios CDN externos (`https://media.xvexperience.com/...`), liberando el 100% de la carga de transferencia del servidor central de la aplicación.
