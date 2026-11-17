<?php
/**
 * Componente Tarjeta 12: DESPEDIDA (¡Te Espero! & Foto de Angie)
 * Plataforma: XV Experience
 */
?>
<div class="card-slide hidden" data-slide="12">
    <div class="card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px 16px;">
        <div class="corner-tl"></div>
        <div class="corner-tr"></div>
        <div class="corner-bl"></div>
        <div class="corner-br"></div>

        <div class="card-title-script" style="font-size: 2.3rem; margin-bottom: 2px;">¡Te Espero!</div>
        <div class="card-title-serif" style="font-size: 0.72rem; letter-spacing: 2px; margin-bottom: 8px;">MIS XV AÑOS</div>

        <!-- Marco dorado con la foto de Angie -->
        <div class="angie-photo-wrapper">
            <div class="angie-photo-frame">
                <img src="angie-xv.jpg" onerror="if(!this.dataset.retry){this.dataset.retry=1;this.src='public/angie-xv.jpg';}" alt="Angie Karolina — Mis XV Años" class="angie-photo-img" loading="eager">
                <div class="angie-photo-sheen"></div>
            </div>
        </div>

        <div class="gold-divider" style="margin: 10px auto 8px auto;"></div>

        <p class="card-text" style="margin: 0; font-size: 0.86rem; line-height: 1.4;">
            Tu presencia es el mejor regalo.<br>
            ¡Celebremos juntos esta noche mágica! ✨
        </p>

        <p class="card-text" style="font-size: 0.75rem; margin-top: 8px; opacity: 0.85;">
            Con todo mi cariño,<br>
            <span style="font-family: var(--font-script); font-size: 1.75rem; color: var(--dorado); display: inline-block; margin-top: 1px;">
                Angie Karolina
            </span>
        </p>
    </div>
</div>
