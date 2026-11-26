<?php
/**
 * Centro de Moderación Multimedia y Álbum Colaborativo
 * Plataforma Multi-Celebración — XV Experience
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/autoload.php';

use App\Services\AuthService;
use App\Services\StorageService;
use App\Core\Database;

// Verificación de Autenticación
$isAuthenticated = !empty($_SESSION['admin_auth']) || AuthService::check();
if (!$isAuthenticated) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();
if (class_exists('App\Core\DatabaseMigrationService')) {
    \App\Core\DatabaseMigrationService::ensureMigrated($pdo);
}

// 1. Obtener lista de celebraciones disponibles
$stmtEventos = $pdo->query("SELECT id, slug, festejada_nombre, nombre_celebracion, activo FROM eventos ORDER BY id ASC");
$eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

// 2. Celebración activa seleccionada
$activeEventoId = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : (int)($_SESSION['active_evento_id'] ?? 2);
if ($activeEventoId <= 0) $activeEventoId = 1;
$_SESSION['active_evento_id'] = $activeEventoId;

$currentEvento = null;
foreach ($eventos as $ev) {
    if ((int)$ev['id'] === $activeEventoId) {
        $currentEvento = $ev;
        break;
    }
}
if (!$currentEvento && !empty($eventos)) {
    $currentEvento = $eventos[0];
    $activeEventoId = (int)$currentEvento['id'];
}

// 3. Pestaña de filtro
$tab = $_GET['tab'] ?? 'pendientes'; // pendientes, aprobadas, rechazadas, destacadas, todas

// 4. Estadísticas del evento
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = :ev");
$stmtTotal->execute([':ev' => $activeEventoId]);
$totalFotos = (int)$stmtTotal->fetchColumn();

$stmtPend = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = :ev AND (estado = 'pendiente' OR (aprobada = 0 AND visible = 0 AND estado != 'rechazada'))");
$stmtPend->execute([':ev' => $activeEventoId]);
$totalPend = (int)$stmtPend->fetchColumn();

$stmtAprob = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = :ev AND aprobada = 1 AND visible = 1");
$stmtAprob->execute([':ev' => $activeEventoId]);
$totalAprob = (int)$stmtAprob->fetchColumn();

$stmtRech = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = :ev AND estado = 'rechazada'");
$stmtRech->execute([':ev' => $activeEventoId]);
$totalRech = (int)$stmtRech->fetchColumn();

$stmtDest = $pdo->prepare("SELECT COUNT(*) FROM fotos_fiesta WHERE evento_id = :ev AND es_destacada = 1");
$stmtDest->execute([':ev' => $activeEventoId]);
$totalDest = (int)$stmtDest->fetchColumn();

$storageUsage = StorageService::getEventStorageUsage($activeEventoId);

// 5. Consulta de fotos filtradas
$where = "evento_id = :ev";
if ($tab === 'pendientes') {
    $where .= " AND (estado = 'pendiente' OR (aprobada = 0 AND visible = 0 AND estado != 'rechazada'))";
} elseif ($tab === 'aprobadas') {
    $where .= " AND aprobada = 1 AND visible = 1";
} elseif ($tab === 'rechazadas') {
    $where .= " AND estado = 'rechazada'";
} elseif ($tab === 'destacadas') {
    $where .= " AND es_destacada = 1";
}

$stmtList = $pdo->prepare("SELECT * FROM fotos_fiesta WHERE {$where} ORDER BY id DESC LIMIT 200");
$stmtList->execute([':ev' => $activeEventoId]);
$fotos = $stmtList->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Moderación Multimedia · XV Experience</title>
    <!-- Favicon Corona Real -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='30' fill='%23062E25' stroke='%23C8A24A' stroke-width='2.5'/%3E%3Cpath d='M13 43 L17 24 L24 33 L32 15 L40 33 L47 24 L51 43 Z' fill='%23FFD700' stroke='%23FFF0BA' stroke-width='0.8'/%3E%3Cpath d='M13 43 Q32 47 51 43 L51 47 Q32 51 13 47 Z' fill='%23C8A24A'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-body: #071512;
            --bg-card: #0D231E;
            --bg-card-hover: #12302A;
            --gold: #C8A24A;
            --gold-bright: #FFD700;
            --gold-light: #FFF0BA;
            --emerald: #062E25;
            --text-main: #FFFFFF;
            --text-sub: #A8BDB8;
            --border-card: rgba(200, 162, 74, 0.25);
            --color-success: #2ecc71;
            --color-danger: #e74c3c;
            --color-warning: #f39c12;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg-body);
            color: var(--text-main);
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            padding: 24px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-card);
        }

        .brand-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-title h1 {
            font-family: 'Cinzel Decorative', serif;
            font-size: 1.45rem;
            color: var(--gold-bright);
        }

        .brand-title span {
            font-size: 0.8rem;
            color: var(--text-sub);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-nav {
            background: rgba(200, 162, 74, 0.15);
            border: 1px solid var(--gold);
            color: var(--gold-light);
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-nav:hover {
            background: var(--gold);
            color: #000;
        }

        /* Selector de Celebraciones */
        .celebration-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            padding: 6px 14px;
            border-radius: 10px;
        }

        .celebration-selector select {
            background: transparent;
            border: none;
            color: var(--gold-bright);
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            outline: none;
            cursor: pointer;
        }

        .celebration-selector select option {
            background: var(--bg-card);
            color: #fff;
        }

        /* Grid de KPIs */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .kpi-val { font-size: 1.4rem; font-weight: 700; color: #FFF; }
        .kpi-lbl { font-size: 0.75rem; text-transform: uppercase; color: var(--text-sub); letter-spacing: 0.5px; }

        /* Barra de Pestañas y Acciones en Lote */
        .controls-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            padding: 10px 16px;
            border-radius: 12px;
        }

        .tabs-list {
            display: flex;
            gap: 8px;
            overflow-x: auto;
        }

        .tab-btn {
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-sub);
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tab-btn.active {
            background: rgba(200, 162, 74, 0.2);
            border-color: var(--gold);
            color: var(--gold-bright);
            font-weight: 600;
        }

        .batch-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-action {
            padding: 7px 12px;
            border-radius: 6px;
            border: none;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: opacity 0.2s;
        }

        .btn-action:disabled { opacity: 0.4; cursor: not-allowed; }
        .btn-approve { background: var(--color-success); color: #000; }
        .btn-reject { background: var(--color-danger); color: #FFF; }

        /* Grid de Fotografías */
        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }

        .photo-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .photo-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            border-color: var(--gold);
        }

        .photo-img-wrap {
            position: relative;
            aspect-ratio: 4/3;
            background: #000;
            cursor: pointer;
        }

        .photo-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-checkbox {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 20px;
            height: 20px;
            accent-color: var(--gold-bright);
            cursor: pointer;
            z-index: 2;
        }

        .badge-star {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0,0,0,0.6);
            color: var(--gold-bright);
            padding: 4px 8px;
            border-radius: 50px;
            font-size: 0.75rem;
            cursor: pointer;
            border: 1px solid var(--gold);
        }

        .photo-body {
            padding: 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .guest-name { font-weight: 600; font-size: 0.9rem; color: var(--gold-light); margin-bottom: 4px; }
        .guest-msg { font-size: 0.8rem; color: var(--text-sub); line-height: 1.3; font-style: italic; margin-bottom: 8px; }
        .photo-meta-tech { font-size: 0.7rem; color: rgba(255, 255, 255, 0.4); margin-bottom: 10px; display: flex; justify-content: space-between; }

        .photo-actions {
            display: flex;
            gap: 6px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 10px;
        }

        .btn-card-action {
            flex: 1;
            padding: 6px 8px;
            border-radius: 6px;
            border: none;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .btn-card-approve { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71; }
        .btn-card-approve:hover { background: #2ecc71; color: #000; }
        .btn-card-reject { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }
        .btn-card-reject:hover { background: #e74c3c; color: #fff; }
        .btn-card-del { background: rgba(255, 255, 255, 0.1); color: var(--text-sub); width: 32px; flex: none; }
        .btn-card-del:hover { background: #e74c3c; color: #fff; }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px dashed var(--border-card);
        }

        .empty-state i { font-size: 3rem; color: var(--gold); margin-bottom: 12px; }
        .empty-state h3 { font-size: 1.2rem; color: var(--gold-light); margin-bottom: 6px; }
        .empty-state p { color: var(--text-sub); font-size: 0.9rem; }

        /* Modal Lightbox */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-card {
            background: var(--bg-card);
            border: 1px solid var(--gold);
            border-radius: 16px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            display: flex;
            overflow: hidden;
            box-shadow: 0 16px 40px rgba(0,0,0,0.8);
        }

        @media (max-width: 768px) {
            .modal-card { flex-direction: column; }
        }

        .modal-img-wrap {
            flex: 1.5;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-img-wrap img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }

        .modal-info {
            flex: 1;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: auto;
        }
    </style>
</head>
<body>

    <header class="top-bar">
        <div class="brand-title">
            <i class="fas fa-crown" style="font-size: 1.8rem; color: var(--gold);"></i>
            <div>
                <h1>Centro de Moderación Multimedia</h1>
                <span>XV Experience · Panel Administrativo</span>
            </div>
        </div>

        <div class="nav-actions">
            <div class="celebration-selector">
                <i class="fas fa-calendar-check" style="color: var(--gold);"></i>
                <select id="selectEvento" onchange="window.location.href='?evento_id=' + this.value + '&tab=<?= $tab ?>'">
                    <?php foreach ($eventos as $ev): ?>
                        <option value="<?= $ev['id'] ?>" <?= ((int)$ev['id'] === $activeEventoId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ev['festejada_nombre']) ?> (<?= htmlspecialchars($ev['slug']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <a href="index.php" class="btn-nav">
                <i class="fas fa-users"></i> Panel Invitados
            </a>
            <a href="../en-vivo?evento=<?= urlencode($currentEvento['slug'] ?? 'angie-xv') ?>" target="_blank" class="btn-nav">
                <i class="fas fa-tv"></i> Pantalla en Vivo
            </a>
        </div>
    </header>

    <!-- Métricas del Evento Seleccionado -->
    <section class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(200, 162, 74, 0.2); color: var(--gold-bright);">
                <i class="fas fa-images"></i>
            </div>
            <div>
                <div class="kpi-val"><?= $totalFotos ?></div>
                <div class="kpi-lbl">Total Recibidas</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243, 156, 18, 0.2); color: var(--color-warning);">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div class="kpi-val"><?= $totalPend ?></div>
                <div class="kpi-lbl">Por Moderar</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(46, 204, 113, 0.2); color: var(--color-success);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="kpi-val"><?= $totalAprob ?></div>
                <div class="kpi-lbl">Aprobadas en Vivo</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(231, 76, 60, 0.2); color: var(--color-danger);">
                <i class="fas fa-ban"></i>
            </div>
            <div>
                <div class="kpi-val"><?= $totalRech ?></div>
                <div class="kpi-lbl">Rechazadas</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(52, 152, 219, 0.2); color: #3498db;">
                <i class="fas fa-hard-drive"></i>
            </div>
            <div>
                <div class="kpi-val"><?= $storageUsage['total_mb'] ?> MB</div>
                <div class="kpi-lbl">Espacio Almacenado</div>
            </div>
        </div>
    </section>

    <!-- Barra de Pestañas y Acciones en Lote -->
    <section class="controls-bar">
        <div class="tabs-list">
            <a href="?evento_id=<?= $activeEventoId ?>&tab=pendientes" class="tab-btn <?= ($tab === 'pendientes') ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> Pendientes (<?= $totalPend ?>)
            </a>
            <a href="?evento_id=<?= $activeEventoId ?>&tab=aprobadas" class="tab-btn <?= ($tab === 'aprobadas') ? 'active' : '' ?>">
                <i class="fas fa-check-double"></i> Aprobadas (<?= $totalAprob ?>)
            </a>
            <a href="?evento_id=<?= $activeEventoId ?>&tab=rechazadas" class="tab-btn <?= ($tab === 'rechazadas') ? 'active' : '' ?>">
                <i class="fas fa-times-circle"></i> Rechazadas (<?= $totalRech ?>)
            </a>
            <a href="?evento_id=<?= $activeEventoId ?>&tab=destacadas" class="tab-btn <?= ($tab === 'destacadas') ? 'active' : '' ?>">
                <i class="fas fa-star"></i> Destacadas (<?= $totalDest ?>)
            </a>
            <a href="?evento_id=<?= $activeEventoId ?>&tab=todas" class="tab-btn <?= ($tab === 'todas') ? 'active' : '' ?>">
                <i class="fas fa-list"></i> Todas (<?= $totalFotos ?>)
            </a>
        </div>

        <div class="batch-actions">
            <label style="font-size:0.8rem; color:var(--text-sub); display:flex; align-items:center; gap:6px; cursor:pointer;">
                <input type="checkbox" id="selectAllCheckbox"> Seleccionar todas
            </label>
            <button type="button" class="btn-action btn-approve" id="btnBatchApprove" disabled>
                <i class="fas fa-check"></i> Aprobar Seleccionadas
            </button>
            <button type="button" class="btn-action btn-reject" id="btnBatchReject" disabled>
                <i class="fas fa-ban"></i> Rechazar Seleccionadas
            </button>
        </div>
    </section>

    <!-- Grilla de Fotos -->
    <main class="photos-grid">
        <?php if (empty($fotos)): ?>
            <div class="empty-state">
                <i class="fas fa-camera-retro"></i>
                <h3>No hay fotografías en esta sección</h3>
                <p>Las fotografías que envíen los invitados mediante el código QR aparecerán aquí.</p>
            </div>
        <?php else: ?>
            <?php foreach ($fotos as $f): 
                $fId = (int)$f['id'];
                $urlFoto = '../api/foto.php?f=' . rawurlencode($f['archivo']) . '&evento_id=' . $activeEventoId;
                $urlThumb = '../api/foto.php?f=' . rawurlencode($f['archivo']) . '&evento_id=' . $activeEventoId . '&thumb=1';
                $isAprob = (int)($f['aprobada'] ?? 0) === 1 && (int)($f['visible'] ?? 0) === 1;
                $isRech = ($f['estado'] ?? '') === 'rechazada';
                $isDest = (int)($f['es_destacada'] ?? 0) === 1;
                $pesoKb = !empty($f['peso_bytes']) ? round($f['peso_bytes'] / 1024, 0) . ' KB' : 'Optimizado';
                $dimen = (!empty($f['ancho_px']) && !empty($f['alto_px'])) ? "{$f['ancho_px']}×{$f['alto_px']}" : '';
            ?>
                <div class="photo-card" id="card-<?= $fId ?>">
                    <div class="photo-img-wrap" onclick="openLightbox('<?= $urlFoto ?>', '<?= htmlspecialchars($f['nombre_invitado'] ?? 'Invitado', ENT_QUOTES) ?>', '<?= htmlspecialchars($f['mensaje'] ?? '', ENT_QUOTES) ?>', '<?= $pesoKb ?>', '<?= $dimen ?>', '<?= $f['created_at'] ?>', '<?= $f['hash_sha256'] ?? '' ?>', <?= $fId ?>)">
                        <input type="checkbox" class="photo-checkbox" value="<?= $fId ?>" onclick="event.stopPropagation(); updateBatchButtons();">
                        <img src="<?= $urlThumb ?>" alt="Foto invitado" loading="lazy">
                        <span class="badge-star" onclick="event.stopPropagation(); toggleDestacar(<?= $fId ?>)" title="Destacar foto">
                            <i class="<?= $isDest ? 'fas' : 'far' ?> fa-star"></i>
                        </span>
                    </div>

                    <div class="photo-body">
                        <div>
                            <div class="guest-name"><?= htmlspecialchars($f['nombre_invitado'] ?? 'Invitado Especial') ?></div>
                            <?php if (!empty($f['mensaje'])): ?>
                                <div class="guest-msg">"<?= htmlspecialchars($f['mensaje']) ?>"</div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <div class="photo-meta-tech">
                                <span><?= date('H:i d/m', strtotime($f['created_at'])) ?></span>
                                <span><?= $pesoKb ?> <?= $dimen ? '· ' . $dimen : '' ?></span>
                            </div>

                            <div class="photo-actions">
                                <button type="button" class="btn-card-action btn-card-approve" onclick="moderarFoto(<?= $fId ?>, 'aprobar')" title="Aprobar para pantalla en vivo">
                                    <i class="fas fa-check"></i> Aprobar
                                </button>
                                <button type="button" class="btn-card-action btn-card-reject" onclick="moderarFoto(<?= $fId ?>, 'rechazar')" title="Rechazar y ocultar">
                                    <i class="fas fa-ban"></i> Rechazar
                                </button>
                                <button type="button" class="btn-card-action btn-card-del" onclick="eliminarFoto(<?= $fId ?>)" title="Eliminar definitivamente">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- Modal Lightbox -->
    <div class="modal-overlay" id="lightboxModal" onclick="closeLightbox()">
        <div class="modal-card" onclick="event.stopPropagation()">
            <div class="modal-img-wrap">
                <img id="modalImg" src="" alt="Foto completa">
            </div>
            <div class="modal-info">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <h2 id="modalGuest" style="font-size:1.2rem; color:var(--gold-bright);">Invitado</h2>
                        <button type="button" onclick="closeLightbox()" style="background:none; border:none; color:#fff; font-size:1.3rem; cursor:pointer;">&times;</button>
                    </div>
                    <p id="modalMsg" style="font-style:italic; color:var(--text-sub); margin-bottom:16px; font-size:0.95rem;">"Mensaje"</p>
                    
                    <div style="background:rgba(0,0,0,0.3); padding:12px; border-radius:8px; font-size:0.75rem; color:var(--text-sub); display:flex; flex-direction:column; gap:6px;">
                        <div><strong>Fecha:</strong> <span id="modalDate">-</span></div>
                        <div><strong>Peso / Dim:</strong> <span id="modalWeight">-</span></div>
                        <div><strong>SHA-256:</strong> <span id="modalHash" style="word-break:break-all; font-family:monospace;">-</span></div>
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <a id="modalDownload" href="#" download class="btn-nav" style="flex:1; justify-content:center;">
                        <i class="fas fa-download"></i> Descargar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.photo-checkbox');
    const btnApprove = document.getElementById('btnBatchApprove');
    const btnReject = document.getElementById('btnBatchReject');

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBatchButtons();
    });

    function updateBatchButtons() {
        const checkedCount = document.querySelectorAll('.photo-checkbox:checked').length;
        btnApprove.disabled = checkedCount === 0;
        btnReject.disabled = checkedCount === 0;
        if (checkedCount === 0) selectAll.checked = false;
    }

    async function sendModAction(action, payload) {
        try {
            const res = await fetch('../api/fotos.php?action=' + action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            return await res.json();
        } catch (e) {
            alert('Error conectando con el servidor.');
            return null;
        }
    }

    async function moderarFoto(id, action) {
        const data = await sendModAction(action, { id });
        if (data && data.success) {
            const card = document.getElementById('card-' + id);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.8)';
                setTimeout(() => card.remove(), 300);
            }
        }
    }

    async function toggleDestacar(id) {
        const data = await sendModAction('destacar', { id });
        if (data && data.success) {
            location.reload();
        }
    }

    async function eliminarFoto(id) {
        if (!confirm('¿Estás seguro de eliminar permanentemente esta fotografía?')) return;
        const data = await sendModAction('eliminar', { id });
        if (data && data.success) {
            const card = document.getElementById('card-' + id);
            if (card) card.remove();
        }
    }

    btnApprove.addEventListener('click', async () => {
        const ids = Array.from(document.querySelectorAll('.photo-checkbox:checked')).map(cb => parseInt(cb.value));
        if (ids.length === 0) return;
        const data = await sendModAction('aprobar', { ids });
        if (data && data.success) location.reload();
    });

    btnReject.addEventListener('click', async () => {
        const ids = Array.from(document.querySelectorAll('.photo-checkbox:checked')).map(cb => parseInt(cb.value));
        if (ids.length === 0) return;
        const data = await sendModAction('rechazar', { ids });
        if (data && data.success) location.reload();
    });

    function openLightbox(url, guest, msg, weight, dim, date, hash, id) {
        document.getElementById('modalImg').src = url;
        document.getElementById('modalGuest').textContent = guest;
        document.getElementById('modalMsg').textContent = msg ? `"${msg}"` : 'Sin mensaje';
        document.getElementById('modalWeight').textContent = `${weight} ${dim ? '· ' + dim : ''}`;
        document.getElementById('modalDate').textContent = date;
        document.getElementById('modalHash').textContent = hash || 'N/A';
        document.getElementById('modalDownload').href = url + '&download=1';
        document.getElementById('lightboxModal').style.display = 'flex';
    }

    function closeLightbox() {
        document.getElementById('lightboxModal').style.display = 'none';
    }
    </script>
</body>
</html>
