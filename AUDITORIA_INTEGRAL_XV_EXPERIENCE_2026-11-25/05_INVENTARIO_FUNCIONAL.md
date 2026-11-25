# 05 — INVENTARIO FUNCIONAL COMPLETO
**Plataforma:** XV Experience  
**Fecha:** 25 de Noviembre de 2026  
**Especialidad:** Full Stack, QA y Product Management

---

## 1. INVENTARIO DE LAS 13 TARJETAS Y COMPONENTES DE LA INVITACIÓN

| Componente | Archivo / Módulo | Estado Angie | Estado Sophia | Estado Multi-Tenant | Clasificación Funcional | Prioridad |
| :--- | :--- | :---: | :---: | :---: | :--- | :---: |
| **00. Portada Interactiva** | `public/index.php` / Card 0 | OK | OK | OK | **Funcional y Verificada** | P3 |
| **01. Historia y Mensaje** | `public/index.php` / Card 1 | OK | OK | OK | **Funcional y Verificada** | P3 |
| **02. Cuenta Regresiva** | `public/index.php` / Card 2 | OK | OK | OK | **Funcional y Verificada** | P3 |
| **03. Familia y Padrinos** | `public/index.php` / Card 3 | OK | OK | OK | **Funcional y Verificada** | P3 |
| **04. Lugar, Fecha y Hora** | `public/index.php` / Card 4 | OK | OK (Dir. Pendiente) | OK | **Funcional y Verificada** | P2 |
| **05. Código de Vestimenta**| `public/index.php` / Card 5 | OK | OK | OK | **Funcional y Verificada** | P3 |
| **06. Itinerario Gala** | `public/index.php` / Card 6 | OK | OK | OK | **Funcional y Verificada** | P3 |
| **07. Trivia Interactiva** | `public/index.php` / Card 7 | OK | OK | OK | **Funcional y Verificada** | P3 |
| **08. Playlist Sugerencias**| `public/index.php` / Card 8 | OK | OK | **Filtro Pendiente**| **Parcialmente Funcional** | **P1** |
| **09. Lluvia de Sobres** | `public/index.php` / Card 9 | OK | OK | OK | **Funcional y Verificada** | P3 |
| **10. Confirmación RSVP** | `public/index.php` / Card 10 | OK | OK | OK | **Funcional y Verificada** | P2 |
| **11. Galería y Fotos QR** | `public/index.php` / Card 11 | OK | OK | OK | **Funcional y Verificada** | P2 |
| **12. Despedida y Gratitud**| `public/index.php` / Card 12 | OK | OK | OK | **Funcional y Verificada** | P3 |

---

## 2. INVENTARIO DE MÓDULOS DE VALOR AGREGADO

### 2.1 Álbum Colaborativo Móvil por QR ([public/subir-fotos.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/subir-fotos.php))
- **Objetivo:** Permitir a los invitados de la fiesta subir fotos directamente escaneando el código QR proyectado en la pantalla.
- **Estado Angie:** Funcional con tokens esmeralda.
- **Estado Sophia:** Funcional con tokens ciruela y rosa.
- **Aislamiento Multi-Tenant:** Verificado. Envía `evento_id = 2` vía `FormData`.
- **Clasificación:** **Funcional y Verificada.**

### 2.2 Pantalla Interactiva Proyector en Vivo ([public/en-vivo.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/en-vivo.php))
- **Objetivo:** Proyectar las fotos en tiempo real durante la recepción con código QR flotante para incentivar la participación.
- **Estado Angie:** Funcional (`evento_id = 1`, fotos oficiales cargadas).
- **Estado Sophia:** Funcional (`evento_id = 2`, feed independiente).
- **Clasificación:** **Funcional y Verificada.**

### 2.3 Grimorio Mágico 3D de Recuerdos ([public/libro-recuerdos.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/libro-recuerdos.php))
- **Objetivo:** Libro digital con animación de doble cara, sonido de pergamino, dedicatorias y galería de momentos.
- **Estado Angie:** Funcional con 160 fotos y 19 videos de gala.
- **Estado Sophia:** Funcional para dedicatorias (muestra la dedicatoria oficial de sus padres). Sin embargo, hereda la lista de videos de Angie al no tener videos propios cargados.
- **Clasificación:** **Parcialmente Funcional.**

### 2.4 Control de Acceso y Check-in QR ([api/checkin.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/api/checkin.php))
- **Objetivo:** Validar en la entrada de la fiesta los pases VIP presentados por los invitados en su teléfono.
- **Estado Angie:** Funcional con pases `XV-ANGIE-...`.
- **Estado Sophia:** Funcional por ID directo, pero falla si se busca por patrón de texto debido al regex rígido `/XVANGIE-(\d+)/i`.
- **Clasificación:** **Parcialmente Funcional.**

### 2.5 Panel de Control Administrativo ([admin/index.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/admin/index.php))
- **Objetivo:** Administrar invitados, exportar listas a CSV, moderar fotos, gestionar música y controlar check-in.
- **Estado:** Protegido con clave. Soporta selector de evento activo mediante `$_SESSION['active_evento_id']`.
- **Clasificación:** **Funcional y Verificada para Operador Interno.**
