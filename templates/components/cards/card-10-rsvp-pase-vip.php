<?php
/**
 * Componente Tarjeta 10: RSVP-PASE-VIP
 * Plataforma: XV Experience
 */
?>
<div class="card-slide hidden" data-slide="10">
            <div class="card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="card-title-script">Confirma tu Asistencia</div>
                <div class="card-title-serif">RSVP</div>
                <div class="gold-divider"></div>

                <!-- Formulario RSVP -->
                <div class="rsvp-form-container" id="rsvp-form">
                    <div class="rsvp-error" id="rsvp-error"></div>

                    <div class="rsvp-label">Tu nombre completo</div>
                    <input type="text" class="rsvp-input" id="rsvp-nombre" 
                           placeholder="Nombre y Apellido" 
                           maxlength="150" autocomplete="name">

                    <div class="rsvp-label">¿Asistirás?</div>
                    <div class="rsvp-attendance-btns">
                        <button class="rsvp-attend-btn" id="rsvp-yes" data-attend="true">
                            <i class="fas fa-check-circle"></i>
                            Sí asistiré
                        </button>
                        <button class="rsvp-attend-btn" id="rsvp-no" data-attend="false">
                            <i class="fas fa-times-circle"></i>
                            No podré asistir
                        </button>
                    </div>

                    <!-- Sección de acompañantes (visible solo si confirma que sí asistirá) -->
                    <div class="rsvp-companions-section" id="rsvp-companions-section">
                        <div class="rsvp-companions-header">
                            <span class="rsvp-label" style="margin-bottom: 0;">Acompañantes</span>
                            <span id="rsvp-passes-badge">1 persona (Solo tú)</span>
                        </div>
                        <p style="font-size: 0.73rem; color: rgba(255, 248, 236, 0.7); margin-bottom: 12px; text-align: center; line-height: 1.4;">
                            ¿Vienes con alguien más? Agrega a tus acompañantes para que queden incluidos en tu pase QR:
                        </p>
                        <div id="rsvp-companions-list"></div>
                        <button type="button" class="rsvp-add-companion-btn" id="rsvp-add-companion">
                            <i class="fas fa-user-plus"></i> + Agregar Nombre de Acompañante
                        </button>
                    </div>

                    <button class="rsvp-submit-btn" id="rsvp-submit" disabled>
                        Confirmar Asistencia
                    </button>
                </div>

                <!-- Mensaje de éxito / Pase VIP QR -->
                <div class="rsvp-success" id="rsvp-success">
                    <!-- Pase de entrada VIP con QR si asistirá -->
                    <div class="vip-ticket" id="vipTicket" style="display: none;">
                        <div class="ticket-badge">✨ PASE DE ENTRADA VIP ✨</div>
                        <div class="ticket-tiara">👑</div>
                        <div class="ticket-title">MIS XV AÑOS</div>
                        <div class="ticket-subtitle">Angie Karolina</div>
                        <div class="gold-divider" style="margin: 8px auto 14px auto;"></div>

                        <!-- Contenedor del QR -->
                        <div class="ticket-qr-container">
                            <div id="ticketQrWrapper"></div>
                            <div class="ticket-qr-code-txt" id="ticketQrCodeText"></div>
                        </div>
