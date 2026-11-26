/**
 * ==============================================================================
 * XV EXPERIENCE — EL JARDÍN ENCANTADO V2.0 (MINIJUEGO DE MARIPOSAS)
 * Experiencia exclusiva para Sophia Valentina (sin colores verdes, WCAG 2.2 AA)
 * ==============================================================================
 */
(() => {
    'use strict';

    const cfg = Object.assign({
        eventoId: 2,
        eventoSlug: 'sophia-xv',
        festejada: 'Sophia Valentina',
        fechaTexto: '21 de diciembre de 2026',
        apiUrl: 'api/juego_recompensas.php',
        durationSec: 15,
        goalCaptures: 15,
        minActive: 2,
        maxActive: 4,
        goldenChance: 0.14
    }, window.SOPHIA_GAME_CONFIG || {});

    const STORAGE_KEY = `xv_game_rewards_${cfg.eventoSlug}`;

    // Elementos del DOM
    const gamePanel = document.getElementById('sophiaGamePanel');
    const arena = document.getElementById('sophiaGameArena');
    const fxCanvas = document.getElementById('sophiaFxCanvas');
    const targetsArea = document.getElementById('sophiaTargetsArea');
    const capturesEl = document.getElementById('sophiaCapturesCount');
    const pointsEl = document.getElementById('sophiaPointsCount');
    const timerEl = document.getElementById('sophiaGameTimer');
    const timerPill = document.getElementById('sophiaTimerPill');
    const progressBar = document.getElementById('sophiaProgressBar');
    const comboBadge = document.getElementById('sophiaComboBadge');
    const secretToast = document.getElementById('sophiaSecretToast');
    const countdownOverlay = document.getElementById('sophiaCountdownOverlay');
    const countdownNum = document.getElementById('sophiaCountdownNum');
    const startOverlay = document.getElementById('sophiaStartOverlay');
    const gameTitle = document.getElementById('sophiaGameTitle');
    const gameMsg = document.getElementById('sophiaGameMsg');
    const btnStart = document.getElementById('btnStartGame');
    const btnOpenSavedRewards = document.getElementById('btnOpenSavedRewards');
    const accessibleToggle = document.getElementById('sophiaAccessibleToggle');

    // Modal de Cofre y Recompensas
    const rewardModal = document.getElementById('sophiaRewardModal');
    const btnCloseRewardModal = document.getElementById('btnCloseRewardModal');
    const chestStage = document.getElementById('sophiaChestStage');
    const chestBtn = document.getElementById('btnOpenMagicChest');
    const chest3d = document.getElementById('sophiaChest3d');
    const showcase = document.getElementById('sophiaRewardsShowcase');
    const showcaseTitle = document.getElementById('sophiaRewardTitleBanner');
    const showcaseTabs = document.getElementById('sophiaShowcaseTabs');
    const btnTabCard = document.getElementById('btnTabCardReward');
    const btnTabBadge = document.getElementById('btnTabBadgeReward');
    const rewardCanvas = document.getElementById('sophiaRewardCanvas');
    const winnerNameInput = document.getElementById('sophiaWinnerNameInput');
    const btnSaveWinner = document.getElementById('btnSaveWinnerName');
    const winnerStatusEl = document.getElementById('sophiaWinnerRegisterStatus');
    const btnDownloadPng = document.getElementById('btnDownloadRewardPng');
    const btnShareReward = document.getElementById('btnShareReward');
    const btnPlayAgainReward = document.getElementById('btnPlayAgainFromReward');
    const btnContinueInv = document.getElementById('btnContinueInvitation');

    if (!gamePanel || !arena || !fxCanvas || !targetsArea || !btnStart) {
        return;
    }

    const ctx = fxCanvas.getContext('2d');
    const reducedMotionQuery = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;

    // Estado persistido por celebración
    function loadSavedRewards() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return null;
            const parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' ? parsed : null;
        } catch (e) {
            return null;
        }
    }

    function saveRewards(data) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        } catch (e) {}
    }

    let savedState = loadSavedRewards() || {
        chestUnlocked: false,
        secretUnlocked: false,
        bestPoints: 0,
        bestCombo: 0,
        winnerName: '',
        syncedToServer: false,
        updatedAt: null
    };

    function getPrefilledGuestName() {
        if (savedState.winnerName) return savedState.winnerName;
        const rsvpInput = document.getElementById('rsvp-nombre');
        if (rsvpInput && rsvpInput.value.trim()) return rsvpInput.value.trim();
        try {
            const stored = localStorage.getItem(`xv_rsvp_name_${cfg.eventoId}`);
            if (stored && stored.trim()) return stored.trim();
        } catch (e) {}
        return '';
    }

    // Estado de partida
    let state = 'idle'; // idle | countdown | playing | paused | ended
    let captures = 0;
    let points = 0;
    let timeLeft = cfg.durationSec;
    let combo = 0;
    let maxComboThisRun = 0;
    let lastCatchTime = 0;
    let secretCaughtThisRun = false;
    let accessibleMode = false;
    let currentViewReward = 'card'; // card | badge

    // Motores y temporizadores
    let rafId = null;
    let lastFrameTs = 0;
    let spawnAccumulator = 0;
    let countdownTimerId = null;
    let clockIntervalId = null;
    let secretToastTimeout = null;
    let comboTimeout = null;
    let nextButterflyId = 1;
    let activeButterflies = [];
    let particles = [];
    let ambientPetals = [];

    // ==========================================================================
    // 1. SÍNTESIS DE AUDIO MÁGICO (WEB AUDIO API — RESPETA SONIDO DE INVITACIÓN)
    // ==========================================================================
    let audioCtx = null;

    function isSoundAllowed() {
        return window.xvSoundEnabled !== false;
    }

    function getAudioCtx() {
        if (!isSoundAllowed()) return null;
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return null;
        if (!audioCtx) {
            try { audioCtx = new Ctx(); } catch (e) { return null; }
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume().catch(() => {});
        }
        return audioCtx;
    }

    function playTone(freq, duration, type = 'sine', gainVal = 0.08, delay = 0) {
        const ac = getAudioCtx();
        if (!ac) return;
        try {
            const t0 = ac.currentTime + delay;
            const osc = ac.createOscillator();
            const gain = ac.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, t0);
            gain.gain.setValueAtTime(0.0001, t0);
            gain.gain.exponentialRampToValueAtTime(gainVal, t0 + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, t0 + duration);
            osc.connect(gain);
            gain.connect(ac.destination);
            osc.start(t0);
            osc.stop(t0 + duration + 0.03);
        } catch (e) {}
    }

    function playCatchSound(kind, comboCount) {
        const step = Math.min(comboCount - 1, 6);
        const semitoneFactor = Math.pow(1.05946, step * 2);
        if (kind === 'golden') {
            [784, 987.77, 1174.66, 1567.98].forEach((f, idx) => {
                playTone(f, 0.34, 'triangle', 0.09, idx * 0.055);
            });
        } else if (kind === 'lavender') {
            playTone(659.25 * semitoneFactor, 0.22, 'triangle', 0.07, 0);
            playTone(987.77 * semitoneFactor, 0.24, 'sine', 0.055, 0.05);
        } else {
            playTone(523.25 * semitoneFactor, 0.18, 'sine', 0.075, 0);
            playTone(783.99 * semitoneFactor, 0.16, 'sine', 0.04, 0.04);
        }
    }

    function playCountdownTick(finalGo) {
        if (finalGo) {
            playTone(880, 0.25, 'triangle', 0.08, 0);
        } else {
            playTone(523.25, 0.14, 'sine', 0.05, 0);
        }
    }

    function playVictoryFanfare() {
        [523.25, 659.25, 783.99, 1046.5, 1318.5].forEach((f, i) => {
            playTone(f, 0.42, 'triangle', 0.08, i * 0.085);
        });
    }

    function playChestOpenSound() {
        [587.33, 739.99, 880, 1174.66, 1479.98, 1760].forEach((f, i) => {
            playTone(f, 0.55, 'sine', 0.085, i * 0.065);
        });
    }

    // ==========================================================================
    // 2. CANVAS DE PARTÍCULAS DORADAS Y PÉTALOS ROSADOS
    // ==========================================================================
    function resizeCanvas() {
        const w = arena.clientWidth || 340;
        const h = arena.clientHeight || 330;
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        fxCanvas.width = Math.round(w * dpr);
        fxCanvas.height = Math.round(h * dpr);
        fxCanvas.style.width = `${w}px`;
        fxCanvas.style.height = `${h}px`;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function seedAmbientPetals() {
        ambientPetals = [];
        const w = arena.clientWidth || 340;
        const h = arena.clientHeight || 330;
        const count = (reducedMotionQuery && reducedMotionQuery.matches) ? 6 : 16;
        const petalPalette = ['#EBC5CC', '#D9A5B5', '#C6AEDB', '#EAD8B8'];
        for (let i = 0; i < count; i++) {
            ambientPetals.push({
                x: Math.random() * w,
                y: Math.random() * h,
                r: 3 + Math.random() * 4.5,
                vy: 12 + Math.random() * 18,
                vx: -8 + Math.random() * 16,
                swayPhase: Math.random() * Math.PI * 2,
                rot: Math.random() * Math.PI * 2,
                vRot: (Math.random() - 0.5) * 1.4,
                alpha: 0.35 + Math.random() * 0.4,
                color: petalPalette[i % petalPalette.length]
            });
        }
    }

    function spawnBurstParticles(x, y, kind, count = 18) {
        const palettes = {
            pink: ['#C5A16A', '#EBC5CC', '#FFF3E7', '#D9A5B5'],
            lavender: ['#C6AEDB', '#C5A16A', '#FFF3E7', '#854761'],
            golden: ['#C5A16A', '#EAD8B8', '#FFF3E7', '#D9A5B5', '#854761'],
            victory: ['#C5A16A', '#EAD8B8', '#EBC5CC', '#D9A5B5', '#C6AEDB', '#FFF3E7']
        };
        const pal = palettes[kind] || palettes.pink;
        for (let i = 0; i < count; i++) {
            const angle = (Math.PI * 2 * i) / count + (Math.random() - 0.5) * 0.5;
            const speed = kind === 'victory' ? (70 + Math.random() * 190) : (45 + Math.random() * 110);
            particles.push({
                x,
                y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed - (kind === 'victory' ? 40 : 15),
                size: (kind === 'golden' || kind === 'victory') ? (2.5 + Math.random() * 4) : (2 + Math.random() * 3),
                life: 0,
                maxLife: kind === 'victory' ? (0.95 + Math.random() * 0.7) : (0.45 + Math.random() * 0.35),
                color: pal[i % pal.length],
                isPetal: kind === 'victory' && i % 2 === 0
            });
        }
    }

    function updateAndDrawFx(dt) {
        const w = arena.clientWidth || 340;
        const h = arena.clientHeight || 330;
        ctx.clearRect(0, 0, w, h);

        // Pétalos flotantes ambientales
        for (let i = 0; i < ambientPetals.length; i++) {
            const p = ambientPetals[i];
            p.swayPhase += dt * 1.6;
            p.x += (p.vx + Math.sin(p.swayPhase) * 14) * dt;
            p.y += p.vy * dt;
            p.rot += p.vRot * dt;
            if (p.y > h + 12) {
                p.y = -10;
                p.x = Math.random() * w;
            }
            if (p.x < -12) p.x = w + 10;
            if (p.x > w + 12) p.x = -10;

            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rot);
            ctx.globalAlpha = p.alpha;
            ctx.fillStyle = p.color;
            ctx.beginPath();
            ctx.ellipse(0, 0, p.r, p.r * 0.58, 0, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }

        // Partículas de captura / explosión
        for (let i = particles.length - 1; i >= 0; i--) {
            const pt = particles[i];
            pt.life += dt;
            if (pt.life >= pt.maxLife) {
                particles.splice(i, 1);
                continue;
            }
            pt.vy += 120 * dt; // gravedad suave
            pt.x += pt.vx * dt;
            pt.y += pt.vy * dt;

            const ratio = 1 - pt.life / pt.maxLife;
            ctx.save();
            ctx.globalAlpha = Math.max(0, ratio);
            ctx.fillStyle = pt.color;
            ctx.translate(pt.x, pt.y);
            if (pt.isPetal) {
                ctx.rotate(pt.life * 4);
                ctx.beginPath();
                ctx.ellipse(0, 0, pt.size * 1.4, pt.size * 0.8, 0, 0, Math.PI * 2);
                ctx.fill();
            } else {
                // Destello romboidal dorado
                const s = pt.size * (0.6 + ratio * 0.5);
                ctx.beginPath();
                ctx.moveTo(0, -s * 1.4);
                ctx.lineTo(s * 0.7, 0);
                ctx.lineTo(0, s * 1.4);
                ctx.lineTo(-s * 0.7, 0);
                ctx.closePath();
                ctx.fill();
            }
            ctx.restore();
        }

        // Destellos sutiles en mariposas doradas activas
        for (let i = 0; i < activeButterflies.length; i++) {
            const b = activeButterflies[i];
            if (b.kind === 'golden' && !b.caught && Math.random() < 0.35) {
                particles.push({
                    x: b.x + 26 + (Math.random() - 0.5) * 18,
                    y: b.y + 26 + (Math.random() - 0.5) * 18,
                    vx: (Math.random() - 0.5) * 20,
                    vy: -10 - Math.random() * 20,
                    size: 1.8 + Math.random() * 2,
                    life: 0,
                    maxLife: 0.4,
                    color: '#C5A16A',
                    isPetal: false
                });
            }
        }
    }

    // ==========================================================================
    // 3. MARIPOSAS SVG ARTESANALES (SIN EMOJIS NI VERDES)
    // ==========================================================================
    const BUTTERFLY_SPECS = {
        pink: {
            label: 'Mariposa Rosada',
            pts: 1,
            speed: 98,
            ttlMs: 3600,
            flapSec: 0.32,
            size: 52,
            wingTop1: '#FFF3E7',
            wingTop2: '#EBC5CC',
            wingBot: '#D9A5B5',
            stroke: '#854761',
            vein: '#C5A16A',
            body: '#4A303B'
        },
        lavender: {
            label: 'Mariposa Lavanda',
            pts: 2,
            speed: 148,
            ttlMs: 2800,
            flapSec: 0.24,
            size: 50,
            wingTop1: '#FFF3E7',
            wingTop2: '#C6AEDB',
            wingBot: '#D9A5B5',
            stroke: '#854761',
            vein: '#C5A16A',
            body: '#4A303B'
        },
        golden: {
            label: 'Mariposa Dorada Secreta',
            pts: 5,
            speed: 175,
            ttlMs: 2100,
            flapSec: 0.19,
            size: 56,
            wingTop1: '#FFF3E7',
            wingTop2: '#EAD8B8',
            wingBot: '#C5A16A',
            stroke: '#854761',
            vein: '#4A303B',
            body: '#4A303B'
        }
    };

    function buildButterflyMarkup(kind, uid) {
        const sp = BUTTERFLY_SPECS[kind];
        return `
            <svg viewBox="0 0 64 64" class="sophia-bf-svg ${kind === 'golden' ? 'is-golden' : ''}" style="--flap-dur:${sp.flapSec}s;" aria-hidden="true">
                <defs>
                    <linearGradient id="bfGrad_${uid}" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="${sp.wingTop1}" />
                        <stop offset="55%" stop-color="${sp.wingTop2}" />
                        <stop offset="100%" stop-color="${sp.wingBot}" />
                    </linearGradient>
                </defs>
                <g class="bf-wing-left">
                    <path d="M32 31 C20 10, 4 14, 7 29 C9 39, 22 39, 32 33 Z" fill="url(#bfGrad_${uid})" stroke="${sp.stroke}" stroke-width="1.4" />
                    <path d="M32 34 C22 38, 13 49, 21 54 C27 57, 31 46, 32 36 Z" fill="${sp.wingBot}" stroke="${sp.stroke}" stroke-width="1.2" />
                    <path d="M29 29 Q17 20 12 25 M29 32 Q19 32 14 35" stroke="${sp.vein}" stroke-width="0.9" fill="none" opacity="0.85" />
                    <circle cx="15" cy="24" r="2" fill="#FFF3E7" opacity="0.85" />
                </g>
                <g class="bf-wing-right">
                    <path d="M32 31 C44 10, 60 14, 57 29 C55 39, 42 39, 32 33 Z" fill="url(#bfGrad_${uid})" stroke="${sp.stroke}" stroke-width="1.4" />
                    <path d="M32 34 C42 38, 51 49, 43 54 C37 57, 33 46, 32 36 Z" fill="${sp.wingBot}" stroke="${sp.stroke}" stroke-width="1.2" />
                    <path d="M35 29 Q47 20 52 25 M35 32 Q45 32 50 35" stroke="${sp.vein}" stroke-width="0.9" fill="none" opacity="0.85" />
                    <circle cx="49" cy="24" r="2" fill="#FFF3E7" opacity="0.85" />
                </g>
                <ellipse cx="32" cy="34" rx="2.4" ry="11" fill="${sp.body}" />
                <circle cx="32" cy="22" r="2.2" fill="${sp.body}" />
                <path d="M31 20 Q27 12 23 13 M33 20 Q37 12 41 13" stroke="${sp.vein}" stroke-width="1.2" fill="none" stroke-linecap="round" />
            </svg>
        `;
    }

    function pickButterflyKind(forceKind) {
        if (forceKind && BUTTERFLY_SPECS[forceKind]) return forceKind;
        const hasGoldenActive = activeButterflies.some(b => b.kind === 'golden' && !b.caught);
        const r = Math.random();
        if (!hasGoldenActive && r < cfg.goldenChance) {
            return 'golden';
        }
        if (r < cfg.goldenChance + 0.34) {
            return 'lavender';
        }
        return 'pink';
    }

    function spawnButterfly(forceKind = null) {
        if (state !== 'playing') return;
        const w = targetsArea.clientWidth || 320;
        const h = targetsArea.clientHeight || 280;
        const kind = pickButterflyKind(forceKind);
        const spec = BUTTERFLY_SPECS[kind];
        const size = spec.size;
        const margin = 8;

        const x = margin + Math.random() * Math.max(20, w - size - margin * 2);
        const y = margin + Math.random() * Math.max(20, h - size - margin * 2);

        const el = document.createElement('button');
        el.type = 'button';
        el.className = `sophia-catch-target kind-${kind}`;
        el.setAttribute('aria-label', `${spec.label} (+${spec.pts})`);
        el.style.width = `${size}px`;
        el.style.height = `${size}px`;

        const uid = nextButterflyId++;
        el.innerHTML = buildButterflyMarkup(kind, uid);

        const speedScale = accessibleMode
            ? 0.52
            : 1 + Math.min(0.6, ((cfg.durationSec - timeLeft) / cfg.durationSec) * 0.45 + (captures / cfg.goalCaptures) * 0.25);

        const ttlMs = accessibleMode ? spec.ttlMs * 2.2 : spec.ttlMs;

        const b = {
            id: uid,
            kind,
            spec,
            el,
            x,
            y,
            size,
            angle: Math.random() * Math.PI * 2,
            turnRate: (Math.random() - 0.5) * (kind === 'lavender' ? 4.2 : 2.2),
            waveFreq: 2.2 + Math.random() * 2.5,
            waveAmp: kind === 'lavender' ? 42 : 26,
            speed: spec.speed * speedScale,
            ageMs: 0,
            ttlMs,
            nextJukeMs: kind === 'lavender' ? (550 + Math.random() * 350) : 999999,
            caught: false
        };

        el.style.transform = `translate3d(${b.x}px, ${b.y}px, 0)`;

        el.addEventListener('pointerdown', (e) => {
            e.preventDefault();
            e.stopPropagation();
            handleButterflyCatch(b);
        });

        targetsArea.appendChild(el);
        activeButterflies.push(b);
    }

    function showFloatingPoints(x, y, pts, kind, comboVal) {
        const fx = document.createElement('div');
        fx.className = `catch-sparkle-fx kind-${kind}`;
        fx.textContent = comboVal >= 2 ? `+${pts} (x${comboVal})` : `+${pts}`;
        fx.style.left = `${Math.max(14, Math.min((targetsArea.clientWidth || 300) - 70, x + 8))}px`;
        fx.style.top = `${Math.max(8, y - 4)}px`;
        targetsArea.appendChild(fx);
        setTimeout(() => fx.remove(), 820);
    }

    function showComboBadge(comboVal) {
        if (!comboBadge) return;
        if (comboVal < 2) {
            comboBadge.classList.remove('visible');
            return;
        }
        comboBadge.textContent = `COMBO x${comboVal}`;
        comboBadge.classList.remove('visible');
        void comboBadge.offsetWidth;
        comboBadge.classList.add('visible');
        if (comboTimeout) clearTimeout(comboTimeout);
        comboTimeout = setTimeout(() => {
            comboBadge.classList.remove('visible');
        }, 1250);
    }

    function showSecretToast() {
        if (!secretToast) return;
        secretToast.classList.add('visible');
        if (secretToastTimeout) clearTimeout(secretToastTimeout);
        secretToastTimeout = setTimeout(() => {
            secretToast.classList.remove('visible');
        }, 2400);
    }

    function updateHud() {
        capturesEl.textContent = captures;
        pointsEl.textContent = points;
        if (progressBar) {
            const pct = Math.min(100, Math.round((captures / cfg.goalCaptures) * 100));
            progressBar.style.width = `${pct}%`;
        }
        if (accessibleMode) {
            timerEl.textContent = 'Libre';
            timerPill.classList.remove('is-urgent');
        } else {
            timerEl.textContent = `${Math.max(0, timeLeft)}s`;
            timerPill.classList.toggle('is-urgent', timeLeft <= 5 && state === 'playing');
        }
    }

    function handleButterflyCatch(b) {
        if (state !== 'playing' || b.caught) return;
        b.caught = true;
        b.el.style.pointerEvents = 'none';

        const now = performance.now();
        if (now - lastCatchTime <= 1700) {
            combo += 1;
        } else {
            combo = 1;
        }
        lastCatchTime = now;
        if (combo > maxComboThisRun) maxComboThisRun = combo;

        captures += 1;
        points += b.spec.pts;

        // Desbloquear insignia secreta si es mariposa dorada sin interrumpir la partida
        if (b.kind === 'golden') {
            secretCaughtThisRun = true;
            savedState.secretUnlocked = true;
            savedState.updatedAt = new Date().toISOString();
            saveRewards(savedState);
            updateSavedRewardsBtn();
            showSecretToast();
        }

        updateHud();
        showComboBadge(combo);
        playCatchSound(b.kind, combo);

        const cx = b.x + b.size / 2;
        const cy = b.y + b.size / 2;
        spawnBurstParticles(cx, cy, b.kind, b.kind === 'golden' ? 28 : 16);
        showFloatingPoints(b.x, b.y, b.spec.pts, b.kind, combo);

        b.el.classList.add('is-caught');
        setTimeout(() => {
            b.el.remove();
            const idx = activeButterflies.indexOf(b);
            if (idx !== -1) activeButterflies.splice(idx, 1);
        }, 180);

        if (captures >= cfg.goalCaptures) {
            finishGame(true);
            return;
        }

        // Mantener población continua entre 2 y 4 mariposas
        const activeCount = activeButterflies.filter(item => !item.caught).length;
        if (activeCount < cfg.minActive) {
            spawnButterfly();
        }
    }

    function updateButterflies(dt, dtMs) {
        const w = targetsArea.clientWidth || 320;
        const h = targetsArea.clientHeight || 280;

        for (let i = activeButterflies.length - 1; i >= 0; i--) {
            const b = activeButterflies[i];
            if (b.caught) continue;

            b.ageMs += dtMs;
            if (b.ageMs >= b.ttlMs) {
                b.caught = true;
                b.el.style.pointerEvents = 'none';
                b.el.classList.add('is-fading');
                setTimeout(() => {
                    b.el.remove();
                    const idx = activeButterflies.indexOf(b);
                    if (idx !== -1) activeButterflies.splice(idx, 1);
                }, 220);
                continue;
            }

            // Cambios inesperados de dirección para la mariposa lavanda
            if (b.ageMs >= b.nextJukeMs) {
                b.angle += (Math.random() > 0.5 ? 1 : -1) * (1.1 + Math.random() * 1.4);
                b.turnRate = (Math.random() - 0.5) * 4.8;
                b.nextJukeMs = b.ageMs + 520 + Math.random() * 400;
            }

            b.angle += b.turnRate * dt;
            const perpAngle = b.angle + Math.PI / 2;
            const waveOffset = Math.sin((b.ageMs / 1000) * b.waveFreq) * b.waveAmp;

            const vx = Math.cos(b.angle) * b.speed + Math.cos(perpAngle) * waveOffset;
            const vy = Math.sin(b.angle) * b.speed + Math.sin(perpAngle) * waveOffset;

            b.x += vx * dt;
            b.y += vy * dt;

            const maxX = Math.max(10, w - b.size - 6);
            const maxY = Math.max(10, h - b.size - 6);

            if (b.x < 6) {
                b.x = 6;
                b.angle = Math.PI - b.angle + (Math.random() - 0.5) * 0.4;
            } else if (b.x > maxX) {
                b.x = maxX;
                b.angle = Math.PI - b.angle + (Math.random() - 0.5) * 0.4;
            }

            if (b.y < 6) {
                b.y = 6;
                b.angle = -b.angle + (Math.random() - 0.5) * 0.4;
            } else if (b.y > maxY) {
                b.y = maxY;
                b.angle = -b.angle + (Math.random() - 0.5) * 0.4;
            }

            const deg = (Math.atan2(vy, vx) * 180 / Math.PI) + 90;
            const tilt = Math.max(-28, Math.min(28, Math.sin(deg * Math.PI / 180) * 24));
            b.el.style.transform = `translate3d(${b.x.toFixed(1)}px, ${b.y.toFixed(1)}px, 0) rotate(${tilt.toFixed(1)}deg)`;
        }

        // Generación continua entre minActive (2) y maxActive (4)
        spawnAccumulator += dtMs;
        const alive = activeButterflies.filter(x => !x.caught).length;
        if (alive < cfg.minActive) {
            spawnButterfly();
            spawnAccumulator = 0;
        } else if (alive < cfg.maxActive && spawnAccumulator >= (accessibleMode ? 900 : 520)) {
            spawnButterfly();
            spawnAccumulator = 0;
        }
    }

    // ==========================================================================
    // 4. BUCLE PRINCIPAL & CONTROL DE CICLO DE VIDA
    // ==========================================================================
    function mainLoop(ts) {
        if (!lastFrameTs) lastFrameTs = ts;
        const rawDtMs = ts - lastFrameTs;
        lastFrameTs = ts;
        const dtMs = Math.min(rawDtMs, 50);
        const dt = dtMs / 1000;

        if (state === 'playing') {
            updateButterflies(dt, dtMs);
        }
        updateAndDrawFx(dt);

        if (state === 'playing' || state === 'countdown' || particles.length > 0 || ambientPetals.length > 0) {
            rafId = requestAnimationFrame(mainLoop);
        } else {
            rafId = null;
        }
    }

    function ensureLoopRunning() {
        if (!rafId) {
            lastFrameTs = 0;
            rafId = requestAnimationFrame(mainLoop);
        }
    }

    function clearAllTimers() {
        if (countdownTimerId) {
            clearInterval(countdownTimerId);
            countdownTimerId = null;
        }
        if (clockIntervalId) {
            clearInterval(clockIntervalId);
            clockIntervalId = null;
        }
        if (secretToastTimeout) {
            clearTimeout(secretToastTimeout);
            secretToastTimeout = null;
        }
        if (comboTimeout) {
            clearTimeout(comboTimeout);
            comboTimeout = null;
        }
    }

    function clearActiveButterflies() {
        activeButterflies.forEach(b => b.el.remove());
        activeButterflies = [];
        targetsArea.innerHTML = '';
    }

    function startCountdownAndPlay() {
        clearAllTimers();
        clearActiveButterflies();
        closeRewardModal();

        accessibleMode = !!(accessibleToggle && accessibleToggle.checked);
        captures = 0;
        points = 0;
        combo = 0;
        maxComboThisRun = 1;
        lastCatchTime = 0;
        secretCaughtThisRun = false;
        timeLeft = cfg.durationSec;

        updateHud();
        resizeCanvas();
        if (ambientPetals.length === 0) seedAmbientPetals();

        startOverlay.style.display = 'none';
        arena.classList.remove('is-dimmed');
        state = 'countdown';
        ensureLoopRunning();

        let step = 3;
        countdownOverlay.style.display = 'flex';
        countdownNum.textContent = String(step);
        countdownNum.className = 'sophia-countdown-num pop';
        playCountdownTick(false);

        countdownTimerId = setInterval(() => {
            step -= 1;
            if (step > 0) {
                countdownNum.textContent = String(step);
                countdownNum.classList.remove('pop');
                void countdownNum.offsetWidth;
                countdownNum.classList.add('pop');
                playCountdownTick(false);
            } else {
                clearInterval(countdownTimerId);
                countdownTimerId = null;
                countdownOverlay.style.display = 'none';
                beginPlayingPhase();
            }
        }, 620);
    }

    function beginPlayingPhase() {
        state = 'playing';
        updateHud();
        spawnAccumulator = 0;

        // Aparecer de 2 a 3 mariposas de inmediato
        spawnButterfly('pink');
        spawnButterfly('lavender');
        if (Math.random() < 0.5) spawnButterfly();

        ensureLoopRunning();

        if (!accessibleMode) {
            clockIntervalId = setInterval(() => {
                if (state !== 'playing') return;
                timeLeft -= 1;
                updateHud();
                if (timeLeft <= 0) {
                    finishGame(false);
                }
            }, 1000);
        }
    }

    function finishGame(wonChest) {
        clearAllTimers();
        state = 'ended';
        clearActiveButterflies();
        updateHud();

        if (points > savedState.bestPoints) savedState.bestPoints = points;
        if (maxComboThisRun > savedState.bestCombo) savedState.bestCombo = maxComboThisRun;
        if (wonChest) savedState.chestUnlocked = true;
        if (secretCaughtThisRun) savedState.secretUnlocked = true;
        savedState.updatedAt = new Date().toISOString();
        saveRewards(savedState);
        updateSavedRewardsBtn();

        if (wonChest) {
            arena.classList.add('is-dimmed');
            const w = arena.clientWidth || 340;
            const h = arena.clientHeight || 330;
            spawnBurstParticles(w / 2, h / 2, 'victory', 65);
            playVictoryFanfare();
            ensureLoopRunning();

            setTimeout(() => {
                openRewardExperience(true);
            }, 680);
        } else if (secretCaughtThisRun) {
            // Recompensa secreta se entrega aunque no alcance las 15 capturas
            playVictoryFanfare();
            openRewardExperience(false);
        } else {
            gameTitle.textContent = 'Tiempo Completado';
            gameMsg.innerHTML = `Atrapaste <strong>${captures} de ${cfg.goalCaptures}</strong> mariposas (${points} pts). ¡Inténtalo de nuevo para abrir el cofre mágico!`;
            btnStart.innerHTML = '<i class="fas fa-redo"></i> Jugar de Nuevo';
            startOverlay.style.display = 'flex';
        }
    }

    function abandonGameCleanup() {
        if (state === 'playing' || state === 'countdown') {
            clearAllTimers();
            clearActiveButterflies();
            state = 'idle';
            countdownOverlay.style.display = 'none';
            startOverlay.style.display = 'flex';
            btnStart.innerHTML = '<i class="fas fa-play"></i> Jugar Ahora';
        }
    }

    // ==========================================================================
    // 5. COFRE MÁGICO 3D, TARJETA CONMEMORATIVA PNG & INSIGNIA SECRETA (CANVAS)
    // ==========================================================================
    function openRewardExperience(showChestFirst) {
        if (!rewardModal) return;
        rewardModal.style.display = 'flex';
        prefillWinnerInput();

        const hasChest = savedState.chestUnlocked;
        const hasSecret = savedState.secretUnlocked;

        if (showChestFirst && hasChest) {
            chestStage.style.display = 'flex';
            showcase.style.display = 'none';
            chest3d.classList.remove('is-opening');
        } else {
            chestStage.style.display = 'none';
            showcase.style.display = 'flex';
            currentViewReward = hasChest ? 'card' : 'badge';
            renderShowcase();
        }
    }

    function closeRewardModal() {
        if (!rewardModal) return;
        rewardModal.style.display = 'none';
        arena.classList.remove('is-dimmed');
        if (state === 'ended') {
            gameTitle.textContent = savedState.chestUnlocked ? '¡Guardián de los Sueños!' : '¡Jardín Encantado!';
            gameMsg.innerHTML = `Última partida: <strong>${captures} / ${cfg.goalCaptures}</strong> mariposas · <strong>${points} puntos</strong>.`;
            btnStart.innerHTML = '<i class="fas fa-redo"></i> Volver a Jugar';
            startOverlay.style.display = 'flex';
        }
    }

    function handleChestTap() {
        if (!chest3d || chest3d.classList.contains('is-opening')) return;
        chest3d.classList.add('is-opening');
        playChestOpenSound();

        // Si ya tenemos el nombre del invitado (desde RSVP o guardado previo), registrar automáticamente en Admin
        const autoName = getPrefilledGuestName();
        if (autoName) {
            syncRewardToAdmin(autoName, true);
        }

        setTimeout(() => {
            chestStage.style.display = 'none';
            showcase.style.display = 'flex';
            currentViewReward = 'card';
            renderShowcase();
        }, 1050);
    }

    function prefillWinnerInput() {
        if (!winnerNameInput) return;
        const name = getPrefilledGuestName();
        if (name && !winnerNameInput.value) {
            winnerNameInput.value = name;
        }
        if (savedState.syncedToServer && savedState.winnerName) {
            setWinnerStatus(`Registrado en el salón real para: ${savedState.winnerName}`, 'success');
        } else {
            setWinnerStatus('Opcional: escribe tu nombre para personalizar tu recuerdo y aparecer en la lista de ganadores.', 'info');
        }
    }

    function setWinnerStatus(msg, kind) {
        if (!winnerStatusEl) return;
        winnerStatusEl.textContent = msg;
        winnerStatusEl.className = `sophia-register-status is-${kind}`;
    }

    async function syncRewardToAdmin(rawName, silentIfEmpty = false) {
        const cleanName = String(rawName || '').trim();
        if (cleanName.length < 2) {
            if (!silentIfEmpty) {
                setWinnerStatus('Escribe tu nombre (mínimo 2 caracteres) para guardar tu premio.', 'error');
            }
            return;
        }

        savedState.winnerName = cleanName;
        saveRewards(savedState);
        renderShowcase();

        if (btnSaveWinner) {
            btnSaveWinner.disabled = true;
            btnSaveWinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }

        try {
            const res = await fetch(cfg.apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    evento: cfg.eventoSlug,
                    evento_id: cfg.eventoId,
                    nombre_invitado: cleanName,
                    recompensa_cofre: savedState.chestUnlocked ? 1 : 0,
                    recompensa_secreta: savedState.secretUnlocked ? 1 : 0,
                    capturas: Math.max(captures, savedState.chestUnlocked ? cfg.goalCaptures : captures),
                    puntos: Math.max(points, savedState.bestPoints || 0),
                    combo_max: Math.max(maxComboThisRun, savedState.bestCombo || 1),
                    modo_accesible: accessibleMode ? 1 : 0
                })
            });
            const data = await res.json();
            if (data && data.success) {
                savedState.syncedToServer = true;
                saveRewards(savedState);
                setWinnerStatus(`¡Premio registrado con éxito para ${cleanName}!`, 'success');
            } else {
                setWinnerStatus((data && data.error) || 'No se pudo registrar en el servidor.', 'error');
            }
        } catch (err) {
            setWinnerStatus('Recuerdo personalizado localmente (sin conexión al servidor).', 'info');
        } finally {
            if (btnSaveWinner) {
                btnSaveWinner.disabled = false;
                btnSaveWinner.innerHTML = '<i class="fas fa-check"></i> Guardar';
            }
        }
    }

    function updateSavedRewardsBtn() {
        if (!btnOpenSavedRewards) return;
        if (savedState.chestUnlocked || savedState.secretUnlocked) {
            btnOpenSavedRewards.style.display = 'inline-flex';
        } else {
            btnOpenSavedRewards.style.display = 'none';
        }
    }

    function renderShowcase() {
        const hasBoth = savedState.chestUnlocked && savedState.secretUnlocked;
        if (showcaseTabs) {
            showcaseTabs.style.display = hasBoth ? 'flex' : 'none';
        }
        if (btnTabCard) btnTabCard.classList.toggle('active', currentViewReward === 'card');
        if (btnTabBadge) btnTabBadge.classList.toggle('active', currentViewReward === 'badge');

        if (currentViewReward === 'badge' && savedState.secretUnlocked) {
            showcaseTitle.textContent = 'La mariposa de los sueños';
            drawSecretBadgeCanvas(rewardCanvas, savedState.winnerName);
        } else {
            currentViewReward = 'card';
            showcaseTitle.textContent = 'Guardián de mis sueños';
            drawCommemorativeCardCanvas(rewardCanvas, savedState.winnerName);
        }
    }

    // Dibuja flor ornamental rosada/lavanda en Canvas (CERO VERDE)
    function drawCanvasFlower(c, x, y, radius, petalColors) {
        c.save();
        c.translate(x, y);
        for (let i = 0; i < 6; i++) {
            c.save();
            c.rotate((Math.PI * 2 * i) / 6);
            c.fillStyle = petalColors[i % petalColors.length];
            c.beginPath();
            c.ellipse(0, -radius * 0.72, radius * 0.42, radius * 0.7, 0, 0, Math.PI * 2);
            c.fill();
            c.restore();
        }
        c.fillStyle = '#C5A16A';
        c.beginPath();
        c.arc(0, 0, radius * 0.3, 0, Math.PI * 2);
        c.fill();
        c.restore();
    }

    // Dibuja mariposa vectorial dorada/editorial en Canvas
    function drawCanvasButterfly(c, x, y, scale, goldMode = true) {
        c.save();
        c.translate(x, y);
        c.scale(scale, scale);

        const g = c.createLinearGradient(-45, -35, 45, 35);
        if (goldMode) {
            g.addColorStop(0, '#FFF3E7');
            g.addColorStop(0.5, '#EAD8B8');
            g.addColorStop(1, '#C5A16A');
        } else {
            g.addColorStop(0, '#FFF3E7');
            g.addColorStop(0.5, '#EBC5CC');
            g.addColorStop(1, '#C6AEDB');
        }

        c.fillStyle = g;
        c.strokeStyle = '#854761';
        c.lineWidth = 2;

        // Ala sup izq
        c.beginPath();
        c.moveTo(0, -2);
        c.bezierCurveTo(-24, -44, -58, -32, -50, -4);
        c.bezierCurveTo(-45, 16, -20, 14, 0, 3);
        c.fill();
        c.stroke();

        // Ala sup der
        c.beginPath();
        c.moveTo(0, -2);
        c.bezierCurveTo(24, -44, 58, -32, 50, -4);
        c.bezierCurveTo(45, 16, 20, 14, 0, 3);
        c.fill();
        c.stroke();

        // Ala inf izq
        c.fillStyle = goldMode ? '#C5A16A' : '#D9A5B5';
        c.beginPath();
        c.moveTo(0, 4);
        c.bezierCurveTo(-22, 12, -38, 36, -20, 44);
        c.bezierCurveTo(-8, 49, -2, 26, 0, 8);
        c.fill();
        c.stroke();

        // Ala inf der
        c.beginPath();
        c.moveTo(0, 4);
        c.bezierCurveTo(22, 12, 38, 36, 20, 44);
        c.bezierCurveTo(8, 49, 2, 26, 0, 8);
        c.fill();
        c.stroke();

        // Cuerpo y antenas
        c.fillStyle = '#4A303B';
        c.beginPath();
        c.ellipse(0, 4, 4.5, 22, 0, 0, Math.PI * 2);
        c.fill();

        c.strokeStyle = '#C5A16A';
        c.lineWidth = 2;
        c.beginPath();
        c.moveTo(-2, -16);
        c.quadraticCurveTo(-12, -34, -20, -32);
        c.moveTo(2, -16);
        c.quadraticCurveTo(12, -34, 20, -32);
        c.stroke();

        c.restore();
    }

    function drawSparkleStar(c, x, y, r) {
        c.save();
        c.translate(x, y);
        c.fillStyle = '#C5A16A';
        c.beginPath();
        c.moveTo(0, -r);
        c.lineTo(r * 0.28, -r * 0.28);
        c.lineTo(r, 0);
        c.lineTo(r * 0.28, r * 0.28);
        c.lineTo(0, r);
        c.lineTo(-r * 0.28, r * 0.28);
        c.lineTo(-r, 0);
        c.lineTo(-r * 0.28, -r * 0.28);
        c.closePath();
        c.fill();
        c.restore();
    }

    function wrapCanvasText(c, text, x, y, maxWidth, lineHeight) {
        const words = text.split(' ');
        let line = '';
        let curY = y;
        for (let i = 0; i < words.length; i++) {
            const testLine = line + words[i] + ' ';
            if (c.measureText(testLine).width > maxWidth && i > 0) {
                c.fillText(line.trim(), x, curY);
                line = words[i] + ' ';
                curY += lineHeight;
            } else {
                line = testLine;
            }
        }
        if (line.trim()) {
            c.fillText(line.trim(), x, curY);
        }
        return curY;
    }

    function drawCommemorativeCardCanvas(canvas, guestName) {
        const W = 900;
        const H = 1200;
        canvas.width = W;
        canvas.height = H;
        const c = canvas.getContext('2d');

        // Fondo marfil y blush cálido
        const bg = c.createLinearGradient(0, 0, W, H);
        bg.addColorStop(0, '#FFF3E7');
        bg.addColorStop(0.55, '#FCECEF');
        bg.addColorStop(1, '#EBC5CC');
        c.fillStyle = bg;
        c.fillRect(0, 0, W, H);

        // Resplandor radial central
        const glow = c.createRadialGradient(W / 2, H * 0.34, 20, W / 2, H * 0.34, 420);
        glow.addColorStop(0, 'rgba(255, 255, 255, 0.92)');
        glow.addColorStop(1, 'rgba(255, 243, 231, 0)');
        c.fillStyle = glow;
        c.fillRect(0, 0, W, H);

        // Marco exterior champagne
        c.strokeStyle = '#C5A16A';
        c.lineWidth = 6;
        c.strokeRect(36, 36, W - 72, H - 72);

        c.strokeStyle = '#854761';
        c.lineWidth = 2;
        c.strokeRect(54, 54, W - 108, H - 108);

        // Guirnalda floral en esquinas
        const pinkSet = ['#EBC5CC', '#D9A5B5', '#C6AEDB'];
        drawCanvasFlower(c, 92, 92, 28, pinkSet);
        drawCanvasFlower(c, 148, 78, 18, ['#C6AEDB', '#EBC5CC']);
        drawCanvasFlower(c, W - 92, 92, 28, pinkSet);
        drawCanvasFlower(c, W - 148, 78, 18, ['#C6AEDB', '#EBC5CC']);
        drawCanvasFlower(c, 92, H - 92, 28, pinkSet);
        drawCanvasFlower(c, W - 92, H - 92, 28, pinkSet);

        // Destellos champagne
        drawSparkleStar(c, 220, 210, 14);
        drawSparkleStar(c, W - 220, 210, 14);
        drawSparkleStar(c, 160, 620, 10);
        drawSparkleStar(c, W - 160, 620, 10);

        // Encabezado editorial
        c.textAlign = 'center';
        c.fillStyle = '#854761';
        c.font = '700 22px "Montserrat", sans-serif';
        c.fillText('MIS XV AÑOS · RECUERDO OFICIAL', W / 2, 165);

        // Emblema mariposa central
        drawCanvasButterfly(c, W / 2, 290, 1.55, true);
        drawCanvasButterfly(c, W / 2 - 140, 255, 0.55, false);
        drawCanvasButterfly(c, W / 2 + 140, 255, 0.55, false);

        // Título oficial
        c.fillStyle = '#4A303B';
        c.font = '400 78px "Great Vibes", cursive';
        c.fillText('Guardián de mis sueños', W / 2, 435);

        // Divisor dorado
        c.strokeStyle = '#C5A16A';
        c.lineWidth = 2.5;
        c.beginPath();
        c.moveTo(W / 2 - 170, 470);
        c.lineTo(W / 2 + 170, 470);
        c.stroke();

        // Nombre personalizado si existe
        let nextY = 540;
        if (guestName && guestName.trim()) {
            c.fillStyle = '#854761';
            c.font = '700 24px "Montserrat", sans-serif';
            c.fillText('OTORGADO CON CARIÑO A', W / 2, nextY);
            c.fillStyle = '#4A303B';
            c.font = '700 38px "Playfair Display", Georgia, serif';
            c.fillText(guestName.trim(), W / 2, nextY + 52);
            nextY += 125;
        } else {
            nextY = 560;
        }

        // Caja editorial del mensaje
        c.fillStyle = 'rgba(255, 255, 255, 0.85)';
        c.strokeStyle = '#C5A16A';
        c.lineWidth = 2;
        c.beginPath();
        c.roundRect(110, nextY - 40, W - 220, 250, 24);
        c.fill();
        c.stroke();

        c.fillStyle = '#4A303B';
        c.font = '500 30px "Playfair Display", Georgia, serif';
        wrapCanvasText(
            c,
            '“Gracias por atrapar mis 15 mariposas y formar parte de la magia de mis quince años.”',
            W / 2,
            nextY + 45,
            W - 300,
            46
        );

        // Firma y Fecha
        c.fillStyle = '#854761';
        c.font = '400 68px "Great Vibes", cursive';
        c.fillText(cfg.festejada, W / 2, H - 185);

        c.fillStyle = '#79544D';
        c.font = '700 22px "Montserrat", sans-serif';
        c.fillText(cfg.fechaTexto.toUpperCase(), W / 2, H - 125);
    }

    function drawSecretBadgeCanvas(canvas, guestName) {
        const S = 960;
        canvas.width = S;
        canvas.height = S;
        const c = canvas.getContext('2d');

        // Fondo malva profundo de gala
        const bg = c.createRadialGradient(S / 2, S / 2, 40, S / 2, S / 2, S * 0.65);
        bg.addColorStop(0, '#5E3847');
        bg.addColorStop(0.6, '#4A303B');
        bg.addColorStop(1, '#2D1A22');
        c.fillStyle = bg;
        c.fillRect(0, 0, S, S);

        // Anillos champagne
        c.strokeStyle = '#C5A16A';
        c.lineWidth = 5;
        c.beginPath();
        c.arc(S / 2, S / 2, 415, 0, Math.PI * 2);
        c.stroke();

        c.strokeStyle = '#EBC5CC';
        c.lineWidth = 2;
        c.setLineDash([10, 8]);
        c.beginPath();
        c.arc(S / 2, S / 2, 392, 0, Math.PI * 2);
        c.stroke();
        c.setLineDash([]);

        // Medallón interior claro de alto contraste
        const innerGlow = c.createRadialGradient(S / 2, S / 2, 10, S / 2, S / 2, 355);
        innerGlow.addColorStop(0, '#FFF3E7');
        innerGlow.addColorStop(0.8, '#F9E6EA');
        innerGlow.addColorStop(1, '#EBC5CC');
        c.fillStyle = innerGlow;
        c.beginPath();
        c.arc(S / 2, S / 2, 355, 0, Math.PI * 2);
        c.fill();

        // Ornamentos florales rosados alrededor del medallón
        const flowerAngles = [-2.5, -1.9, -1.2, -0.6, 0.6, 1.2, 1.9, 2.5];
        flowerAngles.forEach((a, i) => {
            const fx = S / 2 + Math.cos(a) * 355;
            const fy = S / 2 + Math.sin(a) * 355;
            drawCanvasFlower(c, fx, fy, i % 2 === 0 ? 22 : 16, ['#EBC5CC', '#D9A5B5', '#C6AEDB']);
        });

        drawSparkleStar(c, S / 2 - 190, S / 2 - 150, 16);
        drawSparkleStar(c, S / 2 + 190, S / 2 - 150, 16);
        drawSparkleStar(c, S / 2, 195, 18);

        c.textAlign = 'center';
        c.fillStyle = '#854761';
        c.font = '700 20px "Montserrat", sans-serif';
        c.fillText('INSIGNIA SECRETA DESBLOQUEADA', S / 2, 265);

        // Gran mariposa dorada central
        drawCanvasButterfly(c, S / 2, 415, 2.0, true);

        c.fillStyle = '#4A303B';
        c.font = '400 72px "Great Vibes", cursive';
        c.fillText('La mariposa de los sueños', S / 2, 595);

        if (guestName && guestName.trim()) {
            c.fillStyle = '#854761';
            c.font = '700 26px "Playfair Display", Georgia, serif';
            c.fillText(guestName.trim(), S / 2, 650);
        }

        c.fillStyle = '#79544D';
        c.font = '600 20px "Montserrat", sans-serif';
        c.fillText(`${cfg.festejada} · ${cfg.fechaTexto}`, S / 2, 710);
    }

    function downloadCanvasPng() {
        if (!rewardCanvas) return;
        const suffix = currentViewReward === 'badge' ? 'insignia_mariposa_suenos' : 'guardian_de_mis_suenos';
        const dataUrl = rewardCanvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = `${cfg.eventoSlug}_${suffix}.png`;
        document.body.appendChild(link);
        link.click();
        link.remove();
    }

    async function shareCanvasReward() {
        if (!rewardCanvas) return;
        const title = currentViewReward === 'badge'
            ? 'La mariposa de los sueños — XV Sophia Valentina'
            : 'Guardián de mis sueños — XV Sophia Valentina';
        const text = `¡Desbloqueé un recuerdo mágico en los XV Años de ${cfg.festejada}!`;

        if (navigator.share && rewardCanvas.toBlob) {
            try {
                const blob = await new Promise(resolve => rewardCanvas.toBlob(resolve, 'image/png'));
                if (blob) {
                    const file = new File([blob], `${cfg.eventoSlug}_recuerdo.png`, { type: 'image/png' });
                    if (!navigator.canShare || navigator.canShare({ files: [file] })) {
                        await navigator.share({ title, text, files: [file] });
                        return;
                    }
                }
                await navigator.share({ title, text, url: window.location.href });
                return;
            } catch (e) {
                if (e && e.name === 'AbortError') return;
            }
        }
        downloadCanvasPng();
    }

    // ==========================================================================
    // 6. EVENTOS DE INTERFAZ, PAUSA EN PESTAÑA OCULTA Y NAVEGACIÓN
    // ==========================================================================
    btnStart.addEventListener('click', startCountdownAndPlay);

    if (btnOpenSavedRewards) {
        btnOpenSavedRewards.addEventListener('click', () => {
            openRewardExperience(false);
        });
    }

    if (chestBtn) {
        chestBtn.addEventListener('click', handleChestTap);
    }

    if (btnCloseRewardModal) {
        btnCloseRewardModal.addEventListener('click', closeRewardModal);
    }

    if (btnTabCard) {
        btnTabCard.addEventListener('click', () => {
            currentViewReward = 'card';
            renderShowcase();
        });
    }

    if (btnTabBadge) {
        btnTabBadge.addEventListener('click', () => {
            currentViewReward = 'badge';
            renderShowcase();
        });
    }

    if (btnSaveWinner && winnerNameInput) {
        btnSaveWinner.addEventListener('click', () => {
            syncRewardToAdmin(winnerNameInput.value, false);
        });
        winnerNameInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                syncRewardToAdmin(winnerNameInput.value, false);
            }
        });
    }

    if (btnDownloadPng) {
        btnDownloadPng.addEventListener('click', downloadCanvasPng);
    }

    if (btnShareReward) {
        btnShareReward.addEventListener('click', shareCanvasReward);
    }

    if (btnPlayAgainReward) {
        btnPlayAgainReward.addEventListener('click', startCountdownAndPlay);
    }

    if (btnContinueInv) {
        btnContinueInv.addEventListener('click', () => {
            closeRewardModal();
            if (typeof window.xvGoToNextSlide === 'function') {
                window.xvGoToNextSlide();
            }
        });
    }

    // Pausar si cambia visibilidad de pestaña
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && (state === 'playing' || state === 'countdown')) {
            clearAllTimers();
            clearActiveButterflies();
            state = 'paused';
            countdownOverlay.style.display = 'none';
            gameTitle.textContent = 'Partida en Pausa';
            gameMsg.innerHTML = 'Pausamos el jardín mientras cambiaste de pestaña. Toca continuar para una nueva ronda.';
            btnStart.innerHTML = '<i class="fas fa-play"></i> Jugar de Nuevo';
            startOverlay.style.display = 'flex';
        }
    });

    // Liberar temporizadores al salir de la tarjeta 8
    window.addEventListener('xv:slidechange', (e) => {
        const idx = e && e.detail ? e.detail.index : -1;
        if (idx !== 7) {
            abandonGameCleanup();
        } else {
            resizeCanvas();
            if (ambientPetals.length === 0) seedAmbientPetals();
            ensureLoopRunning();
        }
    });

    window.addEventListener('resize', () => {
        resizeCanvas();
    });

    // Inicialización visual inmediata (petals suaves, HUD nítido)
    resizeCanvas();
    seedAmbientPetals();
    updateHud();
    updateSavedRewardsBtn();
    ensureLoopRunning();

    // API segura de inspección para pruebas automatizadas (sin modificar experiencia normal)
    window.__sophiaGameDebug = {
        getState: () => ({
            state,
            captures,
            points,
            timeLeft,
            combo,
            maxComboThisRun,
            secretCaughtThisRun,
            accessibleMode,
            activeCount: activeButterflies.filter(b => !b.caught).length,
            activeKinds: activeButterflies.filter(b => !b.caught).map(b => b.kind),
            savedState: Object.assign({}, savedState)
        }),
        triggerSecretSpawn: () => {
            if (state === 'playing') {
                spawnButterfly('golden');
                return true;
            }
            return false;
        },
        openChestRewardForTest: () => {
            finishGame(true);
        }
    };
})();
