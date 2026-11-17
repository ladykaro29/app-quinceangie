<?php
/**
 * Componente Tarjeta 0: PORTADA
 * Plataforma: XV Experience
 */
?>
<div class="card-slide active" data-slide="0">
            <div class="card cover-card">
                <div class="corner-tl"></div>
                <div class="corner-tr"></div>
                <div class="corner-bl"></div>
                <div class="corner-br"></div>

                <div class="cover-tiara" style="margin-bottom: -8px; z-index: 6;">
                    <svg viewBox="0 0 140 85" class="tiara-svg" style="width: 88px;" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="tiaraGold" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#FFFDF5" />
                                <stop offset="25%" stop-color="#F7E7B4" />
                                <stop offset="50%" stop-color="#C8A24A" />
                                <stop offset="75%" stop-color="#E2CA7F" />
                                <stop offset="100%" stop-color="#8B6914" />
                            </linearGradient>
                            <linearGradient id="tiaraDiamond" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#FFFFFF" />
                                <stop offset="50%" stop-color="#F0F8FF" />
                                <stop offset="100%" stop-color="#D4E6F1" />
                            </linearGradient>
                        </defs>

                        <!-- Banda arqueada base -->
                        <path d="M 20 76 Q 70 82 120 76" stroke="url(#tiaraGold)" stroke-width="2.5" fill="none" stroke-linecap="round" />
                        <path d="M 24 72 Q 70 78 116 72" stroke="url(#tiaraGold)" stroke-width="1.2" fill="none" stroke-linecap="round" opacity="0.8" />

                        <!-- Perlas / Gemas en la banda base -->
                        <circle cx="30" cy="74.5" r="1.8" fill="url(#tiaraDiamond)" />
                        <circle cx="43" cy="76" r="1.8" fill="url(#tiaraDiamond)" />
                        <circle cx="56" cy="77" r="1.8" fill="url(#tiaraDiamond)" />
                        <circle cx="70" cy="77.5" r="2.2" fill="url(#tiaraDiamond)" />
                        <circle cx="84" cy="77" r="1.8" fill="url(#tiaraDiamond)" />
                        <circle cx="97" cy="76" r="1.8" fill="url(#tiaraDiamond)" />
                        <circle cx="110" cy="74.5" r="1.8" fill="url(#tiaraDiamond)" />

                        <!-- Arco central majestuoso -->
                        <path d="M 70 77 C 62 60 52 40 70 12 C 88 40 78 60 70 77 Z" fill="none" stroke="url(#tiaraGold)" stroke-width="2" />
                        <path d="M 70 77 C 65 62 60 48 70 28 C 80 48 75 62 70 77 Z" fill="none" stroke="url(#tiaraGold)" stroke-width="1.2" opacity="0.75" />

                        <!-- Arcos laterales -->
                        <path d="M 50 75 C 44 56 36 42 46 25 C 56 42 62 58 54 75" fill="none" stroke="url(#tiaraGold)" stroke-width="1.8" />
                        <path d="M 90 75 C 96 56 104 42 94 25 C 84 42 78 58 86 75" fill="none" stroke="url(#tiaraGold)" stroke-width="1.8" />

                        <!-- Filigranas interiores -->
                        <path d="M 70 65 C 64 54 60 46 70 38 C 80 46 76 54 70 65" fill="none" stroke="url(#tiaraGold)" stroke-width="1.2" />
                        <path d="M 46 68 C 38 58 35 48 42 38 C 48 48 48 58 46 68" fill="none" stroke="url(#tiaraGold)" stroke-width="1.2" />
                        <path d="M 94 68 C 102 58 105 48 98 38 C 92 48 92 58 94 68" fill="none" stroke="url(#tiaraGold)" stroke-width="1.2" />

                        <!-- Espirales exteriores -->
                        <path d="M 28 73 C 20 62 14 52 24 40 C 32 50 36 62 30 73" fill="none" stroke="url(#tiaraGold)" stroke-width="1.5" />
                        <path d="M 112 73 C 120 62 126 52 116 40 C 108 50 104 62 110 73" fill="none" stroke="url(#tiaraGold)" stroke-width="1.5" />

                        <!-- Joyas centrales en gota -->
                        <path d="M 70 36 C 66 42 66 48 70 52 C 74 48 74 42 70 36 Z" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.8" />
                        <path d="M 46 44 C 43 48 43 53 46 56 C 49 53 49 48 46 44 Z" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.7" />
                        <path d="M 94 44 C 91 48 91 53 94 56 C 97 53 97 48 94 44 Z" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.7" />

                        <!-- Cristales en las cúspides y estrella brillante -->
                        <circle cx="70" cy="11" r="3.5" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="1" />
                        <path d="M 70 3 L 71.5 9 L 77.5 10.5 L 71.5 12 L 70 18 L 68.5 12 L 62.5 10.5 L 68.5 9 Z" fill="#FFFDF5" opacity="0.95" />

                        <circle cx="46" cy="24" r="2.8" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.8" />
                        <circle cx="94" cy="24" r="2.8" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.8" />
                        <circle cx="24" cy="39" r="2.2" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.8" />
                        <circle cx="116" cy="39" r="2.2" fill="url(#tiaraDiamond)" stroke="url(#tiaraGold)" stroke-width="0.8" />

                        <!-- Destellos finos en los picos laterales -->
                        <path d="M 46 19 L 47 23 L 51 24 L 47 25 L 46 29 L 45 25 L 41 24 L 45 23 Z" fill="#FFFFFF" opacity="0.85" />
                        <path d="M 94 19 L 95 23 L 99 24 L 95 25 L 94 29 L 93 25 L 89 24 L 93 23 Z" fill="#FFFFFF" opacity="0.85" />
                    </svg>
                </div>

                <!-- Marco elegante real con la foto de Angie -->
                <div class="cover-photo-container">
                    <div class="cover-photo-wrapper">
                        <div class="cover-photo-frame">
                            <img src="angie-portada.jpg" onerror="if(!this.dataset.retry){this.dataset.retry=1;this.src='public/angie-portada.jpg';}" alt="Angie Karolina - Mis XV Años" class="cover-photo-img" loading="eager">
                            <div class="cover-photo-sheen"></div>
                        </div>
