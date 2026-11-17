# ARQUITECTURA DE SOFTWARE — PLATAFORMA "XV EXPERIENCE"
## DOCUMENTO 05: DISEÑO DEL MOTOR DE PLANTILLAS Y COMPONENTES

---

### 1. OBJETIVO DEL MOTOR DE PLANTILLAS

El motor de plantillas de **XV Experience** resuelve la principal limitación detectada en la auditoría técnica: **la imposibilidad de cambiar colores, tipografías, fondos, textos o habilitar/deshabilitar tarjetas sin modificar código**.

El objetivo es lograr que una sola base de código pueda generar invitaciones visualmente radicales y distintas entre sí (ej. un diseño gótico esmeralda para una fiesta medieval vs. un diseño dulce rosa dorado para una fiesta de gala clásica) mediante un sistema modular de **Layouts**, **Componentes**, **Temas (Themes)** y **Tokens de Diseño**.

---

### 2. ARQUITECTURA DEL MOTOR DE PLANTILLAS

```mermaid
flowchart TD
    subgraph BaseDatos[Base de Datos: Evento & Configuración]
        DataContent[Textos, Fechas, Padres, Itinerario, Trivia]
        ThemeSelection[ID de Plantilla: 'rose-gold-princess']
        FeatureToggles[Modulos Habilitados: {trivia: true, lluvia: false}]
        CustomTokens[Tokens Sobrescritos: {--color-primary: '#E0A899'}]
    end

    subgraph ThemeSystem[Sistema de Temas: templates/themes/]
        ThemeManifest[theme.json: Metadatos, Assets, Fuentes, Paleta Base]
        ThemeCSS[theme.css: Reglas CSS con Variables Custom Properties]
        ThemeSVGs[Elementos Vectoriales: Coronas, Tiaras, Mariposas, Esquinas]
    end

    subgraph ComponentRegistry[Registro de Componentes: templates/components/]
        CardCover[Componente: Portada Real]
        CardStory[Componente: Historia]
        CardCountdown[Componente: Cuenta Regresiva]
        CardTrivia[Componente: Trivia Interactiva]
        CardRsvp[Componente: RSVP & Pase VIP]
        CardOther[Otras 8 Tarjetas...]
    end

    subgraph Compiler[Motor de Renderizado: View.php]
        LayoutCompiler[Compilador de Layout: Deck Slider o Libro 3D]
    end

    DataContent --> Compiler
    ThemeSelection --> ThemeManifest --> Compiler
    FeatureToggles --> Compiler
    CustomTokens --> Compiler
    ThemeCSS --> Compiler
    ThemeSVGs --> Compiler

    ComponentRegistry --> Compiler

    Compiler --> OutputHTML[HTML5 Compilado + CSS Variables Inyectadas + Vanilla JS]
```

---

### 3. CONTRATO TÉCNICO DE PLANTILLA (`theme.json`)

Cada tema visual reside en `templates/themes/{theme_id}/` y contiene un archivo de especificación denominado `theme.json`:

```json
{
  "codigo": "rose-gold-princess",
  "nombre": "Rose Gold Princess",
  "version": "1.0.0",
  "descripcion": "Estética romántica de alta gala en tonos oro rosa, champán, polvo de estrellas y tiara de cristal.",
  "fuentes": {
    "script": {
      "nombre": "Great Vibes",
      "import_url": "https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap"
    },
    "serif": {
      "nombre": "Playfair Display",
      "import_url": "https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap"
    },
    "sans": {
      "nombre": "Montserrat",
      "import_url": "https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
    }
  },
  "tokens_color": {
    "--color-fondo-viewport": "#1C1418",
    "--color-tarjeta-bg": "rgba(42, 28, 35, 0.88)",
    "--color-primario": "#C98276",
    "--color-secundario": "#8E4A52",
    "--color-dorado-acento": "#E5A985",
    "--color-dorado-brillante": "#FFD9C0",
    "--color-texto-principal": "#FFF5F2",
    "--color-texto-suave": "#ECC5BA",
    "--color-pergamino": "#FBF5EE",
    "--color-borde-decorativo": "#D8998C"
  },
  "efectos": {
    "tipo_particulas": "estrellas_rosa_dorado",
    "volumen_mariposas": 4,
    "marco_portada_estilo": "filigrana_ovalada_real",
    "tiara_svg_variante": "tiara_princesa_delicada"
  },
  "componentes_por_defecto": {
    "card-00-cover": true,
    "card-01-story": true,
    "card-02-countdown": true,
    "card-03-blessing": true,
    "card-04-location": true,
    "card-05-dresscode": true,
    "card-06-itinerary": true,
    "card-07-trivia": true,
    "card-08-playlist": true,
    "card-09-gifts": true,
    "card-10-rsvp": true,
    "card-11-livephotos": true,
    "card-12-farewell": true
  }
}
```

---

### 4. COMPONENTIZACIÓN DEL DECK DE 13 TARJETAS

En la arquitectura actual, el archivo `public/index.php` contenía las 13 tarjetas pegadas en un solo bloque. El nuevo diseño desacopla cada tarjeta en un componente autónomo:

```text
templates/components/
├── card-00-cover.php         # Portada con Tiara SVG, foto principal y llamado a deslizar
├── card-01-story.php         # Historia "Érase una vez" con letra capital y mención familiar
├── card-02-countdown.php     # Contador regresivo interactivo con botón Google Calendar
├── card-03-blessing.php      # Bendición formal de padres y dedicatoria
├── card-04-location.php      # Lugar, fecha y botón GPS para Google Maps / Waze
├── card-05-dresscode.php     # Código de vestimenta y muestras circulares de colores sugeridos
├── card-06-itinerary.php     # Línea de tiempo visual del programa nocturno
├── card-07-trivia.php        # Juego interactivo de 5 preguntas con confeti y puntuación
├── card-08-playlist.php      # Formulario para sugerir canciones a la fiesta
├── card-09-gifts.php         # Lluvia de sobres, datos bancarios o buzón de recepción
├── card-10-rsvp.php          # Formulario RSVP, acompañantes dinámicos, Pase VIP QR y boletos rifa
├── card-11-livephotos.php    # Galería comunitaria, botón de cámara móvil y link a proyector
└── card-12-farewell.php      # Foto final de gala, firma caligráfica y agradecimiento
```

#### Mecanismo de Inclusión Condicional:
En el layout principal (`templates/layouts/invitation-deck.php`), las tarjetas se compilan en un bucle que valida la configuración del evento:

```php
<?php
// templates/layouts/invitation-deck.php
$slideIndex = 0;
foreach ($activeCards as $cardName => $isEnabled) {
    if (!$isEnabled) continue;
    $componentPath = __DIR__ . "/../components/{$cardName}.php";
    if (file_exists($componentPath)) {
        echo "<div class=\"card-slide " . ($slideIndex === 0 ? "active" : "hidden") . "\" data-slide=\"{$slideIndex}\">";
        include $componentPath;
        echo "</div>";
        $slideIndex++;
    }
}
?>
```
*Ventaja:* Si un cliente contrata el paquete básico sin trivia ni playlist, simplemente se define `"trivia": false` y `"playlist": false` en la base de datos. El motor omite esas tarjetas y recalcula automáticamente la paginación y los puntos de navegación (*nav dots*) sin errores de JavaScript.

---

### 5. SISTEMA DE TOKENS DE DISEÑO Y CSS VARIABLES

Para que el cambio de diseño sea instantáneo y no requiera compilar archivos Sass o Less en el servidor, el compilador inyecta un bloque `<style id="theme-tokens">` en el `<head>` del documento:

```html
<style id="theme-tokens">
:root {
    /* Fuentes dinámicas según plantilla */
    --font-script: <?= $theme['fuentes']['script']['nombre'] ?>, cursive;
    --font-serif: <?= $theme['fuentes']['serif']['nombre'] ?>, Georgia, serif;
    --font-sans: <?= $theme['fuentes']['sans']['nombre'] ?>, sans-serif;

    /* Tokens de color extraídos de la configuración del evento */
    <?php foreach ($tokensColor as $variable => $valorHex): ?>
        <?= $variable ?>: <?= htmlspecialchars($valorHex, ENT_QUOTES, 'UTF-8') ?>;
    <?php endforeach; ?>
}
</style>
```

#### Catálogo de Temas de Lanzamiento:
1. **Esmeralda Real (`esmeralda-real`):** El tema original de Angie (Verde noche, verde esmeralda y oro viejo).
2. **Rose Gold Princess (`rose-gold-princess`):** Tonos rosa cuarzo, oro rosado, champán y marfil (Tema del piloto de Sophia).
3. **Celestial Night (`celestial-night`):** Azul zafiro nocturno, plata brillante y acentos de luna helada.
4. **Golden Glamour (`golden-glamour`):** Negro azabache de gala profunda, destellos dorados intensos y estética de alfombra roja.
