<?php
/**
 * Componente Tarjeta 7: TRIVIA
 * Plataforma: XV Experience
 */
?>
<div class="card-slide hidden" data-slide="7">
            <div class="card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="card-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="card-title-script">¿Cuánto me conoces?</div>
                <div class="card-title-serif">TRIVIA</div>
                <div class="gold-divider"></div>

                <div id="trivia-container">
                    <div class="trivia-counter" id="trivia-counter">Pregunta 1 de 5</div>
                    <div class="trivia-question" id="trivia-question"></div>
                    <div class="trivia-options" id="trivia-options"></div>
                    <div class="trivia-result" id="trivia-result"></div>
                    <div class="trivia-nav" id="trivia-nav">
                        <button class="trivia-nav-btn" id="trivia-prev" disabled>
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                        <button class="trivia-nav-btn" id="trivia-next">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
