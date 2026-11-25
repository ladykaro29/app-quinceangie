<?php
/**
 * XV Experience — Página Móvil del Álbum Colaborativo Profesional
 * Subida múltiple, compresión inteligente en navegador y cola de envíos resiliente.
 */
require_once __DIR__ . '/../app/autoload.php';

use App\Middleware\TenantResolverMiddleware;
use App\Core\CurrentEventContext;

TenantResolverMiddleware::resolve();

$event = CurrentEventContext::get();
$configs = CurrentEventContext::allConfigs();
$festejada = htmlspecialchars($event['festejada_nombre'] ?? 'La Quinceañera');
$nombreEvento = htmlspecialchars($event['nombre_celebracion'] ?? 'Mis XV Años');
$slug = htmlspecialchars(CurrentEventContext::slug() ?? 'angie-xv');
$isSophia = str_contains($festejada, 'Sophia');

$portadaUrl = $configs['portada_url'] ?? ($isSophia ? 'sophia-portada.webp' : 'portada.jpg');
$designTokens = $configs['design_tokens'] ?? null;
if (is_string($designTokens)) {
    $designTokens = json_decode($designTokens, true);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>📸 Álbum Colaborativo · <?= $festejada ?></title>
    <!-- Favicon Corona / Mariposa -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='30' fill='%23062E25' stroke='%23C8A24A' stroke-width='2.5'/%3E%3Cpath d='M13 43 L17 24 L24 33 L32 15 L40 33 L47 24 L51 43 Z' fill='%23FFD700' stroke='%23FFF0BA' stroke-width='0.8'/%3E%3Cpath d='M13 43 Q32 47 51 43 L51 47 Q32 51 13 47 Z' fill='%23C8A24A'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Great+Vibes&family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #031713;
            --emerald-deep: #062E25;
            --emerald-card: #0A3C31;
            --gold-primary: #C8A24A;
            --gold-bright: #FFD700;
            --gold-light: #FFF0BA;
            --text-light: #F5F5F0;
            --text-muted: #A3B8B0;
            --accent-pink: #F9E0E9;
            --accent-lavender: #9C84BA;
            --radius-card: 16px;
        }

        <?php if (!empty($designTokens) && is_array($designTokens)): ?>
        :root {
            <?php if (!empty($designTokens['--verde-oscuro'])): ?>
                --bg-dark: <?= $designTokens['--marron'] ?? '#120610' ?>;
                --emerald-deep: <?= $designTokens['--verde-oscuro'] ?>;
                --emerald-card: <?= $designTokens['--verde-medio'] ?? '#3B1E37' ?>;
            <?php endif; ?>
            <?php if (!empty($designTokens['--dorado'])): ?>
                --gold-primary: <?= $designTokens['--dorado'] ?>;
                --gold-bright: <?= $designTokens['--dorado-brillante'] ?? '#FFF0BA' ?>;
                --gold-light: <?= $designTokens['--dorado-claro'] ?? '#F7E5B5' ?>;
            <?php endif; ?>
        }
        <?php endif; ?>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 10% 10%, rgba(200, 162, 74, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(10, 60, 49, 0.4) 0%, transparent 50%),
                linear-gradient(180deg, #02110E 0%, #062E25 100%);
            color: var(--text-light);
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            padding: 16px 14px 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header {
            text-align: center;
            max-width: 480px;
            width: 100%;
            margin-bottom: 20px;
        }

        .badge-celebration {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(200, 162, 74, 0.18);
            border: 1px solid var(--gold-primary);
            border-radius: 50px;
            color: var(--gold-bright);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
        }

        .header h1 {
            font-family: 'Cinzel Decorative', serif;
            font-size: 1.5rem;
            color: var(--gold-light);
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .header p {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .upload-card {
            background: var(--emerald-card);
            border: 1px solid rgba(200, 162, 74, 0.35);
            border-radius: var(--radius-card);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.55);
            max-width: 480px;
            width: 100%;
            padding: 22px 18px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gold-light);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-input, .form-textarea {
            width: 100%;
            background: rgba(4, 27, 22, 0.7);
            border: 1px solid rgba(200, 162, 74, 0.3);
            border-radius: 10px;
            padding: 12px 14px;
            color: #FFF;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-input:focus, .form-textarea:focus {
            border-color: var(--gold-bright);
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.25);
        }

        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 18px;
            line-height: 1.4;
            cursor: pointer;
        }

        .terms-checkbox input[type="checkbox"] {
            margin-top: 3px;
            accent-color: var(--gold-primary);
            width: 16px;
            height: 16px;
        }

        .dropzone-area {
            border: 2px dashed var(--gold-primary);
            border-radius: 12px;
            padding: 24px 16px;
            text-align: center;
            background: rgba(4, 27, 22, 0.45);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .dropzone-area:hover, .dropzone-area.dragover {
            background: rgba(200, 162, 74, 0.15);
            border-color: var(--gold-bright);
        }

        .dropzone-area i {
            font-size: 2.2rem;
            color: var(--gold-bright);
            margin-bottom: 10px;
            display: block;
        }

        .dropzone-area p {
            font-size: 0.88rem;
            color: var(--text-light);
            margin-bottom: 4px;
            font-weight: 500;
        }

        .dropzone-area span {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .file-input {
            display: none;
        }

        /* Cola de Archivos */
        .queue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
            margin-bottom: 10px;
            font-size: 0.82rem;
            color: var(--gold-light);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .queue-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 16px;
            max-height: 380px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .queue-item {
            background: rgba(3, 23, 19, 0.8);
            border: 1px solid rgba(200, 162, 74, 0.25);
            border-radius: 10px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .queue-thumb {
            width: 48px;
            height: 48px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid rgba(200, 162, 74, 0.3);
            background: #000;
            flex-shrink: 0;
        }

        .queue-info {
            flex: 1;
            min-width: 0;
        }

        .queue-name {
            font-size: 0.82rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-light);
        }

        .queue-meta {
            font-size: 0.72rem;
            color: var(--text-muted);
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 2px;
        }

        .status-pill {
            display: inline-block;
            font-size: 0.68rem;
            padding: 2px 7px;
            border-radius: 20px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .status-selected { background: rgba(255, 255, 255, 0.15); color: #fff; }
        .status-processing { background: rgba(243, 156, 18, 0.3); color: #f39c12; }
        .status-ready { background: rgba(52, 152, 219, 0.3); color: #3498db; }
        .status-uploading { background: rgba(230, 126, 34, 0.3); color: #e67e22; }
        .status-success { background: rgba(46, 204, 113, 0.3); color: #2ecc71; }
        .status-error { background: rgba(231, 76, 60, 0.3); color: #e74c3c; }

        .queue-action {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 6px;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .queue-action:hover {
            color: #e74c3c;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #FFD700 0%, #C8A24A 100%);
            color: #062E25;
            font-family: 'Cinzel Decorative', serif;
            font-size: 0.95rem;
            font-weight: 700;
            padding: 14px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(200, 162, 74, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
        }

        .progress-bar-container {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            height: 8px;
            margin-top: 14px;
            overflow: hidden;
            display: none;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #C8A24A, #FFD700);
            width: 0%;
            transition: width 0.3s;
        }

        .alert-box {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-top: 16px;
            display: none;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(30, 140, 70, 0.25);
            border: 1px solid #2ecc71;
            color: #a8f0c2;
        }

        .alert-error {
            background: rgba(180, 40, 40, 0.25);
            border: 1px solid #e74c3c;
            color: #f8b4b4;
        }

        .back-link {
            margin-top: 24px;
            color: var(--gold-light);
            text-decoration: none;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--gold-bright);
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="badge-celebration">
            <i class="fas <?= $isSophia ? 'fa-butterfly' : 'fa-crown' ?>"></i>
            <span><?= $nombreEvento ?></span>
        </div>
        <h1><?= $festejada ?></h1>
        <p>Comparte tus fotos y momentos especiales para proyectarlos en la fiesta en vivo.</p>
    </header>

    <main class="upload-card">
        <form id="albumForm">
            <input type="hidden" name="evento_id" id="hidden_evento_id" value="<?= (int)($event['id'] ?? 1) ?>">
            <div class="form-group">
                <label class="form-label" for="nombre_invitado">Tu Nombre o Apodo:</label>
                <input type="text" id="nombre_invitado" class="form-input" placeholder="Ej. Tía Carmen / Primo David" required maxlength="60">
            </div>

            <div class="form-group">
                <label class="form-label" for="mensaje">Dedicatoria o Mensaje (Opcional):</label>
                <textarea id="mensaje" class="form-textarea" rows="2" placeholder="¡Muchas felicidades en tus XV!" maxlength="300"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Seleccionar Fotografías:</label>
                <div class="dropzone-area" id="dropzone">
                    <i class="fas fa-cloud-arrow-up"></i>
                    <p>Toca aquí o arrastra tus fotografías</p>
                    <span>Formatos JPG, PNG, WEBP (Comprimidas automáticamente)</span>
                </div>
                <input type="file" id="fotoInput" class="file-input" accept="image/jpeg,image/png,image/webp,image/heic" multiple>
            </div>

            <!-- Lista interactiva de la cola -->
            <div id="queueContainer" style="display: none;">
                <div class="queue-header">
                    <span>Fotos para enviar (<span id="queueCount">0</span>)</span>
                    <button type="button" id="btnClearAll" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:0.75rem;">Limpiar todo</button>
                </div>
                <div class="queue-list" id="queueList"></div>
            </div>

            <label class="terms-checkbox">
                <input type="checkbox" id="termsCheck" checked required>
                <span>Acepto que estas fotografías se proyecten en la fiesta y formen parte del álbum digital de recuerdos.</span>
            </label>

            <div class="progress-bar-container" id="progressContainer">
                <div class="progress-bar" id="progressBar"></div>
            </div>

            <div class="alert-box alert-success" id="alertSuccess">
                <i class="fas fa-check-circle"></i> <span id="successMsg">¡Fotografías enviadas exitosamente!</span>
            </div>

            <div class="alert-box alert-error" id="alertError">
                <i class="fas fa-exclamation-triangle"></i> <span id="errorMsg">Ocurrió un error.</span>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit" disabled>
                <i class="fas fa-paper-plane"></i> Enviar al Álbum en Vivo
            </button>
        </form>
    </main>

    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Volver a la Invitación
    </a>

    <script>
    (() => {
        const eventoId = <?= (int)($event['id'] ?? 1) ?>;
        const dropzone = document.getElementById('dropzone');
        const fotoInput = document.getElementById('fotoInput');
        const queueContainer = document.getElementById('queueContainer');
        const queueList = document.getElementById('queueList');
        const queueCount = document.getElementById('queueCount');
        const btnClearAll = document.getElementById('btnClearAll');
        const btnSubmit = document.getElementById('btnSubmit');
        const albumForm = document.getElementById('albumForm');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const alertSuccess = document.getElementById('alertSuccess');
        const alertError = document.getElementById('alertError');
        const successMsg = document.getElementById('successMsg');
        const errorMsg = document.getElementById('errorMsg');
        const termsCheck = document.getElementById('termsCheck');

        // Estado de la cola de archivos
        // Cada elemento: { id, file, originalSize, optimizedFile, optimizedSize, status, progress, error, uuid }
        let uploadQueue = [];
        let isUploading = false;

        function generateUuid() {
            if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
            return 'c_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        // Dropzone interactions
        dropzone.addEventListener('click', () => fotoInput.click());
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            handleIncomingFiles(e.dataTransfer.files);
        });
        fotoInput.addEventListener('change', (e) => {
            handleIncomingFiles(e.target.files);
            fotoInput.value = '';
        });

        btnClearAll.addEventListener('click', () => {
            if (isUploading) return;
            uploadQueue = [];
            renderQueue();
        });

        termsCheck.addEventListener('change', updateSubmitState);

        function handleIncomingFiles(files) {
            if (!files || files.length === 0) return;
            for (let f of files) {
                if (!f.type.startsWith('image/')) continue;
                uploadQueue.push({
                    id: 'q_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
                    uuid: generateUuid(),
                    file: f,
                    name: f.name,
                    originalSize: f.size,
                    optimizedFile: null,
                    optimizedSize: 0,
                    status: 'selected', // selected, processing, ready, uploading, success, error
                    error: null
                });
            }
            renderQueue();
            processQueueCompression();
        }

        function renderQueue() {
            if (uploadQueue.length === 0) {
                queueContainer.style.display = 'none';
                updateSubmitState();
                return;
            }

            queueContainer.style.display = 'block';
            queueCount.textContent = uploadQueue.length;
            queueList.innerHTML = '';

            uploadQueue.forEach((item, index) => {
                const el = document.createElement('div');
                el.className = 'queue-item';

                // Status label & badge class
                let statusClass = 'status-selected';
                let statusLabel = 'Seleccionada';
                if (item.status === 'processing') { statusClass = 'status-processing'; statusLabel = 'Optimizando...'; }
                else if (item.status === 'ready') { statusClass = 'status-ready'; statusLabel = 'Lista'; }
                else if (item.status === 'uploading') { statusClass = 'status-uploading'; statusLabel = 'Subiendo...'; }
                else if (item.status === 'success') { statusClass = 'status-success'; statusLabel = 'Recibida ✔'; }
                else if (item.status === 'error') { statusClass = 'status-error'; statusLabel = 'Error ✖'; }

                const thumbUrl = URL.createObjectURL(item.file);
                const sizeText = item.optimizedSize > 0 
                    ? `${formatBytes(item.originalSize)} → ${formatBytes(item.optimizedSize)}` 
                    : formatBytes(item.originalSize);

                el.innerHTML = `
                    <img class="queue-thumb" src="${thumbUrl}" alt="Vista previa">
                    <div class="queue-info">
                        <div class="queue-name" title="${item.name}">${item.name}</div>
                        <div class="queue-meta">
                            <span>${sizeText}</span>
                            <span class="status-pill ${statusClass}">${statusLabel}</span>
                        </div>
                        ${item.error ? `<div style="font-size:0.7rem; color:#e74c3c; margin-top:2px;">${item.error}</div>` : ''}
                    </div>
                    ${item.status === 'error' ? `<button type="button" class="queue-action retry-btn" data-id="${item.id}" title="Reintentar"><i class="fas fa-redo"></i></button>` : ''}
                    ${item.status !== 'uploading' ? `<button type="button" class="queue-action delete-btn" data-id="${item.id}" title="Quitar"><i class="fas fa-trash-alt"></i></button>` : ''}
                `;

                const delBtn = el.querySelector('.delete-btn');
                if (delBtn) {
                    delBtn.onclick = () => {
                        uploadQueue.splice(index, 1);
                        renderQueue();
                    };
                }

                const retryBtn = el.querySelector('.retry-btn');
                if (retryBtn) {
                    retryBtn.onclick = () => {
                        item.status = 'ready';
                        item.error = null;
                        renderQueue();
                    };
                }

                queueList.appendChild(el);
            });

            updateSubmitState();
        }

        function updateSubmitState() {
            const hasPending = uploadQueue.some(i => i.status === 'selected' || i.status === 'processing' || i.status === 'ready');
            btnSubmit.disabled = !hasPending || isUploading || !termsCheck.checked;
        }

        // Módulo de compresión Canvas / WebP
        async function compressImageFile(file, maxWidth = 2048, maxHeight = 2048, quality = 0.82) {
            return new Promise((resolve) => {
                if (!file.type.startsWith('image/') || file.size < 200 * 1024) {
                    resolve(file);
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        let w = img.width;
                        let h = img.height;

                        if (w > maxWidth || h > maxHeight) {
                            if (w / h > maxWidth / maxHeight) {
                                h = Math.round((h * maxWidth) / w);
                                w = maxWidth;
                            } else {
                                w = Math.round((w * maxHeight) / h);
                                h = maxHeight;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);

                        // Probar WebP primero, fallback a JPEG
                        canvas.toBlob((blob) => {
                            if (blob && blob.size < file.size) {
                                const cleanName = file.name.replace(/\.[^/.]+$/, "") + ".webp";
                                resolve(new File([blob], cleanName, { type: 'image/webp', lastModified: Date.now() }));
                            } else {
                                canvas.toBlob((blobJpeg) => {
                                    if (blobJpeg) {
                                        const cleanNameJpeg = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                                        resolve(new File([blobJpeg], cleanNameJpeg, { type: 'image/jpeg', lastModified: Date.now() }));
                                    } else {
                                        resolve(file);
                                    }
                                }, 'image/jpeg', quality);
                            }
                        }, 'image/webp', quality);
                    };
                    img.onerror = () => resolve(file);
                    img.src = e.target.result;
                };
                reader.onerror = () => resolve(file);
                reader.readAsDataURL(file);
            });
        }

        // Procesa la compresión secuencial de la cola para no saturar CPU en móviles
        async function processQueueCompression() {
            for (let item of uploadQueue) {
                if (item.status === 'selected') {
                    item.status = 'processing';
                    renderQueue();

                    try {
                        const opt = await compressImageFile(item.file);
                        item.optimizedFile = opt;
                        item.optimizedSize = opt.size;
                        item.status = 'ready';
                    } catch (e) {
                        item.optimizedFile = item.file;
                        item.optimizedSize = item.file.size;
                        item.status = 'ready';
                    }
                    renderQueue();
                }
            }
        }

        // Envío con concurrencia limitada (máx. 2 simultáneas)
        albumForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (isUploading) return;

            const itemsToUpload = uploadQueue.filter(i => i.status === 'ready');
            if (itemsToUpload.length === 0) return;

            isUploading = true;
            btnSubmit.disabled = true;
            alertSuccess.style.display = 'none';
            alertError.style.display = 'none';
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';

            const nombre = document.getElementById('nombre_invitado').value.trim();
            const mensaje = document.getElementById('mensaje').value.trim();

            let completedCount = 0;
            let successCount = 0;
            let errorCount = 0;
            const total = itemsToUpload.length;

            const CONCURRENCY = 2;
            let currentIndex = 0;

            async function uploadWorker() {
                while (currentIndex < itemsToUpload.length) {
                    const item = itemsToUpload[currentIndex++];
                    item.status = 'uploading';
                    renderQueue();

                    const formData = new FormData();
                    formData.append('evento_id', '<?= (int)($event['id'] ?? 1) ?>');
                    formData.append('foto', item.optimizedFile || item.file);
                    formData.append('nombre_invitado', nombre);
                    formData.append('mensaje', mensaje);
                    formData.append('subida_uuid', item.uuid);

                    try {
                        const res = await fetch('../api/fotos.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await res.json();
                        if (data && data.success) {
                            item.status = 'success';
                            successCount++;
                        } else {
                            item.status = 'error';
                            item.error = data.error || 'Fallo en servidor';
                            errorCount++;
                        }
                    } catch (err) {
                        item.status = 'error';
                        item.error = 'Error de conexión';
                        errorCount++;
                    }

                    completedCount++;
                    const pct = Math.round((completedCount / total) * 100);
                    progressBar.style.width = pct + '%';
                    renderQueue();
                }
            }

            // Iniciar workers en paralelo con concurrencia limitada
            const workers = [];
            for (let w = 0; w < Math.min(CONCURRENCY, itemsToUpload.length); w++) {
                workers.push(uploadWorker());
            }

            await Promise.all(workers);

            isUploading = false;
            progressContainer.style.display = 'none';

            if (successCount > 0) {
                successMsg.textContent = `¡Listo! Se compartieron ${successCount} fotografía(s) en la fiesta de ${nombre ? nombre : 'los anfitriones'}.`;
                alertSuccess.style.display = 'flex';
                document.getElementById('mensaje').value = '';
            }

            if (errorCount > 0) {
                errorMsg.textContent = `${errorCount} foto(s) no se pudieron enviar. Puedes tocar el botón de reintentar.`;
                alertError.style.display = 'flex';
            }

            updateSubmitState();
        });
    })();
    </script>
</body>
</html>
