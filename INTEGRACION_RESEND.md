# INTEGRACIÓN CON RESEND — ARQUITECTURA DE MENSAJERÍA TRANSACCIONAL

**Plataforma:** XV Experience  
**Servicio:** API Oficial de Resend (v1)  
**Autor:** Especialista en Infraestructura y Automatización de Correo  
**Estado:** Especificación de Diseño (Pendiente de Aprobación para Implementación)  

---

## 1. OBJETIVO DEL SERVICIO

Implementar un servicio centralizado, resiliente y de alta entregabilidad para el envío de correos electrónicos transaccionales y automatizados en la plataforma XV Experience, utilizando la API oficial de **Resend**.

El servicio debe:
1. Garantizar aislamiento multi-tenant por celebración.
2. Soportar envíos individuales e importaciones/envíos masivos controlados.
3. Rastrear eventos en tiempo real mediante webhooks firmados criptográficamente.
4. Diseñar plantillas HTML adaptadas estrictamente a la identidad visual de Sophia Valentina.
5. Gestionar rebotes, quejas y solicitudes de desuscripción de forma automática.

---

## 2. CONFIGURACIÓN Y VARIABLES DE ENTORNO

Las credenciales y parámetros de configuración se gestionan mediante variables de entorno seguras, **sin almacenarse jamás en el código fuente ni en el repositorio**:

```env
# Configuración Transaccional Resend (XV Experience)
RESEND_API_KEY=re_123456789_abcdefghijklmnopqrstuvwxyz
EMAIL_FROM="Sophia Valentina XV <invitaciones@xvexperience.com>"
EMAIL_REPLY_TO="eventos@xvexperience.com"
RESEND_WEBHOOK_SECRET=whsec_abcdef1234567890abcdef1234567890

# Modo de Entorno
RESEND_ENVIRONMENT=production # 'sandbox' o 'production'
RESEND_TEST_EMAIL=pruebas@xvexperience.com # Redirección opcional en modo desarrollo
```

### 2.1 Configuración de Dominios (DNS)
Para alcanzar una reputación de remitente del 99%+ y evitar carpetas de spam:
- **SPF:** `v=spf1 include:amazonses.com ~all`
- **DKIM:** Claves CNAME proporcionadas por Resend para el dominio verificado `xvexperience.com`.
- **DMARC:** `v=DMARC1; p=quarantine; pct=100; rua=mailto:dmarc-reports@xvexperience.com; aspf=r; adkim=r`

---

## 3. ARQUITECTURA DEL CLIENTE: `App\Services\ResendService`

Se implementa un cliente HTTP nativo en PHP utilizando `cURL`, eliminando dependencias pesadas de terceros y garantizando compatibilidad con cualquier hosting PHP 8.x:

```php
namespace App\Services;

class ResendService
{
    private string $apiKey;
    private string $fromEmail;
    private string $replyTo;
    private string $baseUrl = 'https://api.resend.com';

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? ($_ENV['RESEND_API_KEY'] ?? '');
        $this->fromEmail = $_ENV['EMAIL_FROM'] ?? 'Sophia Valentina <no-reply@xvexperience.com>';
        $this->replyTo = $_ENV['EMAIL_REPLY_TO'] ?? 'contacto@xvexperience.com';
    }

    /**
     * Envío individual transaccional con clave de idempotencia
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $htmlContent,
        ?string $idempotencyKey = null,
        array $tags = []
    ): array;

    /**
     * Envío masivo por lotes (Resend Batch API: hasta 100 correos por llamada)
     */
    public function sendBatch(array $emailsPayload): array;

    /**
     * Petición HTTP centralizada con reintentos exponenciales
     */
    private function request(string $endpoint, array $payload, string $method = 'POST', ?string $idempotencyKey = null): array;
}
```

### 3.1 Mecanismo de Idempotencia
Cada solicitud incluye el encabezado HTTP:
`Idempotency-Key: {evento_id}_{tipo_mensaje}_{invitado_id}_{fecha_yyyymmdd}`
Esto evita de forma absoluta que un invitado reciba correos duplicados ante reintentos de red o caídas temporales de conexión.

---

## 4. SISTEMA DE WEBHOOKS EN TIEMPO REAL

Se habilita el endpoint seguro `api/webhooks/resend.php` para recibir notificaciones de estado de Resend:

### 4.1 Verificación de Firma Criptográfica (Svix/HMAC-SHA256)
Para asegurar que los eventos provienen legítimamente de Resend:
```php
$signature = $_SERVER['HTTP_SVIX_SIGNATURE'] ?? $_SERVER['HTTP_RESEND_SIGNATURE'] ?? '';
$timestamp = $_SERVER['HTTP_SVIX_TIMESTAMP'] ?? '';
$msgId = $_SERVER['HTTP_SVIX_ID'] ?? '';
$payload = file_get_contents('php://input');

$expectedSignature = hash_hmac('sha256', "{$msgId}.{$timestamp}.{$payload}", $webhookSecret);
if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(401);
    exit('Firma de webhook inválida');
}
```

### 4.2 Mapeo de Eventos en Base de Datos
| Evento Resend | Acción en Base de Datos (`comunicaciones_log` / `invitados`) |
| :--- | :--- |
| `email.sent` | Estado actualizado a `'sent'`. |
| `email.delivered` | Estado actualizado a `'delivered'`. Registra `updated_at`. |
| `email.opened` | Incrementa contador de aperturas y registra fecha de lectura. |
| `email.clicked` | Registra clic en enlace mágico o Pase VIP. |
| `email.bounced` | Marca `'bounced'`, registra motivo y suprime futuros envíos automáticos al email. |
| `email.complained` | Activa `opt_out = true` inmediato para respetar la privacidad del usuario. |

---

## 5. PLANTILLAS HTML RESPONSIVAS: IDENTIDAD DE SOPHIA VALENTINA

Las plantillas de correo respetan rigurosamente el **Manual de Identidad Cromática de Sophia Valentina**, con **Cero Elementos Verdes**:
- **Fondo General del Correo:** `#FFF3E7` (Marfil cálido).
- **Contenedor Principal de la Tarjeta:** Blanco puro (`#FFFFFF`) con bordes sutiles en Champagne (`#C5A16A`).
- **Encabezados:** `#4A303B` (Malva profundo) con familias tipográficas Georgia / Times New Roman (equivalentes serif web-safe).
- **Cuerpo de Texto y Fechas:** `#79544D` (Marrón rosado cálido, contraste $5.8:1$).
- **Botón de Acción Principal (Pase VIP / Confirmar):**
  - Fondo: `#854761` (Rosa intenso).
  - Texto: `#FFFFFF` (Blanco puro, contraste $7.3:1$).
  - Bordes redondeados elegantes (24px) y sombra champagne.
- **Detalles Gráficos:** Mariposa y corona champagne en SVG/PNG de alta definición optimizados para clientes de correo (Gmail, Outlook, Apple Mail, iOS Mail).
- **Pase VIP embebido:** Imagen generada del ticket o código QR generado en tiempo real.
- **Pie de Página Legal:** Enlace de desuscripción de un solo clic y datos de privacidad.

---

## 6. PREVENCIÓN DE ENVÍOS DUPLICADOS Y POLÍTICA DE SUPRESIÓN

1. **Tabla de Supresión:** Los correos con eventos de rebote duro (*hard bounce*) o queja de spam se bloquean automáticamente en la tabla `comunicaciones_log`, impidiendo reintentos que dañen la reputación del dominio.
2. **Validación de Estado Previo:** Antes de enviar un recordatorio, el motor consulta si ya existe un registro exitoso (`state IN ('sent', 'delivered')`) para ese `invitado_id` y `tipo_comunicacion`.
