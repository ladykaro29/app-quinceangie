# Invitación Digital XV Años - Angie Karolina Avendaño Rivera

Aplicación web completa para invitación digital de XV años con sistema RSVP, backend en PHP puro (PDO) y base de datos MySQL/MariaDB.

---

## 🎨 Especificaciones de Diseño
- **Paleta de Colores:**
  - Verde Oscuro: `#062E25`
  - Verde Esmeralda: `#006B4F`
  - Verde Claro: `#2F8F68`
  - Dorado Base: `#C8A24A`
  - Dorado Claro: `#E7D49A`
  - Crema: `#FFF8EC`
  - Marrón Oscuro: `#1A1410`
- **Tipografías:**
  - `Great Vibes` (Titulares en caligrafía)
  - `Playfair Display` (Subtítulos en serif elegante)
  - `Montserrat` (Cuerpo de texto y botones)

---

## 📁 Estructura del Proyecto

```text
/
├── .env.example          # Plantilla de variables de entorno
├── admin/
│   └── index.php         # Panel de administración (vista de invitados y exportación CSV)
├── api/
│   ├── confirmar.php     # Endpoint POST para guardar RSVP y acompañantes
│   └── invitados.php     # Endpoint GET para obtener la lista de confirmados
├── config/
│   └── database.php      # Configuración de conexión PDO a MySQL
├── database/
│   └── schema.sql        # Script de creación de tablas MySQL
├── public/
│   └── index.php         # Invitación digital interactiva con tarjetas deslizables
└── README.md             # Guía de instalación y uso
```

---

## 🚀 Requisitos Previos

- Servidor web con PHP 8.0 o superior (XAMPP, Laragon, WampServer o PHP CLI incorporado).
- Extensión `pdo_mysql` habilitada en PHP.
- MySQL 5.7+ / MariaDB 10.2+.

---

## ⚙️ Pasos para Levantar Localmente

### 1. Configurar la Base de Datos
1. Inicia tus servicios de MySQL y Apache en XAMPP/Laragon.
2. Abre phpMyAdmin o tu cliente MySQL (HeidiSQL, DBeaver, MySQL Workbench).
3. Importa o ejecuta el archivo `database/schema.sql`:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

### 2. Configurar Variables de Entorno
Copia el archivo `.env.example` como `.env` en la raíz del proyecto y ajusta tus datos:
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=invitacion_xv
DB_USER=root
DB_PASS=

ADMIN_USER=admin
ADMIN_PASS=angie2026

MAX_ACOMPANANTES=5
```

### 3. Ejecutar el Proyecto

#### Con XAMPP / Laragon:
Coloca la carpeta `InvitacionAngie` en tu directorio `htdocs` (XAMPP) o `www` (Laragon).
- **Invitación:** `http://localhost/InvitacionAngie/public/index.php`
- **Panel de Admin:** `http://localhost/InvitacionAngie/admin/index.php`

#### Con Servidor Embebido de PHP (Terminal):
En la raíz del proyecto ejecuta:
```bash
php -S localhost:8000 -t public
```
- **Invitación:** `http://localhost:8000/index.php`

---

## 🔒 Acceso al Panel de Administración

El panel `/admin/index.php` está protegido por autenticación HTTP Basic.
- **Usuario por defecto:** `admin`
- **Contraseña por defecto:** `angie2026`

---

## 📌 Funcionalidades Principales

1. **Deck de Tarjetas Deslizables:** 10 tarjetas con animación, swipe táctil y navegación con teclado.
2. **Cuenta Regresiva:** Calculada automáticamente hasta el 3 de octubre de 2026 a las 8:00 PM.
3. **Trivia Interactiva:** Juego de preguntas sobre Angie.
4. **Playlist interactiva:** Permite a los invitados añadir sugerencias de canciones.
5. **Módulo RSVP en tiempo real:**
   - Nombre completo obligatorio y sanitizado.
   - Selección "Sí asistiré" / "No podré asistir".
   - Si asiste, permite agregar hasta 5 acompañantes dinámicamente.
   - Envío asíncrono con `fetch()` (sin recargar la página).
   - Animación de destello dorado y partículas al confirmar.
   - Prevención de doble envío accidental.
6. **API REST JSON:** Endpoints seguros con sentencias preparadas (PDO) y transacciones.
7. **Exportación CSV:** Descarga directa de la lista de invitados para Excel con soporte UTF-8 BOM.
