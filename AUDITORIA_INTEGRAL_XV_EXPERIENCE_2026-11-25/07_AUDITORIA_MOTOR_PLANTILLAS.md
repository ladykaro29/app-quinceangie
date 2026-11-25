# 07 — AUDITORÍA DEL SISTEMA DE PERSONALIZACIÓN Y MOTOR DE PLANTILLAS
**Plataforma:** XV Experience  
**Fecha:** 25 de Noviembre de 2026  
**Especialidad:** Arquitectura de Software y Frontend

---

## 1. ¿EXISTE UN VERDADERO MOTOR DE PLANTILLAS?
**Diagnóstico:** **NO.**  
Actualmente no existe un motor de plantillas compilado (como Blade, Twig o Mustache). 

El sistema actual es un **esquema de dinamización por inyección de variables globales y tokens CSS**:
1. La tabla `eventos` suministra los metadatos principales (nombre, fecha, lugar, cover, música).
2. La tabla `configuraciones_evento` almacena un diccionario clave-valor con arrays codificados en JSON (`design_tokens`, `itinerario_json`, `trivia_json`).
3. El archivo [public/index.php](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/public/index.php) inyecta estos valores en tiempo de ejecución reemplazando cadenas fijas.

---

## 2. MATRIZ DE ELEMENTOS PERSONALIZABLES (CONFIGURACIÓN VS CÓDIGO)

| Elemento | ¿Configurable desde BD sin tocar código? | Mecanismo Actual | ¿Dónde reside la limitación? |
| :--- | :---: | :--- | :--- |
| **Nombre de la Festejada** | **SÍ** | Columna `festejada_nombre` en `eventos`. | Ninguna. Funciona 100% dinámico. |
| **Fecha, Hora y Salón** | **SÍ** | Columnas `fecha_evento`, `hora_evento`, `lugar_nombre`.| Ninguna. |
| **Dirección de Salón** | **SÍ** | Columna `lugar_direccion` (soporta "Dir. Pendiente"). | Ninguna. |
| **Nombres de Padres y Padrinos** | **SÍ** | Tabla `configuraciones_evento` (`padres_nombres`). | Ninguna. |
| **Colores Principales (Paleta)**| **SÍ** | Objeto JSON `design_tokens` inyectado en `:root`. | Sobrescribe variables CSS con `!important`. |
| **Itinerario de Actividades** | **SÍ** | Objeto JSON `itinerario_json`. | Renderizado dinámico en bucle `foreach`. |
| **Preguntas de Trivia** | **SÍ** | Objeto JSON `trivia_json`. | Inyectado como variable JavaScript en el cliente. |
| **Música de Fondo** | **SÍ** | Columna `musica_url` en `eventos`. | Requiere que el archivo físico esté en disco. |
| **Fotografía de Portada** | **SÍ** | Columna `portada_url` en `eventos`. | Requiere que el archivo físico esté en disco. |
| **Mariposas, Flores y Adornos** | **NO** | Código SVG hardcoded en `public/index.php`. | Si otra fiesta requiere copos de nieve, debe editarse el PHP. |
| **Tipografías** | **PARCIAL** | Mismo set cargado en cabecera Google Fonts. | No se pueden asignar fuentes distintas por evento desde BD. |
| **Orden de las Tarjetas** | **NO** | Secuencia HTML fija en `public/index.php`. | El orden de las 13 tarjetas es inmutable. |
| **Activar/Desactivar Tarjetas** | **NO** | No hay flags booleanos por tarjeta. | Todas las 13 tarjetas se muestran obligatoriamente. |

---

## 3. ¿SE PUEDE CREAR UNA NUEVA CELEBRACIÓN SOLO DESDE EL PANEL ADMIN?
**Diagnóstico:** **NO EN ESTE MOMENTO.**
- Si un operador de Kick-Off ingresa a `/admin`, **no existe un formulario de "Crear Nueva Celebración"**.
- El selector superior de celebraciones únicamente permite alternar entre las celebraciones ya creadas en la base de datos (`Angie Karolina` y `Sophia Valentina`).
- Para dar de alta una 3ª celebración (ej. *Victoria*), el equipo técnico debe ejecutar sentencias SQL manuales o correr un script de seeder (`seed_evento.php`) y subir manualmente las imágenes al servidor por FTP, SSH o commit de Git.

---

## 4. PROPUESTA ARQUITECTÓNICA: MOTOR REUTILIZABLE DE PLANTILLAS

```
templates/
├── layouts/
│   └── invitation-master.php       # Shell común (Viewport, Meta, Audio Player, Deck Controller)
├── themes/                         # Temáticas estéticas intercambiables
│   ├── emerald-prestige/           # Temática Angie: coronas doradas, partículas esmeralda
│   │   ├── theme.json              # Metadatos, fuentes y paleta por defecto
│   │   ├── styles.css
│   │   └── ornaments/              # SVG específicos (coronas, destellos reales)
│   └── butterfly-lavender/         # Temática Sophia: mariposas flotantes, flores, lavanda
│       ├── theme.json
│       ├── styles.css
│       └── ornaments/              # SVG específicos (mariposas, guirnaldas florales)
└── cards/                          # Bloques funcionales independientes y conmutables
    ├── card-historia.php           # Renderiza si evento tiene 'historia_texto'
    ├── card-itinerario.php         # Renderiza si 'itinerario_json' no está vacío
    ├── card-trivia.php             # Renderiza si flag 'modulo_trivia' = 1
    └── card-rsvp.php               # Siempre activo
```
