# ARQUITECTURA DE SOFTWARE — PLATAFORMA "XV EXPERIENCE"
## DOCUMENTO 09: PLAN DE PRODUCCIÓN DEL PILOTO "SOPHIA VALENTINA"

---

### 1. OBJETIVO DEL PILOTO COMERCIAL

El propósito del proyecto piloto **"XV Años de Sophia"** es someter la nueva arquitectura a una prueba de validación en condiciones reales de mercado: **demostrar que es posible crear, personalizar, publicar y operar una segunda celebración con estética, colores, música, preguntas y contenidos radicalmente distintos a los de Angie, SIN MODIFICAR UNA SOLA LÍNEA DE CÓDIGO FUENTE**.

---

### 2. FICHA TÉCNICA Y REQUISITOS DEL EVENTO PILOTO

| Parámetro | Configuración del Piloto | Diferencia Respecto a Angie |
| :--- | :--- | :--- |
| **Identificador y Slug** | `evento_id = 2`, `slug = 'sophia-xv'` | Slug totalmente independiente |
| **Nombre de la Festejada** | Sophia Valentina Mendoza Silva | Nombre y firma caligráfica propia |
| **Plantilla Asignada** | `rose-gold-princess` | Pasa de *Verde Esmeralda* a *Oro Rosa y Champán* |
| **Fecha de la Fiesta** | Sábado, 14 de Noviembre de 2026, 8:30 PM | Fecha futura independiente para cuenta regresiva |
| **Padres de la Quinceañera**| Carlos Mendoza y Mariana Silva | Dedicatoria familiar personalizada |
| **Lugar de la Recepción** | Salón Grand Mansión, Hotel Eurobuilding, Caracas | Dirección, mapa GPS y coordenadas distintas |
| **Dress Code** | *Black Tie / Formal de Gala* (Reservar Rosa Oro para la quinceañera) | Paleta sugerida: Azul marino, negro, plata |
| **Modalidad de Regalos** | Transferencia bancaria / Zelle y Buzón de Sobres | Información financiera sustituyendo el texto original |
| **Música de Entrada** | Versión acústica/orquestal en formato MP3 | Archivo propio en `storage/media/2/audio/` |

---

### 3. CONFIGURACIÓN EN BASE DE DATOS (SIN TOCAR ARCHIVOS PHP)

Para activar la celebración de Sophia, el SuperAdmin o el propio sistema de ventas únicamente inserta las siguientes filas en la base de datos:

```sql
-- 1. Alta de Cliente y Pedido
INSERT INTO clientes (id, nombre_contacto, email, telefono, ciudad)
VALUES (2, 'Carlos Mendoza', 'carlos.mendoza@email.com', '+584120000000', 'Caracas');

INSERT INTO pedidos (id, cliente_id, paquete_id, codigo_pedido, estado, total, fecha_expiracion)
VALUES (2, 2, 2, 'PED-SOPHIA-2026', 'aprobado', 150.00, '2027-02-15 00:00:00');

-- 2. Alta de la Celebración (Evento 2)
INSERT INTO eventos (id, pedido_id, plantilla_id, slug, subdominio, tipo_evento, nombre_festejado, titulo_evento, fecha_evento, fecha_nacimiento, timezone, estado)
VALUES (2, 2, 2, 'sophia-xv', 'sophia', 'xv_anos', 'Sophia Valentina', 'Mis Dulces 15 Años', '2026-11-14 20:30:00', '2026-11-05 00:00:00', 'America/Caracas', 'publicado');

-- 3. Configuración de Contenidos y Tokens Estéticos para Sophia
INSERT INTO configuraciones_evento (
    evento_id, nombre_padre, nombre_madre, direccion_salon, nombre_salon, url_google_maps,
    dress_code_titulo, dress_code_descripcion, dress_code_colores_json, audio_musica_url,
    foto_portada_url, foto_despedida_url, itinerario_json, trivia_preguntas_json, tokens_estilo_css_json, modulos_habilitados_json
) VALUES (
    2,
    'Carlos Mendoza',
    'Mariana Silva',
    'Salón Grand Mansión, Hotel Eurobuilding, Calle La Guairita, Caracas, Venezuela',
    'Hotel Eurobuilding',
    'https://maps.google.com/?q=Hotel+Eurobuilding+Caracas',
    'BLACK TIE & GALA',
    'Te invitamos a lucir tu mejor atuendo de gala. Sugerimos trajes oscuros y vestidos elegantes. Por favor reserva los tonos rosa y champán para la quinceañera.',
    '["#1A1A24", "#2E384D", "#D4AF37", "#FFFFFF"]',
    'storage/media/2/audio/sophia_vals.mp3',
    'storage/media/2/covers/sophia_portada.jpg',
    'storage/media/2/covers/sophia_gala.jpg',
    '[{"hora":"8:30 PM","actividad":"Recepción y Cóctel de Bienvenida"},{"hora":"9:45 PM","actividad":"Entrada Triunfal de Sophia"},{"hora":"10:15 PM","actividad":"Vals con Papá y Brindis de Honor"},{"hora":"11:00 PM","actividad":"Cena de Gala Internacional"},{"hora":"12:30 AM","actividad":"Show de Luces y Hora Loca VIP"}]',
    '[{"pregunta":"¿Cuál es la pasión artística de Sophia?","opciones":["El Ballet Clásico","La Pintura al Óleo","Tocar el Piano","Canto Lírico"],"correcta":0,"feedback_correcto":"¡Exacto! El ballet es su gran pasión desde pequeña 🩰✨","feedback_incorrecto":"La respuesta correcta es: El Ballet Clásico 🩰"},{"pregunta":"¿Cuál es su postre irresistible?","opciones":["Tarta de Fresa","Cheesecake de Frutos Rojos","Macarons Franceses","Churros con Chocolate"],"correcta":1,"feedback_correcto":"¡Totalmente! Ama el cheesecake de frutos rojos 🍓🍰","feedback_incorrecto":"Su favorito es el Cheesecake de Frutos Rojos 🍰"}]',
    '{"--color-primary":"#8E4A52","--color-secondary":"#C98276","--color-gold":"#E5A985","--color-gold-light":"#FFD9C0","--color-bg":"#1C1418"}',
    '{"tarjeta_historia":true,"cuenta_regresiva":true,"trivia":true,"playlist":true,"lluvia_sobres":true,"rsvp":true,"fotos_envivo":true,"libro_3d":true,"proyector_tv":true,"sorteo_rifa":true,"checkin_qr":true}'
);

-- 4. Creación de Usuario Anfitrión para los Padres de Sophia
INSERT INTO usuarios (rol_id, cliente_id, evento_id, email, password_hash, nombre_completo, activo)
VALUES (2, 2, 2, 'padres.sophia@email.com', '$2y$12$e80yVvj7gX1o0dZkY2j9.uWcO2e9wV8nF2q1sK3zL9jX0mP4uV6qa', 'Carlos y Mariana Mendoza', 1);
```

---

### 4. RECURSOS EN ALMACENAMIENTO DE SOPHIA

En el sistema de almacenamiento se crea la carpeta exclusiva para Sophia:
```text
storage/media/2/
├── covers/
│   ├── sophia_portada.jpg      # Foto de Sophia con tiara de cristal
│   └── sophia_gala.jpg         # Foto de Sophia en vestido rosa oro
├── audio/
│   └── sophia_vals.mp3         # Pista musical elegida por la familia
├── photos/                     # Directorio inicial donde caerán las fotos de su fiesta
└── videos/                     # Videos iniciales del cortejo
```

---

### 5. VERIFICACIÓN DEL COMPORTAMIENTO CONCURRENTE MULTI-TENANT

Al ingresar los usuarios en sus respectivos enlaces, la plataforma exhibe un comportamiento totalmente aislado:

```text
ESCENARIO 1: Invitado de Angie abre:
URL: https://xvexperience.com/evento/angie-xv
-> TenantResolver inyecta: evento_id = 1
-> Carga tema: 'esmeralda-real' (Verde esmeralda y oro)
-> Muestra fotos: angie-portada.jpg, trivia de repostería/pasticho, fecha 3 Octubre 2026.
-> Los RSVPs registrados van a: invitados WHERE evento_id = 1.

ESCENARIO 2: Invitado de Sophia abre simultáneamente:
URL: https://xvexperience.com/evento/sophia-xv
-> TenantResolver inyecta: evento_id = 2
-> Carga tema: 'rose-gold-princess' (Rosa cuarzo, oro rosa y champán)
-> Muestra fotos: sophia_portada.jpg, trivia de ballet/cheesecake, fecha 14 Noviembre 2026.
-> Los RSVPs registrados van a: invitados WHERE evento_id = 2.
```

**Resultado de la Prueba:** Cero colisión de datos, cero contaminación de bases de datos y experiencia 100% personalizada para ambos clientes sobre la misma infraestructura de software.
