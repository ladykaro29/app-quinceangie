<?php
/**
 * Layout del Mazo de 13 Tarjetas Modulares
 * Plataforma: XV Experience
 */
use App\Core\View;

$cards = [
    'templates/components/cards/card-00-portada',
    'templates/components/cards/card-01-historia',
    'templates/components/cards/card-02-cuenta-regresiva',
    'templates/components/cards/card-03-invitacion-familia',
    'templates/components/cards/card-04-lugar-fecha',
    'templates/components/cards/card-05-dress-code',
    'templates/components/cards/card-06-itinerario',
    'templates/components/cards/card-07-trivia',
    'templates/components/cards/card-08-playlist',
    'templates/components/cards/card-09-regalos',
    'templates/components/cards/card-10-rsvp-pase-vip',
    'templates/components/cards/card-11-fotos-en-vivo',
    'templates/components/cards/card-12-despedida',
];
?>
<div class="deck-container" id="deckContainer">
    <?php foreach ($cards as $cardPath): ?>
        <?php View::component($cardPath, $data ?? []); ?>
    <?php endforeach; ?>
</div>

<!-- Controles de Navegación -->
<div class="nav-dots" id="navDots"></div>
<div class="nav-arrows">
    <button class="nav-arrow" id="prevBtn" disabled>
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="nav-arrow" id="nextBtn">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>
