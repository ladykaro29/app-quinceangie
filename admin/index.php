<?php
/**
 * Panel de Administración - Invitación XV Años
 * Lista de invitados confirmados con exportación CSV
 * Protegido por autenticación de sesión con clave privada
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Cerrar sesión
if (isset($_GET['logout'])) {
    $_SESSION['admin_auth'] = false;
    unset($_SESSION['admin_auth']);
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// Claves válidas (ADMIN_PASS desde .env/database.php y alternas como angie2026 / angie15)
$validPasswords = array_unique(array_filter([
    ADMIN_PASS,
    'angie2026',
    'angie15'
]));

// Procesar formulario de login
$loginError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $inputPass = trim($_POST['admin_pass'] ?? '');
    $inputUser = trim($_POST['admin_user'] ?? '');

    if (in_array($inputPass, $validPasswords, true) || 
        (!empty($inputUser) && $inputUser === ADMIN_USER && in_array($inputPass, $validPasswords, true))) {
        $_SESSION['admin_auth'] = true;
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    } else {
        $loginError = 'Clave incorrecta. Por favor verifica e intenta de nuevo.';
    }
}

// Compatibilidad con HTTP Basic (por si un cliente lo envía)
$isAuthenticated = !empty($_SESSION['admin_auth']);
if (!$isAuthenticated && isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
    if ($_SERVER['PHP_AUTH_USER'] === ADMIN_USER && in_array($_SERVER['PHP_AUTH_PW'], $validPasswords, true)) {
        $_SESSION['admin_auth'] = true;
        $isAuthenticated = true;
    }
}

// Si NO está autenticado, mostrar pantalla de inicio de sesión elegante
if (!$isAuthenticated) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso Administrador — XV Años Angie</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            :root {
                --verde-oscuro: #062E25;
                --verde-medio: #006B4F;
                --dorado: #C8A24A;
                --dorado-claro: #E7D49A;
                --crema: #FFF8EC;
            }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Montserrat', sans-serif;
                background: linear-gradient(135deg, #031c16 0%, #062E25 50%, #004D38 100%);
                color: var(--crema);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .login-card {
                background: rgba(6, 46, 37, 0.78);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                border: 2px solid var(--dorado);
                border-radius: 24px;
                padding: 40px 30px;
                width: 100%;
                max-width: 420px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.65), 0 0 35px rgba(200, 162, 74, 0.25);
                position: relative;
                text-align: center;
            }
            .corner-tl, .corner-tr, .corner-bl, .corner-br {
                position: absolute;
                width: 18px;
                height: 18px;
                border-color: var(--dorado);
                pointer-events: none;
            }
            .corner-tl { top: 8px; left: 8px; border-top: 2px solid; border-left: 2px solid; }
            .corner-tr { top: 8px; right: 8px; border-top: 2px solid; border-right: 2px solid; }
            .corner-bl { bottom: 8px; left: 8px; border-bottom: 2px solid; border-left: 2px solid; }
            .corner-br { bottom: 8px; right: 8px; border-bottom: 2px solid; border-right: 2px solid; }
            .crown-icon {
                font-size: 2.8rem;
                color: var(--dorado);
                margin-bottom: 10px;
                filter: drop-shadow(0 0 10px rgba(200, 162, 74, 0.6));
                animation: crownFloat 3s ease-in-out infinite alternate;
            }
            @keyframes crownFloat {
                from { transform: translateY(0); }
                to { transform: translateY(-6px); }
            }
            h1 {
                font-family: 'Great Vibes', cursive;
                font-size: 2.8rem;
                color: var(--dorado);
                line-height: 1.1;
                margin-bottom: 4px;
            }
            h2 {
                font-family: 'Playfair Display', serif;
                font-size: 0.82rem;
                letter-spacing: 3px;
                text-transform: uppercase;
                color: var(--dorado-claro);
                margin-bottom: 22px;
                font-weight: 500;
            }
            .error-box {
                background: rgba(180, 40, 40, 0.3);
                border: 1px solid #ff6b6b;
                color: #ffc9c9;
                padding: 10px 14px;
                border-radius: 10px;
                font-size: 0.85rem;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 8px;
                text-align: left;
            }
            .input-group {
                position: relative;
                margin-bottom: 22px;
                text-align: left;
            }
            .input-label {
                display: block;
                font-size: 0.78rem;
                color: var(--dorado-claro);
                text-transform: uppercase;
                letter-spacing: 1.5px;
                margin-bottom: 8px;
                font-weight: 600;
            }
            .input-wrapper {
                position: relative;
                display: flex;
                align-items: center;
            }
            .input-wrapper i.field-icon {
                position: absolute;
                left: 14px;
                color: var(--dorado);
                font-size: 1rem;
            }
            .input-field {
                width: 100%;
                padding: 13px 45px 13px 40px;
                border-radius: 12px;
                border: 1.5px solid rgba(200, 162, 74, 0.5);
                background: rgba(0, 0, 0, 0.45);
                color: #FFF;
                font-family: 'Montserrat', sans-serif;
                font-size: 1rem;
                transition: all 0.3s ease;
                outline: none;
                box-sizing: border-box;
            }
            .input-field:focus {
                border-color: var(--dorado);
                box-shadow: 0 0 15px rgba(200, 162, 74, 0.45);
                background: rgba(0, 0, 0, 0.65);
            }
            .toggle-pass-btn {
                position: absolute;
                right: 12px;
                background: none;
                border: none;
                color: var(--dorado-claro);
                cursor: pointer;
                font-size: 1rem;
                padding: 6px;
                opacity: 0.7;
                transition: opacity 0.2s;
            }
            .toggle-pass-btn:hover {
                opacity: 1;
            }
            .btn-submit {
                width: 100%;
                padding: 14px;
                border: none;
                border-radius: 25px;
                background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
                color: var(--verde-oscuro);
                font-family: 'Montserrat', sans-serif;
                font-size: 0.95rem;
                font-weight: 700;
                letter-spacing: 1.5px;
                text-transform: uppercase;
                cursor: pointer;
                box-shadow: 0 6px 20px rgba(200, 162, 74, 0.35);
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            .btn-submit:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(200, 162, 74, 0.55);
            }
            .btn-submit:active {
                transform: translateY(0);
            }
            .back-link {
                display: inline-block;
                margin-top: 24px;
                font-size: 0.8rem;
                color: var(--dorado-claro);
                text-decoration: none;
                opacity: 0.75;
                transition: all 0.2s;
            }
            .back-link:hover {
                opacity: 1;
                color: var(--dorado);
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="login-card">
            <div class="corner-tl"></div>
            <div class="corner-tr"></div>
            <div class="corner-bl"></div>
            <div class="corner-br"></div>

            <div class="crown-icon"><i class="fas fa-crown"></i></div>
            <h1>Mis XV Años</h1>
            <h2>Panel de Administración</h2>

            <?php if (!empty($loginError)): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($loginError) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="admin_login" value="1">
                <div class="input-group">
                    <label class="input-label" for="admin_pass">Clave de Acceso</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key field-icon"></i>
                        <input type="password" id="admin_pass" name="admin_pass" class="input-field" placeholder="Ingresa la clave..." required autofocus>
                        <button type="button" class="toggle-pass-btn" id="togglePass" title="Mostrar/ocultar clave">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-lock-open"></i> Ingresar al Panel
                </button>
            </form>

            <a href="../" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver a la Invitación
            </a>
        </div>

        <script>
            const toggleBtn = document.getElementById('togglePass');
            const passInput = document.getElementById('admin_pass');
            const eyeIcon = document.getElementById('eyeIcon');

            toggleBtn?.addEventListener('click', () => {
                const isPassword = passInput.type === 'password';
                passInput.type = isPassword ? 'text' : 'password';
                eyeIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}


// Exportar CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    try {
        $pdo = getDBConnection();
        populateMissingRaffleCodes($pdo);

        $stmt = $pdo->query(
            'SELECT i.id, i.nombre_completo, i.asistira, i.codigo_rifa, i.created_at,
                    GROUP_CONCAT(CONCAT(a.nombre_completo, " [", IFNULL(a.codigo_rifa, "N/A"), "]") SEPARATOR "; ") AS acomp_rifas,
                    COUNT(a.id) AS num_acompanantes
             FROM invitados i
             LEFT JOIN acompanantes a ON a.invitado_id = i.id
             GROUP BY i.id
             ORDER BY i.created_at DESC'
        );
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="invitados_rifa_xv_angie_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        // BOM for Excel UTF-8
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, ['ID', 'Nombre Titular', 'Asistirá', 'Boleto Rifa Titular', 'Acompañantes y Boletos Rifa', 'Núm. Acompañantes', 'Total Personas', 'Fecha Confirmación']);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['id'],
                $row['nombre_completo'],
                $row['asistira'] ? 'Sí' : 'No',
                $row['asistira'] ? ($row['codigo_rifa'] ?? 'Sin código') : 'N/A',
                $row['acomp_rifas'] ?? '',
                $row['num_acompanantes'],
                $row['asistira'] ? 1 + (int)$row['num_acompanantes'] : 0,
                $row['created_at']
            ]);
        }

        fclose($output);
        exit;
    } catch (\PDOException $e) {
        http_response_code(500);
        echo 'Error al exportar: ' . htmlspecialchars($e->getMessage());
        exit;
    }
}

// Obtener datos para la tabla y sorteo de rifa
try {
    $pdo = getDBConnection();
    populateMissingRaffleCodes($pdo);

    $stmtInvitados = $pdo->query(
        'SELECT id, nombre_completo, asistira, codigo_rifa, created_at 
         FROM invitados ORDER BY created_at DESC'
    );
    $invitados = $stmtInvitados->fetchAll();

    $stmtAcomp = $pdo->query(
        'SELECT id, invitado_id, nombre_completo, codigo_rifa FROM acompanantes ORDER BY id ASC'
    );
    $todosAcomp = $stmtAcomp->fetchAll();

    $acompPorInvitado = [];
    foreach ($todosAcomp as $a) {
        $acompPorInvitado[$a['invitado_id']][] = [
            'id'          => $a['id'],
            'nombre'      => $a['nombre_completo'],
            'codigo_rifa' => $a['codigo_rifa']
        ];
    }

    // Conteos y recopilación de todos los boletos de rifa activos
    $totalRegistros = count($invitados);
    $totalConfirmados = 0;
    $totalNoAsistiran = 0;
    $totalPersonas = 0;
    $todosBoletosRifa = [];

    foreach ($invitados as $inv) {
        $acomps = $acompPorInvitado[$inv['id']] ?? [];
        if ($inv['asistira']) {
            $totalConfirmados++;
            $totalPersonas += 1 + count($acomps);

            if (!empty($inv['codigo_rifa'])) {
                $todosBoletosRifa[] = [
                    'id'          => 'inv_' . $inv['id'],
                    'nombre'      => $inv['nombre_completo'],
                    'codigo_rifa' => $inv['codigo_rifa'],
                    'tipo'        => 'Titular',
                    'titular'     => $inv['nombre_completo']
                ];
            }

            foreach ($acomps as $ac) {
                if (!empty($ac['codigo_rifa'])) {
                    $todosBoletosRifa[] = [
                        'id'          => 'ac_' . $ac['id'],
                        'nombre'      => $ac['nombre'],
                        'codigo_rifa' => $ac['codigo_rifa'],
                        'tipo'        => 'Acompañante',
                        'titular'     => $inv['nombre_completo']
                    ];
                }
            }
        } else {
            $totalNoAsistiran++;
        }
    }

    $dbError = null;
} catch (\PDOException $e) {
    $dbError = $e->getMessage();
    $invitados = [];
    $acompPorInvitado = [];
    $todosBoletosRifa = [];
    $totalRegistros = $totalConfirmados = $totalNoAsistiran = $totalPersonas = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Invitados XV Años Angie</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    <style>
        :root {
            --verde-oscuro: #062E25;
            --verde-medio: #006B4F;
            --verde-claro: #2F8F68;
            --dorado: #C8A24A;
            --dorado-claro: #E7D49A;
            --crema: #FFF8EC;
            --marron: #1A1410;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--verde-oscuro) 0%, #0a3d30 50%, var(--verde-oscuro) 100%);
            color: var(--crema);
            min-height: 100vh;
            padding: 20px;
        }

        .admin-header {
            text-align: center;
            padding: 30px 20px;
            margin-bottom: 30px;
        }

        .admin-header h1 {
            font-family: 'Great Vibes', cursive;
            font-size: 2.5rem;
            color: var(--dorado);
            margin-bottom: 5px;
            text-shadow: 0 2px 10px rgba(200, 162, 74, 0.3);
        }

        .admin-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 0.9rem;
            color: var(--dorado-claro);
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 400;
        }

        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .stat-card {
            background: rgba(0, 107, 79, 0.2);
            border: 1px solid rgba(200, 162, 74, 0.2);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            border-color: var(--dorado);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(200, 162, 74, 0.15);
        }

        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--dorado);
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--dorado-claro);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 8px;
        }

        /* Actions */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: 2px solid var(--dorado);
            border-radius: 25px;
            background: transparent;
            color: var(--dorado);
            font-family: 'Montserrat', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            letter-spacing: 1px;
        }

        .action-btn:hover {
            background: var(--dorado);
            color: var(--verde-oscuro);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(200, 162, 74, 0.3);
        }

        .action-btn.primary {
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            border-color: var(--dorado);
        }

        /* Table */
        .table-container {
            max-width: 1200px;
            margin: 0 auto;
            overflow-x: auto;
            border-radius: 15px;
            border: 1px solid rgba(200, 162, 74, 0.2);
            background: rgba(6, 46, 37, 0.6);
            backdrop-filter: blur(10px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        thead {
            background: rgba(200, 162, 74, 0.1);
        }

        th {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--dorado);
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 15px 12px;
            text-align: left;
            border-bottom: 2px solid rgba(200, 162, 74, 0.2);
        }

        td {
            font-size: 0.85rem;
            padding: 14px 12px;
            border-bottom: 1px solid rgba(200, 162, 74, 0.08);
            vertical-align: top;
        }

        tr:hover td {
            background: rgba(200, 162, 74, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .badge-yes {
            background: rgba(47, 143, 104, 0.3);
            color: var(--verde-claro);
            border: 1px solid rgba(47, 143, 104, 0.4);
        }

        .badge-no {
            background: rgba(139, 69, 19, 0.2);
            color: #D4A26A;
            border: 1px solid rgba(139, 69, 19, 0.3);
        }

        .companion-list {
            list-style: none;
            padding: 0;
        }

        .companion-list li {
            font-size: 0.8rem;
            padding: 3px 0;
            color: var(--dorado-claro);
        }

        .companion-list li::before {
            content: '• ';
            color: var(--dorado);
        }

        /* Boletos de rifa en tabla */
        .raffle-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.5px;
            margin: 2px 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
        }

        .raffle-pill.titular {
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            color: var(--verde-oscuro);
            border: 1px solid var(--dorado-claro);
        }

        .raffle-pill.acomp {
            background: rgba(200, 162, 74, 0.15);
            color: var(--dorado-claro);
            border: 1px dashed var(--dorado);
        }

        /* Modal Sorteo de Rifa en Vivo */
        .raffle-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.82);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
            box-sizing: border-box;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .raffle-modal-window {
            background: linear-gradient(145deg, #062E25, #004D38);
            border: 2px solid var(--dorado);
            border-radius: 20px;
            max-width: 620px;
            width: 100%;
            padding: 26px 24px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.7), inset 0 0 30px rgba(200, 162, 74, 0.15);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            from { transform: scale(0.85); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .raffle-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(200, 162, 74, 0.3);
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .raffle-close-btn {
            background: transparent;
            border: none;
            color: var(--dorado);
            font-size: 1.8rem;
            cursor: pointer;
            line-height: 1;
            transition: transform 0.2s;
        }

        .raffle-close-btn:hover {
            transform: scale(1.2) rotate(90deg);
            color: #FFF;
        }

        .raffle-roulette-display {
            background: rgba(0, 0, 0, 0.4);
            border: 2px dashed var(--dorado);
            border-radius: 16px;
            padding: 25px 20px;
            text-align: center;
            margin-bottom: 15px;
            box-shadow: inset 0 0 25px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .raffle-ticket-anim-number {
            font-family: 'Courier New', monospace;
            font-size: 2.8rem;
            font-weight: 800;
            color: #FFD700;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
            letter-spacing: 2px;
            margin-bottom: 6px;
        }

        .raffle-ticket-anim-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: var(--crema);
            margin-bottom: 6px;
            min-height: 1.8rem;
        }

        .raffle-ticket-anim-meta {
            font-size: 0.8rem;
            color: var(--dorado-claro);
            opacity: 0.85;
        }

        .raffle-roulette-display.spinning .raffle-ticket-anim-number {
            animation: pulseFast 0.1s infinite alternate;
        }

        @keyframes pulseFast {
            from { transform: scale(0.98); opacity: 0.9; }
            to { transform: scale(1.02); opacity: 1; }
        }

        .raffle-winners-container {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(200, 162, 74, 0.25);
            border-radius: 12px;
            padding: 14px;
        }

        .winner-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(200, 162, 74, 0.1);
            border-left: 4px solid var(--dorado);
            font-size: 0.82rem;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--dorado-claro);
            opacity: 0.6;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--dorado);
            margin-bottom: 15px;
            opacity: 0.4;
        }

        .error-banner {
            max-width: 1200px;
            margin: 0 auto 20px;
            padding: 15px 20px;
            background: rgba(139, 69, 19, 0.2);
            border: 1px solid rgba(139, 69, 19, 0.4);
            border-radius: 10px;
            color: #D4A26A;
            font-size: 0.85rem;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            font-size: 0.7rem;
            color: var(--dorado-claro);
            opacity: 0.4;
        }

        @media (max-width: 768px) {
            body { padding: 10px; }
            .admin-header h1 { font-size: 2rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

    <div class="admin-top-bar" style="max-width: 1200px; margin: 0 auto 15px auto; display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; background: rgba(0,0,0,0.35); border-radius: 14px; border: 1px solid rgba(200, 162, 74, 0.25); flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px; color: var(--dorado-claro); font-size: 0.82rem;">
            <i class="fas fa-user-shield" style="color: var(--dorado); font-size: 1rem;"></i>
            <span>Administrador activo</span>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <a href="../" target="_blank" class="action-btn" style="padding: 6px 14px; font-size: 0.75rem;">
                <i class="fas fa-external-link-alt"></i> Ver Invitación
            </a>
            <a href="?logout=1" class="action-btn" style="padding: 6px 14px; font-size: 0.75rem; border-color: rgba(230, 90, 90, 0.6); color: #ff9999;">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>

    <div class="admin-header">
        <h1>Panel de Administración</h1>
        <h2>XV Años — Angie Karolina</h2>
    </div>

    <?php if ($dbError): ?>
        <div class="error-banner">
            <i class="fas fa-exclamation-triangle"></i>
            Error de conexión a la base de datos: <?= htmlspecialchars($dbError) ?>
        </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $totalRegistros ?></div>
            <div class="stat-label">Total Registros</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $totalConfirmados ?></div>
            <div class="stat-label">Confirman Asistencia</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $totalNoAsistiran ?></div>
            <div class="stat-label">No Asistirán</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $totalPersonas ?></div>
            <div class="stat-label">Personas Esperadas</div>
        </div>
        <div class="stat-card" style="border-color: rgba(255, 215, 0, 0.45); background: linear-gradient(145deg, rgba(200, 162, 74, 0.18), rgba(6, 46, 37, 0.8));">
            <div class="stat-number" style="color: #FFD700; text-shadow: 0 0 15px rgba(255, 215, 0, 0.4);"><?= count($todosBoletosRifa) ?></div>
            <div class="stat-label" style="color: #FFE680;">🎟️ Boletos en Rifa</div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="actions-bar">
        <div>
            <span style="font-size: 0.8rem; color: var(--dorado-claro); opacity: 0.6;">
                Última actualización: <?= date('d/m/Y H:i') ?>
            </span>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="action-btn" id="btnOpenRaffle" style="background: linear-gradient(135deg, #FFD700, #C8A24A); color: #062E25; font-weight: 700; border: none; cursor: pointer; box-shadow: 0 4px 15px rgba(200, 162, 74, 0.4);">
                <i class="fas fa-gift"></i> Sorteo de Rifa en Vivo
            </button>
            <a href="?export=csv" class="action-btn primary">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
            <a href="" class="action-btn">
                <i class="fas fa-sync-alt"></i> Actualizar
            </a>
        </div>
    </div>

    <!-- Buscador en tiempo real de invitados -->
    <div style="max-width: 1200px; margin: 0 auto 16px auto; position: relative;">
        <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--dorado); font-size: 0.95rem; pointer-events: none;"></i>
        <input type="text" id="guestSearchInput" placeholder="🔍 Buscar invitado por nombre, acompañante o código de rifa..." style="width: 100%; box-sizing: border-box; padding: 12px 18px 12px 45px; border-radius: 25px; border: 1.5px solid rgba(200, 162, 74, 0.35); background: rgba(6, 46, 37, 0.7); color: #FFF; font-family: 'Montserrat', sans-serif; font-size: 0.88rem; outline: none; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.25);">
    </div>

    <!-- Tabla de invitados -->
    <div class="table-container">
        <?php if (empty($invitados)): ?>
            <div class="empty-state">
                <i class="fas fa-user-friends"></i>
                <p>Aún no hay invitados registrados.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre Titular</th>
                        <th>¿Asistirá?</th>
                        <th>Boletos de Rifa</th>
                        <th>Acompañantes</th>
                        <th>Total Personas</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invitados as $i => $inv): 
                        $acomps = $acompPorInvitado[$inv['id']] ?? [];
                        $totalPersonasRow = $inv['asistira'] ? 1 + count($acomps) : 0;
                    ?>
                        <tr>
                            <td style="color: var(--dorado); opacity: 0.5;"><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($inv['nombre_completo']) ?></strong></td>
                            <td>
                                <?php if ($inv['asistira']): ?>
                                    <span class="badge badge-yes"><i class="fas fa-check"></i> Sí</span>
                                <?php else: ?>
                                    <span class="badge badge-no"><i class="fas fa-times"></i> No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($inv['asistira']): ?>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <?php if (!empty($inv['codigo_rifa'])): ?>
                                            <span class="raffle-pill titular" title="Boleto de <?= htmlspecialchars($inv['nombre_completo']) ?>">
                                                <i class="fas fa-ticket-alt"></i> <?= htmlspecialchars($inv['codigo_rifa']) ?>
                                                <span style="font-size: 0.65rem; opacity: 0.85;">(Titular)</span>
                                            </span>
                                        <?php endif; ?>
                                        <?php foreach ($acomps as $ac): ?>
                                            <?php if (!empty($ac['codigo_rifa'])): ?>
                                                <span class="raffle-pill acomp" title="Boleto de <?= htmlspecialchars($ac['nombre']) ?>">
                                                    <i class="fas fa-ticket-alt"></i> <?= htmlspecialchars($ac['codigo_rifa']) ?>
                                                    <span style="font-size: 0.65rem; opacity: 0.85;">(<?= htmlspecialchars($ac['nombre']) ?>)</span>
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="opacity: 0.4; font-size: 0.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($acomps)): ?>
                                    <ul class="companion-list">
                                        <?php foreach ($acomps as $ac): ?>
                                            <li><?= htmlspecialchars($ac['nombre']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span style="opacity: 0.4; font-size: 0.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <strong style="color: var(--dorado);"><?= $totalPersonasRow ?></strong>
                            </td>
                            <td class="date-cell">
                                <?= date('d/m/Y', strtotime($inv['created_at'])) ?><br>
                                <?= date('H:i', strtotime($inv['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="footer">
        Panel de Administración — XV Años Angie Karolina Avendaño Rivera — 2026
    </div>

    <!-- ============================================================
         MODAL SORTEO DE RIFA EN VIVO
         ============================================================ -->
    <div id="raffleModal" class="raffle-modal-backdrop" style="display: none;">
        <div class="raffle-modal-window">
            <div class="raffle-modal-header">
                <div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--dorado); margin: 0;">🎁 Sorteo de Rifa de Regalos</h3>
                    <div style="font-size: 0.8rem; color: var(--dorado-claro); opacity: 0.85;">XV Años — Angie Karolina Avendaño Rivera</div>
                </div>
                <button type="button" class="raffle-close-btn" id="btnCloseRaffle" title="Cerrar">&times;</button>
            </div>

            <div class="raffle-modal-body">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; color: var(--dorado-claro); margin-bottom: 6px; font-weight: 600;">
                        <i class="fas fa-gift"></i> Regalo o Premio a sortear:
                    </label>
                    <input type="text" id="rafflePrizeInput" placeholder="Ej. Premio Sorpresa #1, Perfume, etc." value="Premio Especial #1" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--dorado); background: rgba(0,0,0,0.45); color: var(--crema); font-family: 'Montserrat', sans-serif; font-size: 0.9rem;">
                </div>

                <!-- Ruleta / Pantalla animada -->
                <div class="raffle-roulette-display" id="raffleRouletteBox">
                    <div class="raffle-ticket-anim-number" id="raffleDisplayNumber">🎟️ ????</div>
                    <div class="raffle-ticket-anim-name" id="raffleDisplayName">Presiona el botón para sortear</div>
                    <div class="raffle-ticket-anim-meta" id="raffleDisplayMeta">Boletos participantes: <?= count($todosBoletosRifa) ?></div>
                </div>

                <div style="text-align: center; margin: 20px 0;">
                    <button type="button" class="action-btn primary" id="btnSpinRaffle" style="padding: 14px 36px; font-size: 1.05rem; border-radius: 30px; box-shadow: 0 6px 25px rgba(200, 162, 74, 0.5); cursor: pointer;">
                        <i class="fas fa-dice"></i> ¡GIRAR RULETA Y SORTEAR!
                    </button>
                </div>

                <!-- Ganadores sorteados -->
                <div class="raffle-winners-container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h4 style="font-size: 0.82rem; color: var(--dorado); margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                            🏆 Ganadores de la Noche (<span id="raffleWinnersCount">0</span>)
                        </h4>
                        <button type="button" id="btnClearWinners" style="background: none; border: none; color: #D4A26A; font-size: 0.72rem; cursor: pointer; text-decoration: underline;">
                            Reiniciar sorteos
                        </button>
                    </div>
                    <div id="raffleWinnersList" style="display: flex; flex-direction: column; gap: 8px; max-height: 180px; overflow-y: auto;">
                        <div id="raffleEmptyWinners" style="color: var(--dorado-claro); opacity: 0.5; font-size: 0.8rem; text-align: center; padding: 10px 0;">
                            Aún no se ha realizado ningún sorteo.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script del Sorteo de Rifa en Vivo -->
    <script>
    (() => {
        const allTickets = <?= json_encode($todosBoletosRifa, JSON_UNESCAPED_UNICODE) ?>;
        const modal = document.getElementById('raffleModal');
        const openBtn = document.getElementById('btnOpenRaffle');
        const closeBtn = document.getElementById('btnCloseRaffle');
        const spinBtn = document.getElementById('btnSpinRaffle');
        const prizeInput = document.getElementById('rafflePrizeInput');
        const rouletteBox = document.getElementById('raffleRouletteBox');
        const numDisplay = document.getElementById('raffleDisplayNumber');
        const nameDisplay = document.getElementById('raffleDisplayName');
        const metaDisplay = document.getElementById('raffleDisplayMeta');
        const winnersList = document.getElementById('raffleWinnersList');
        const winnersCount = document.getElementById('raffleWinnersCount');
        const emptyWinnersMsg = document.getElementById('raffleEmptyWinners');
        const clearWinnersBtn = document.getElementById('btnClearWinners');

        let isSpinning = false;
        let drawnWinners = [];

        // Sintetizador de sonido con Web Audio API (no requiere archivos externos)
        const audioCtx = (window.AudioContext || window.webkitAudioContext) ? new (window.AudioContext || window.webkitAudioContext)() : null;

        function playTick() {
            if (!audioCtx) return;
            try {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(450 + Math.random() * 200, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.04);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.05);
            } catch (e) {}
        }

        function playFanfare() {
            if (!audioCtx) return;
            try {
                const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
                notes.forEach((freq, index) => {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, audioCtx.currentTime + index * 0.1);
                    gain.gain.setValueAtTime(0.15, audioCtx.currentTime + index * 0.1);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + index * 0.1 + 0.35);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(audioCtx.currentTime + index * 0.1);
                    osc.stop(audioCtx.currentTime + index * 0.1 + 0.4);
                });
            } catch (e) {}
        }

        // Abrir y cerrar modal
        openBtn?.addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        closeBtn?.addEventListener('click', () => {
            if (!isSpinning) modal.style.display = 'none';
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal && !isSpinning) {
                modal.style.display = 'none';
            }
        });

        // Girar ruleta
        spinBtn?.addEventListener('click', () => {
            if (isSpinning) return;
            if (allTickets.length === 0) {
                alert('No hay boletos de rifa disponibles porque aún no hay invitados confirmados.');
                return;
            }

            // Filtrar boletos que aún no hayan ganado
            const availableTickets = allTickets.filter(t => !drawnWinners.some(w => w.ticket.codigo_rifa === t.codigo_rifa));

            if (availableTickets.length === 0) {
                alert('¡Todos los boletos participantes ya han ganado un premio!');
                return;
            }

            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }

            isSpinning = true;
            spinBtn.disabled = true;
            spinBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sorteando...';
            rouletteBox.classList.add('spinning');

            const prizeName = prizeInput.value.trim() || `Premio #${drawnWinners.length + 1}`;
            const spinDuration = 3200; // 3.2 segundos
            const startTime = Date.now();
            let speed = 60;

            function shuffleStep() {
                const randomItem = availableTickets[Math.floor(Math.random() * availableTickets.length)];
                numDisplay.textContent = randomItem.codigo_rifa;
                nameDisplay.textContent = randomItem.nombre;
                metaDisplay.textContent = `${randomItem.tipo} · Registrado con ${randomItem.titular}`;
                playTick();

                const elapsed = Date.now() - startTime;
                if (elapsed < spinDuration) {
                    speed = 60 + Math.floor((elapsed / spinDuration) * 200);
                    setTimeout(shuffleStep, speed);
                } else {
                    // Seleccionar ganador final
                    finalizeWinner(availableTickets, prizeName);
                }
            }

            shuffleStep();
        });

        function finalizeWinner(pool, prizeName) {
            const winner = pool[Math.floor(Math.random() * pool.length)];

            numDisplay.textContent = '🎉 ' + winner.codigo_rifa;
            nameDisplay.textContent = winner.nombre;
            metaDisplay.innerHTML = `<strong style="color: #FFD700; font-size: 0.95rem;">¡GANADOR(A) DE: ${escapeHtml(prizeName)}!</strong>`;

            rouletteBox.classList.remove('spinning');
            spinBtn.disabled = false;
            spinBtn.innerHTML = '<i class="fas fa-dice"></i> Sortear Siguiente Premio';
            isSpinning = false;

            // Guardar ganador
            const winRecord = {
                prize: prizeName,
                ticket: winner,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            };
            drawnWinners.unshift(winRecord);
            renderWinners();

            // Sonido y confeti triunfal
            playFanfare();
            if (typeof confetti === 'function') {
                confetti({
                    particleCount: 80,
                    spread: 70,
                    origin: { y: 0.6 },
                    colors: ['#FFD700', '#C8A24A', '#2F8F68', '#FFFDF5', '#E7D49A']
                });
                setTimeout(() => {
                    confetti({
                        particleCount: 50,
                        angle: 60,
                        spread: 55,
                        origin: { x: 0 },
                        colors: ['#FFD700', '#C8A24A']
                    });
                    confetti({
                        particleCount: 50,
                        angle: 120,
                        spread: 55,
                        origin: { x: 1 },
                        colors: ['#2F8F68', '#FFFDF5']
                    });
                }, 300);
            }

            // Preparar siguiente sugerencia de premio
            prizeInput.value = `Premio Especial #${drawnWinners.length + 1}`;
        }

        function renderWinners() {
            winnersCount.textContent = drawnWinners.length;
            if (drawnWinners.length === 0) {
                if (emptyWinnersMsg) emptyWinnersMsg.style.display = 'block';
                return;
            }
            if (emptyWinnersMsg) emptyWinnersMsg.style.display = 'none';

            winnersList.innerHTML = '';
            drawnWinners.forEach((w, idx) => {
                const item = document.createElement('div');
                item.className = 'winner-item';
                item.innerHTML = `
                    <div>
                        <div style="font-weight: 700; color: #FFD700; font-size: 0.85rem;">
                            <i class="fas fa-trophy"></i> ${escapeHtml(w.prize)}
                        </div>
                        <div style="color: var(--crema); font-size: 0.82rem;">
                            <strong>${escapeHtml(w.ticket.nombre)}</strong>
                            <span style="opacity: 0.7; font-size: 0.75rem;">(${escapeHtml(w.ticket.tipo)})</span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span class="raffle-pill titular">${escapeHtml(w.ticket.codigo_rifa)}</span>
                        <div style="font-size: 0.68rem; color: var(--dorado-claro); opacity: 0.7;">${w.time}</div>
                    </div>
                `;
                winnersList.appendChild(item);
            });
        }

        clearWinnersBtn?.addEventListener('click', () => {
            if (confirm('¿Deseas reiniciar la lista de ganadores del sorteo?')) {
                drawnWinners = [];
                renderWinners();
                numDisplay.textContent = '🎟️ ????';
                nameDisplay.textContent = 'Presiona el botón para sortear';
                metaDisplay.textContent = `Boletos participantes: ${allTickets.length}`;
            }
        });

        // Filtro en tiempo real de la tabla de invitados
        const guestSearchInput = document.getElementById('guestSearchInput');
        guestSearchInput?.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str || '';
            return div.innerHTML;
        }
    })();
    </script>
</body>
</html>
