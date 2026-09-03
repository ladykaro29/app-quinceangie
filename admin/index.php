<?php
/**
 * Panel de Administración - Invitación XV Años
 * Lista de invitados confirmados con exportación CSV
 * Protegido por autenticación HTTP Basic
 */

require_once __DIR__ . '/../config/database.php';

// Autenticación HTTP Basic
if (!isset($_SERVER['PHP_AUTH_USER']) ||
    $_SERVER['PHP_AUTH_USER'] !== ADMIN_USER ||
    $_SERVER['PHP_AUTH_PW'] !== ADMIN_PASS) {
    header('WWW-Authenticate: Basic realm="Panel de Administración - XV Años Angie"');
    header('HTTP/1.0 401 Unauthorized');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Acceso Denegado</title></head>
    <body style="background:#062E25;color:#FFF8EC;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;">
    <div style="text-align:center;"><h1 style="color:#C8A24A;">🔒 Acceso Denegado</h1><p>Credenciales incorrectas.</p></div></body></html>';
    exit;
}

// Exportar CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->query(
            'SELECT i.id, i.nombre_completo, i.asistira, i.created_at,
                    GROUP_CONCAT(a.nombre_completo SEPARATOR \'; \') AS acompanantes_nombres,
                    COUNT(a.id) AS num_acompanantes
             FROM invitados i
             LEFT JOIN acompanantes a ON a.invitado_id = i.id
             GROUP BY i.id
             ORDER BY i.created_at DESC'
        );
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="invitados_xv_angie_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        // BOM for Excel UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        fputcsv($output, ['ID', 'Nombre Completo', 'Asistirá', 'Acompañantes', 'Núm. Acompañantes', 'Total Personas', 'Fecha Confirmación']);
        
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['id'],
                $row['nombre_completo'],
                $row['asistira'] ? 'Sí' : 'No',
                $row['acompanantes_nombres'] ?? '',
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

// Obtener datos para la tabla
try {
    $pdo = getDBConnection();
    
    $stmtInvitados = $pdo->query(
        'SELECT id, nombre_completo, asistira, created_at 
         FROM invitados ORDER BY created_at DESC'
    );
    $invitados = $stmtInvitados->fetchAll();

    $stmtAcomp = $pdo->query(
        'SELECT invitado_id, nombre_completo FROM acompanantes ORDER BY id ASC'
    );
    $todosAcomp = $stmtAcomp->fetchAll();

    $acompPorInvitado = [];
    foreach ($todosAcomp as $a) {
        $acompPorInvitado[$a['invitado_id']][] = $a['nombre_completo'];
    }

    // Conteos
    $totalRegistros = count($invitados);
    $totalConfirmados = 0;
    $totalNoAsistiran = 0;
    $totalPersonas = 0;

    foreach ($invitados as $inv) {
        if ($inv['asistira']) {
            $totalConfirmados++;
            $totalPersonas += 1 + count($acompPorInvitado[$inv['id']] ?? []);
        } else {
            $totalNoAsistiran++;
        }
    }

    $dbError = null;
} catch (\PDOException $e) {
    $dbError = $e->getMessage();
    $invitados = [];
    $acompPorInvitado = [];
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

        .date-cell {
            font-size: 0.75rem;
            color: var(--dorado-claro);
            opacity: 0.7;
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
            <div class="stat-label">Total Personas Esperadas</div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="actions-bar">
        <div>
            <span style="font-size: 0.8rem; color: var(--dorado-claro); opacity: 0.6;">
                Última actualización: <?= date('d/m/Y H:i') ?>
            </span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="?export=csv" class="action-btn primary">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
            <a href="" class="action-btn">
                <i class="fas fa-sync-alt"></i> Actualizar
            </a>
        </div>
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
                        <th>Nombre Completo</th>
                        <th>¿Asistirá?</th>
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
                                <?php if (!empty($acomps)): ?>
                                    <ul class="companion-list">
                                        <?php foreach ($acomps as $acomp): ?>
                                            <li><?= htmlspecialchars($acomp) ?></li>
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

</body>
</html>
