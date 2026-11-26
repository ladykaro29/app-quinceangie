# MOTOR DE AUTOMATIZACIONES Y COMUNICACIONES PROGRAMADAS — FASE 2

**Plataforma:** XV Experience  
**Módulo:** Motor de Automatizaciones y Cola Persistente de Eventos  
**Autor:** Arquitecto de Software Senior  
**Estado:** Especificación de Diseño (Pendiente de Aprobación para Implementación)  

---

## 1. OBJETIVO DEL MOTOR

Crear un sistema de comunicaciones programadas, dinámicas y persistentes por celebración que:
1. Calcule automáticamente los momentos óptimos de comunicación según la fecha, hora y zona horaria del evento.
2. No dependa bajo ninguna circunstancia de temporizadores del navegador web del usuario.
3. Almacene las tareas en una cola persistente en base de datos.
4. Bloquee de forma estricta el envío accidental de correos vencidos cuando se configure o reactive una automatización con posterioridad a la fecha objetivo.
5. Permita al administrador pausar, editar, activar, forzar o cancelar cada regla de automatización.

---

## 2. MODELO DE DATOS DE AUTOMATIZACIONES

### 2.1 Tabla `automatizaciones_reglas` (Configuración por Evento)
```sql
CREATE TABLE IF NOT EXISTS automatizaciones_reglas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evento_id INT NOT NULL,
    codigo_regla VARCHAR(50) NOT NULL, -- 'REG_INVITACION', 'CONF_RSVP', 'REC_7D', 'REC_3D', 'REC_1D', 'REC_HOY', 'ALBUM_FIESTA', 'AGRADECIMIENTO', 'ALBUM_FINAL'
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    tipo_disparador VARCHAR(30) NOT NULL, -- 'inmediato', 'relativo_evento', 'manual_admin'
    offset_minutos INT NOT NULL DEFAULT 0, -- Minutos relativos a la fecha_evento (negativo: antes, positivo: después)
    asunto_email VARCHAR(255) NOT NULL,
    plantilla_codigo VARCHAR(50) NOT NULL,
    activo TINYINT NOT NULL DEFAULT 1,
    ignorar_vencidos TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    UNIQUE(evento_id, codigo_regla)
);
```

### 2.2 Tabla `cola_automatizaciones` (Cola Persistente de Envíos)
```sql
CREATE TABLE IF NOT EXISTS cola_automatizaciones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evento_id INT NOT NULL,
    regla_id INT NOT NULL,
    invitado_id INT NOT NULL,
    programado_para DATETIME NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente', -- 'pendiente', 'en_proceso', 'completado', 'cancelado', 'vencido', 'fallido'
    intentos INT NOT NULL DEFAULT 0,
    ultimo_error TEXT NULL,
    ejecutado_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (regla_id) REFERENCES automatizaciones_reglas(id) ON DELETE CASCADE,
    FOREIGN KEY (invitado_id) REFERENCES invitados(id) ON DELETE CASCADE,
    UNIQUE(regla_id, invitado_id)
);

CREATE INDEX IF NOT EXISTS idx_cola_ejecucion ON cola_automatizaciones(estado, programado_para);
```

---

## 3. CÁLCULO DINÁMICO DE FECHAS: CELEBRACIÓN SOPHIA VALENTINA

### 3.1 Datos Maestros del Evento
- **Festejada:** Sophia Valentina Rivas Sánchez.
- **Fecha y Hora:** 21 de Diciembre de 2026, 19:30 (7:30 PM).
- **Lugar:** Salón Nova Roma.
- **Zona Horaria Oficial:** `America/Caracas` (UTC-4).

### 3.2 Matriz de Automatizaciones de la Celebración
| Código Regla | Tipo | Disparador / Fecha Calculada | Asunto del Correo | Audiencia Objetivo |
| :--- | :--- | :--- | :--- | :--- |
| `AUTO_REGISTRO` | Inmediato | Al registrar correo en invitación | *Sophia Valentina XV · Tu Invitación Digital y Enlace Mágico* | Registro público e invitados |
| `AUTO_CONFIRMACION` | Inmediato | Al confirmar asistencia en el RSVP | *¡Confirmado! Tu Pase VIP Oficial · Mis XV Años Sophia Valentina* | Confirmados (`asistira = 1`) |
| `AUTO_REC_7D` | Programado | **14 Dic 2026, 10:00 AM** (-7 días) | *Faltan 7 días para una noche mágica ✨ Sophia Valentina XV* | Confirmados y pendientes |
| `AUTO_REC_3D` | Programado | **18 Dic 2026, 10:00 AM** (-3 días) | *Estamos a 3 días de celebrar juntos 🌸 Detalles de llegada* | Confirmados |
| `AUTO_REC_1D` | Programado | **20 Dic 2026, 06:00 PM** (-1 día) | *¡Mañana es el gran día! 👑 Recordatorio de Pase VIP y Dress Code* | Confirmados |
| `AUTO_REC_HOY` | Programado | **21 Dic 2026, 12:00 PM** (Día fiesta) | *¡Hoy celebramos los XV de Sophia Valentina! 🥂 Te esperamos 7:30 PM* | Confirmados |
| `AUTO_ALBUM_FIESTA`| Programado | **21 Dic 2026, 09:30 PM** (+2h fiesta) | *📸 Comparte tus mejores momentos en el álbum en vivo de la fiesta* | Asistentes a la fiesta |
| `AUTO_AGRADECIMIENTO`| Programado| **22 Dic 2026, 02:00 PM** (+1 día) | *Gracias por acompañarme en mis XV Años 💖 — Sophia Valentina* | Asistentes confirmados |
| `AUTO_ALBUM_FINAL` | Manual | Activación administrativa post-fiesta | *¡El Álbum Oficial de Recuerdos está listo para ti! 📖✨* | Todos los invitados |

---

## 4. POLÍTICAS CRÍTICAS DE SEGURIDAD Y CONTROL DE ENVÍOS

### 4.1 Protección Estricta Contra Envíos Vencidos
Si una automatización (ej. `AUTO_REC_7D`) se crea, reactiva o asocia a un nuevo invitado cuando la fecha calculada ya transcurrió:
- El motor evalúa: `programado_para < NOW()`.
- Si `ignorar_vencidos == 1`: El estado se marca automáticamente como `'vencido'` sin invocar a Resend ni emitir correos inoportunos.
- **Solo** si el administrador selecciona expresamente en el panel *"Forzar envío extemporáneo"*, el sistema procesará el envío.

### 4.2 Control de Preferencias y Opt-Out
Antes de encolar o procesar cada mensaje:
1. El motor comprueba si `preferencias_comunicacion->opt_out == true`.
2. Si está desuscrito, la tarea se marca como `'cancelado'` con el motivo *"Destinatario solicitó exclusión de comunicaciones"*.
3. Los correos estrictamente transaccionales de seguridad (como la entrega directa del Pase VIP tras confirmación manual) quedan exentos de opt-out promocional pero respetan las normas legales internacionales.

---

## 5. WORKER DESATENDIDO: `cron/worker_automatizaciones.php`

El procesamiento se ejecuta en segundo plano mediante un script CLI programado:
- **Frecuencia recomendada:** Cada 5 o 10 minutos vía Crontab o Programador de Tareas.
- **Comando:** `php cron/worker_automatizaciones.php --evento=all --limite=50`
- **Mecanismo de Bloqueo (*Locking*):** Utiliza un cerrojo por archivo (`sys_get_temp_dir() . '/xvexp_worker.lock'`) para impedir que dos instancias concurrentes procesen la misma cola y dupliquen envíos.
- **Lotes Controlados:** Procesa un máximo configurable de 50 correos por ejecución, respetando la cuota y los límites de tasa de Resend API.
