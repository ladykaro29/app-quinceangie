# PROTOCOLO DE RESTAURACIÓN Y VERIFICACIÓN — SNAPSHOT DE ANGIE KAROLINA

> **PROPÓSITO:** Garantizar que toda la información, base de datos, dedicatorias, fotografías, videos, configuraciones y textos del evento original de **Angie Karolina** permanezcan 100% seguros, inmutables y restaurables en cualquier instante durante la evolución del proyecto hacia **XV Experience**.

---

## 1. INVENTARIO DE ARCHIVOS DE RESPALDO

Todos los activos críticos están resguardados en el directorio local:
`BACKUP_ANGIE_SNAPSHOT/`

| Archivo | Tipo / Formato | Contenido | Propósito |
| :--- | :--- | :--- | :--- |
| [`invitacion_xv.sqlite`](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/BACKUP_ANGIE_SNAPSHOT/invitacion_xv.sqlite) | Binario SQLite | Copia idéntica de la base de datos de producción (65.5 KB) | Restauración física inmediata |
| [`dump_sqlite.sql`](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/BACKUP_ANGIE_SNAPSHOT/dump_sqlite.sql) | SQL Script | DDL (tablas, índices) + `INSERT` de todos los registros | Migración de datos o reinserción limpia |
| [`dump_sqlite_data.json`](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/BACKUP_ANGIE_SNAPSHOT/dump_sqlite_data.json) | JSON | 160 registros de `fotos_fiesta`, 5 de `libro_recuerdos`, schema | Importación mediante scripts o seeders |
| [`angie_full_profile.json`](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/BACKUP_ANGIE_SNAPSHOT/angie_full_profile.json) | JSON | Perfil integral: fechas, lugar, familia, 6 pasos itinerario, 5 preguntas trivia, 19 videos, paleta visual | Seeder del Tenant Angie en XV Experience |
| [`checksums_sha256.json`](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/BACKUP_ANGIE_SNAPSHOT/checksums_sha256.json) | JSON Hashes | 184 hashes criptográficos SHA-256 de base de datos, fotos y videos | Verificación de integridad bit a bit |
| [`respaldo_angie_critico.zip`](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/BACKUP_ANGIE_SNAPSHOT/respaldo_angie_critico.zip) | ZIP Comprimido | Paquete autocontenido con los archivos anteriores | Backup offline comprimido |

---

## 2. PROCEDIMIENTO DE RESTAURACIÓN RÁPIDA (En caso de emergencia)

Si durante cualquier etapa de refactorización o migración de base de datos se alteraran los datos originales, ejecutar cualquiera de las siguientes opciones:

### Opción A: Restauración Física del Archivo SQLite (3 segundos)
En la consola de Windows (PowerShell):
```powershell
Copy-Item -Path "BACKUP_ANGIE_SNAPSHOT/invitacion_xv.sqlite" -Destination "database/invitacion_xv.sqlite" -Force
```

### Opción B: Restauración mediante Script SQL (Vía PHP)
```bash
php -r "$pdo = new PDO('sqlite:database/invitacion_xv.sqlite'); $pdo->exec(file_get_contents('BACKUP_ANGIE_SNAPSHOT/dump_sqlite.sql'));"
```

---

## 3. VERIFICACIÓN DE INTEGRIDAD CRIPTOGRÁFICA (SHA-256)

Para comprobar que ningún archivo multimedia o registro ha sido modificado o corrompido, ejecutar:
```bash
php -r "
$chk = json_decode(file_get_contents('BACKUP_ANGIE_SNAPSHOT/checksums_sha256.json'), true);
$ok = 0; $fails = 0;
foreach($chk as $file => $meta) {
    if(!file_exists($file) || hash_file('sha256', $file) !== $meta['sha256']) {
        echo 'ALERTA: Modificado o ausente -> ' . $file . PHP_EOL;
        $fails++;
    } else {
        $ok++;
    }
}
echo 'Comprobación finalizada: ' . $ok . ' archivos intactos, ' . $fails . ' con discrepancias.' . PHP_EOL;
"
```

---

## 4. INTEGRACIÓN DE ANGIE EN LA NUEVA ARQUITECTURA "XV EXPERIENCE"

En la plataforma multi-celebración:
- **Evento ID:** `1`
- **Slug oficial:** `angie-xv`
- **Dominio / Ruta:** `/e/angie-xv/` o `angie.xvexperience.com`
- **Plantilla asignada:** `emerald-prestige` (conserva el verde esmeralda y acentos dorados originales)
- **Seeder asignado:** Utilizará [`angie_full_profile.json`](file:///c:/Users/troni/Documents/Descarga-Antigravity/InvitacionAngie/BACKUP_ANGIE_SNAPSHOT/angie_full_profile.json) para poblar la tabla `eventos`, `configuraciones_evento`, `trivia_preguntas` e `itinerario_eventos`.
