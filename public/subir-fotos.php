<?php
/**
 * XV Experience — Página Móvil del Álbum Colaborativo
 * Permite a los invitados compartir fotografías tomadas durante la fiesta
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
    <title>Álbum Colaborativo · <?= $festejada ?></title>
    <!-- Favicon Corona Real -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='30' fill='%23062E25' stroke='%23C8A24A' stroke-width='2.5'/%3E%3Cpath d='M13 43 L17 24 L24 33 L32 15 L40 33 L47 24 L51 43 Z' fill='%23FFD700' stroke='%23FFF0BA' stroke-width='0.8'/%3E%3Cpath d='M13 43 Q32 47 51 43 L51 47 Q32 51 13 47 Z' fill='%23C8A24A'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
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
                radial-gradient(circle at 10% 10%, rgba(200, 162, 74, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(10, 60, 49, 0.4) 0%, transparent 50%),
                linear-gradient(180deg, #02110E 0%, #062E25 100%);
            color: var(--text-light);
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            padding: 20px 16px 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header {
            text-align: center;
            max-width: 480px;
            width: 100%;
            margin-bottom: 24px;
        }

        .badge-crown {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: rgba(200, 162, 74, 0.15);
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
            font-size: 1.55rem;
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
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
            max-width: 480px;
            width: 100%;
            padding: 24px 20px;
        }

        .form-group {
            margin-bottom: 18px;
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

        .dropzone-area {
            border: 2px dashed var(--gold-primary);
            border-radius: 12px;
            padding: 24px 16px;
            text-align: center;
            background: rgba(4, 27, 22, 0.4);
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
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 4px;
        }

        .dropzone-area span {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .file-input {
            display: none;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 16px;
        }

        .preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(200, 162, 74, 0.4);
            background: #000;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-item .remove-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(230, 50, 50, 0.85);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 0.7rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
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
            margin-top: 18px;
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
        }

        .alert-success {
            background: rgba(30, 140, 70, 0.25);
            border: 1px solid #2ecc71;
            color: #a8f0c2;
        }

        .alert-error {
            background: rgba(200, 40, 40, 0.25);
            border: 1px solid #e74c3c;
            color: #fca5a5;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            color: var(--gold-light);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--gold-bright);
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="badge-crown">
            <i class="fas fa-crown"></i>
            <span>XV Experience</span>
        </div>
        <h1>Álbum Colaborativo</h1>
        <p>Comparte las fotos que tomaste durante la fiesta de <strong><?= $festejada ?></strong> para que se proyecten en vivo en las pantallas.</p>
    </header>

    <main class="upload-card">
        <form id="albumForm">
            <div class="form-group">
                <label class="form-label" for="nombre_invitado"><i class="fas fa-user-tag"></i> Tu Nombre / Familia</label>
                <input type="text" id="nombre_invitado" name="nombre_invitado" class="form-input" placeholder="Ej. Tío Roberto y Familia" required maxlength="60">
            </div>

            <div class="form-group">
                <label class="form-label" for="mensaje"><i class="fas fa-comment-heart"></i> Mensaje o Dedicatoria (Opcional)</label>
                <textarea id="mensaje" name="mensaje" class="form-textarea" rows="2" placeholder="¡Felicidades Angie en tu noche mágica!" maxlength="300"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fas fa-camera"></i> Selecciona tus Fotos</label>
                <div class="dropzone-area" id="dropzone">
                    <i class="fas fa-cloud-arrow-up"></i>
                    <p>Toca aquí o arrastra tus fotografías</p>
                    <span>Formatos JPG, PNG, WEBP (Hasta 12 MB)</span>
                </div>
                <input type="file" id="fotoInput" class="file-input" accept="image/jpeg,image/png,image/webp,image/heic" multiple>
            </div>

            <div class="preview-grid" id="previewGrid"></div>

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
        const dropzone = document.getElementById('dropzone');
        const fotoInput = document.getElementById('fotoInput');
        const previewGrid = document.getElementById('previewGrid');
        const btnSubmit = document.getElementById('btnSubmit');
        const albumForm = document.getElementById('albumForm');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const alertSuccess = document.getElementById('alertSuccess');
        const alertError = document.getElementById('alertError');
        const successMsg = document.getElementById('successMsg');
        const errorMsg = document.getElementById('errorMsg');

        let selectedFiles = [];

        dropzone.addEventListener('click', () => fotoInput.click());

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fotoInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            for (let file of files) {
                if (file.type.startsWith('image/')) {
                    selectedFiles.push(file);
                }
            }
            renderPreviews();
        }

        function renderPreviews() {
            previewGrid.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'preview-item';

                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-btn';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.onclick = (e) => {
                    e.stopPropagation();
                    selectedFiles.splice(index, 1);
                    renderPreviews();
                };

                item.appendChild(img);
                item.appendChild(removeBtn);
                previewGrid.appendChild(item);
            });

            btnSubmit.disabled = selectedFiles.length === 0;
        }

        albumForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (selectedFiles.length === 0) return;

            btnSubmit.disabled = true;
            alertSuccess.style.display = 'none';
            alertError.style.display = 'none';
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';

            const nombre = document.getElementById('nombre_invitado').value.trim();
            const mensaje = document.getElementById('mensaje').value.trim();
            let sentCount = 0;
            let errors = 0;

            for (let i = 0; i < selectedFiles.length; i++) {
                const formData = new FormData();
                formData.append('evento_id', '<?= (int)($event['id'] ?? 1) ?>');
                formData.append('foto', selectedFiles[i]);
                formData.append('nombre_invitado', nombre);
                formData.append('mensaje', mensaje);

                try {
                    const res = await fetch('../api/fotos.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        sentCount++;
                    } else {
                        errors++;
                    }
                } catch (err) {
                    errors++;
                }

                const pct = Math.round(((i + 1) / selectedFiles.length) * 100);
                progressBar.style.width = pct + '%';
            }

            progressContainer.style.display = 'none';

            if (sentCount > 0) {
                successMsg.textContent = `¡Listo! Se compartieron ${sentCount} fotografía(s) en la pantalla de la fiesta.`;
                alertSuccess.style.display = 'block';
                selectedFiles = [];
                renderPreviews();
                document.getElementById('mensaje').value = '';
            }

            if (errors > 0) {
                errorMsg.textContent = `${errors} fotografía(s) no pudieron enviarse. Inténtalo nuevamente.`;
                alertError.style.display = 'block';
            }

            btnSubmit.disabled = selectedFiles.length === 0;
        });
    </script>
</body>
</html>
