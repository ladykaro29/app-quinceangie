# FASE 2: GENERADOR QR Y TARJETA IMPRIMIBLE

---

## 1. Tarjeta Imprimible de Mesa (`public/tarjeta-qr.php`)

Para que los invitados compartan sus fotografías de forma espontánea, se diseñó una tarjeta física con código QR lista para imprimir y colocar sobre las mesas del salón:

### A. Especificaciones Físicas y de Impresión:
- **Formatos Compatibles:** 10 × 15 cm (estándar fotografía) y formato postal A6 (105 × 148 mm).
- **Proporción de Aspecto:** 2:3 vertical.
- **Zona de Silencio del QR:** Mínimo 4 módulos de borde blanco alrededor de los patrones de alineación para garantizar lectura instantánea incluso con iluminación tenue de discoteca.
- **Estilos CSS `@media print`:** Al presionar `Ctrl + P` o el botón "Imprimir Tarjeta", el navegador oculta automáticamente las barras de control y centra la tarjeta con márgenes de corte exactos.

---

## 2. Identidades Visuales Disponibles

### A. Sophia Valentina Rivas Sánchez (`/tarjeta-qr/sophia-xv`)
- **Paleta:** Morado profundo (`#4A2645`), lavanda, dorado champagne (`#C8A24A`) y fondo crema pergamino (`#FFFDF9`).
- **Motivo Gráfico:** Iconografía de mariposa en las esquinas decorativas.
- **Destino del QR:** `https://[dominio]/subir-fotos/sophia-xv`.

### B. Angie Karolina (`/tarjeta-qr/angie-xv`)
- **Paleta:** Verde esmeralda profundo (`#062E25`), dorado imperial (`#FFD700`) y bordes ornamentales.
- **Motivo Gráfico:** Corona real de quinceañera.
- **Destino del QR:** `https://[dominio]/subir-fotos/angie-xv`.

---

## 3. Mensaje Oficial Impreso

> ### Comparte tus Recuerdos
> Escanea el código QR y comparte las fotografías que captures durante esta noche tan especial.  
> Cada fotografía será parte de nuestros recuerdos.  
> *✨ Se proyectarán en la pantalla de la fiesta ✨*
