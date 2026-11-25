# FASE 2: CENTRO DE MODERACIÓN ADMINISTRATIVO

---

## 1. Visión y Funcionalidad
El Centro de Moderación Multimedia (`admin/moderacion-fotos.php`) permite a los operadores de Kick-Off Development y anfitriones del evento auditar, filtrar y controlar el flujo de fotografías proyectadas en pantalla gigante en tiempo real.

Está plenamente integrado con la sesión administrativa existente y protegido por control de acceso (`AuthService` y contraseñas de operador).

---

## 2. Componentes de la Interfaz

### A. Selector de Celebración (Multi-Tenant)
Un menú desplegable superior permite conmutar instantáneamente entre las celebraciones activas (Angie Karolina, Sophia Valentina y futuros eventos). Todas las métricas, galerías y acciones se aíslan de inmediato para el `evento_id` seleccionado.

### B. Tarjetas de Indicadores en Tiempo Real (KPIs)
1. **Total Recibidas:** Conteo global de fotos asociadas al evento.
2. **Por Moderar (Pendientes):** Cantidad de fotos esperando revisión antes de proyectarse (prioridad alta).
3. **Aprobadas en Vivo:** Fotos que están rotando en el proyector de la fiesta.
4. **Rechazadas:** Fotos descartadas u ocultadas.
5. **Espacio Almacenado:** Peso total consumido en disco (MB) con base en la cuota límite.

### C. Pestañas de Navegación Rápida
- **Pendientes (Predeterminada):** Muestra únicamente las fotografías que requieren acción del moderador.
- **Aprobadas:** Fotos activas en la fiesta.
- **Rechazadas:** Archivo de fotos no autorizadas.
- **Destacadas:** Fotos marcadas con estrella especial para momentos culminantes (vals, brindis, entrada).
- **Todas:** Historial cronológico completo.

### D. Acciones en Lote (Batch Processing)
- **Casillas de selección múltiple:** Checkbox por fotografía y casilla maestro "Seleccionar todas".
- **Botón "Aprobar Seleccionadas":** Envía `action=aprobar` con un array de IDs, autorizando 10 o 20 fotos con un solo clic.
- **Botón "Rechazar Seleccionadas":** Envía `action=rechazar` con los IDs seleccionados.

### E. Visor Modal Lightbox con Datos Forenses
Al hacer clic en cualquier miniatura se despliega un visor modal que incluye:
- Fotografía en resolución completa.
- Nombre del invitado y dedicatoria asociada.
- Fecha y hora exacta de recepción.
- Peso en KB y dimensiones en píxeles.
- Hash criptográfico SHA-256 para auditoría de integridad.
- Botón directo para descarga local del archivo original.
