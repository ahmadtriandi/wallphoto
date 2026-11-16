@extends('layouts.app')

@section('title', 'Live Photo Wall')

@php $pengaturan = \App\Models\Pengaturan::semua(); @endphp

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background: #1a0533;
        overflow: hidden;
        font-family: 'Plus Jakarta Sans', sans-serif;
        height: 100vh;
        width: 100vw;
    }

    nav { display: none !important; }
    main { padding: 0 !important; max-width: 100% !important; overflow: hidden; }

    .wall-container { position: fixed; inset: 0; overflow: hidden; }

    /* ── Background (diatur dari dashboard) ── */
    .bg-layer {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse at 15% 60%, rgba(140,20,180,0.7) 0%, transparent 45%),
            radial-gradient(ellipse at 70% 10%, rgba(200,20,80,0.6) 0%, transparent 40%),
            radial-gradient(ellipse at 85% 80%, rgba(80,0,160,0.5) 0%, transparent 40%),
            linear-gradient(135deg, #1a0030 0%, #2d0060 40%, #1a0040 70%, #2a0010 100%);
        background-size: cover;
        background-position: center;
        transition: background-color 0.6s ease;
    }

    .wave-deco {
        position: absolute;
        bottom: -60px; right: -60px;
        width: 400px; height: 400px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(200,20,80,0.4) 0%, transparent 70%);
        filter: blur(40px);
        pointer-events: none;
    }

    .dots {
        position: absolute;
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
        opacity: 0.35;
        pointer-events: none;
    }
    .dots span { width: 5px; height: 5px; background: #ff3d7f; border-radius: 50%; display: block; }

    .wall-container.bg-polos .wave-deco,
    .wall-container.bg-polos .dots { display: none; }

    /* ── Area wall ── */
    #wall {
        position: absolute;
        left: 0; top: 0;
        width: calc(100vw - 320px);
        height: 100vh;
    }

    /* ── Kartu foto ── */
    .foto-card {
        position: absolute;
        width: 260px;
        cursor: pointer;
        opacity: 0;
        filter: drop-shadow(0 16px 40px rgba(0,0,0,0.5));
    }

    .foto-card:hover { z-index: 999 !important; }

    .card-inner {
        --rot: 0deg;
        --s: 1;
        border-radius: 18px;
        overflow: hidden;
        border: 2px solid rgba(255,61,127,0.5);
        background: linear-gradient(160deg, #3d0080 0%, #1a0050 100%);
        transform: rotate(var(--rot)) scale(var(--s));
        transition: transform 0.3s ease;
    }

    .foto-card:hover .card-inner {
        transform: rotate(0deg) scale(calc(var(--s) * 1.06)) !important;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
    }

    .card-inner img { width: 100%; height: 200px; object-fit: cover; display: block; }

    /* Frame photobooth aktif: foto tampil utuh dengan rasio 2:3 supaya frame tidak terpotong */
    .pakai-frame .card-inner img { height: 390px; }
    .pakai-frame .spot-card { width: min(420px, 80vw, 40vh); }
    .pakai-frame .spot-card img { max-height: none; aspect-ratio: 2 / 3; }

    .card-bottom {
        padding: 10px 14px 12px;
        position: relative;
        background: linear-gradient(135deg, #350070 0%, #6b0fa8 100%);
    }

    .card-bottom::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, #ff3d7f, #c0134e, #ff3d7f);
    }

    .card-dots {
        position: absolute;
        right: 8px; top: 8px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 3px;
        opacity: 0.4;
    }
    .card-dots span { width: 4px; height: 4px; background: #ff3d7f; border-radius: 50%; display: block; }

    .wish-label {
        font-family: 'Bebas Neue', cursive;
        font-size: 12px;
        letter-spacing: 3px;
        color: #ff3d7f;
        text-decoration: underline;
        text-underline-offset: 3px;
        margin-bottom: 5px;
    }

    .caption-text {
        font-size: 13px;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.4;
        height: 2.8em;            /* selalu 2 baris, supaya tinggi kartu seragam */
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow-wrap: anywhere;
    }

    .chevrons { font-size: 11px; color: #ff3d7f; letter-spacing: -3px; opacity: 0.5; margin-top: 4px; }

    @keyframes cardEnterBottom { from { opacity: 0; transform: translateY(80px) scale(0.7); }  to { opacity: 1; transform: none; } }
    @keyframes cardEnterLeft   { from { opacity: 0; transform: translateX(-80px) scale(0.7); } to { opacity: 1; transform: none; } }
    @keyframes cardEnterRight  { from { opacity: 0; transform: translateX(80px) scale(0.7); }  to { opacity: 1; transform: none; } }
    @keyframes cardEnterTop    { from { opacity: 0; transform: translateY(-80px) scale(0.7); } to { opacity: 1; transform: none; } }
    @keyframes cardFade        { from { opacity: 0; transform: scale(0.9); }                   to { opacity: 1; transform: none; } }
    @keyframes joinWall {
        0%   { opacity: 0; transform: scale(2.2); filter: brightness(2); }
        60%  { opacity: 1; transform: scale(1.05); filter: brightness(1.2); }
        100% { opacity: 1; transform: none; filter: none; }
    }

    /* ── Spotlight foto baru (tengah layar) ── */
    .spot-overlay {
        position: fixed;
        inset: 0;
        z-index: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0);
        pointer-events: none;
        transition: background 0.5s ease;
    }
    .spot-overlay.on { background: rgba(0,0,0,0.75); }

    .spot-card {
        width: min(420px, 80vw);
        border-radius: 22px;
        overflow: hidden;
        border: 3px solid rgba(255,61,127,0.8);
        box-shadow: 0 0 80px rgba(255,61,127,0.6), 0 0 160px rgba(140,20,180,0.4);
        opacity: 0;
        transform: scale(0.3);
        filter: brightness(3) blur(10px);
        transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .spot-card.show { opacity: 1; transform: scale(1); filter: none; }
    .spot-card.hide {
        opacity: 0;
        transform: scale(0.15);
        filter: brightness(3) blur(6px);
        transition: all 0.7s cubic-bezier(0.4, 0, 1, 1);
    }
    .spot-card img { width: 100%; max-height: 62vh; object-fit: cover; display: block; background: #000; }
    .spot-card .card-bottom { padding: 14px 18px 18px; }
    .spot-card .wish-label { font-size: 14px; }
    .spot-card .caption-text { font-size: 18px; height: auto; -webkit-line-clamp: 3; }
    .spot-meta { font-size: 12px; color: rgba(255,255,255,0.55); margin-top: 6px; }

    /* ── Panel kanan: QR ── */
    .right-panel {
        position: fixed;
        right: 0; top: 0;
        width: 320px;
        height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px 24px;
        gap: 24px;
        background: rgba(255,255,255,0.04);
        backdrop-filter: blur(20px);
        border-left: 1px solid rgba(255,61,127,0.2);
        z-index: 100;
    }

    .qr-wrapper {
        background: #ffffff;
        border-radius: 20px;
        padding: 16px;
        width: 220px; height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 0 4px rgba(255,61,127,0.3), 0 20px 60px rgba(0,0,0,0.4);
    }
    .qr-wrapper img, .qr-wrapper canvas { width: 188px !important; height: 188px !important; }

    .qr-info { text-align: center; }
    .qr-title { font-size: 18px; font-weight: 800; color: #ffffff; margin-bottom: 8px; }
    .qr-desc { font-size: 13px; color: rgba(255,255,255,0.7); line-height: 1.7; }
    .qr-url {
        font-size: 11px;
        color: #ff3d7f;
        word-break: break-all;
        margin-top: 10px;
        background: rgba(255,61,127,0.1);
        padding: 6px 10px;
        border-radius: 8px;
        border: 1px solid rgba(255,61,127,0.3);
    }

    /* ── Badge live ── */
    .live-badge {
        position: fixed;
        top: 16px;
        left: calc((100vw - 320px) / 2);
        transform: translateX(-50%);
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,61,127,0.4);
        border-radius: 50px;
        padding: 8px 18px;
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 200;
        color: white;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1px;
        white-space: nowrap;
    }
    .live-dot { width: 8px; height: 8px; background: #ff3d7f; border-radius: 50%; animation: pulseDot 1.2s ease-in-out infinite; }
    @keyframes pulseDot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.6; transform: scale(0.8); } }
    .foto-count {
        background: rgba(255,61,127,0.25);
        border: 1px solid rgba(255,61,127,0.4);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 12px;
    }

    .empty-state {
        position: absolute;
        inset: 0;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.4);
        gap: 10px;
        pointer-events: none;
    }
    .empty-state h2 { font-family: 'Bebas Neue', cursive; font-size: 36px; letter-spacing: 6px; }
    .empty-state p { font-size: 14px; letter-spacing: 1px; }

    /* ── Lightbox ── */
    .lb-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.88);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .lb-overlay.open { opacity: 1; pointer-events: all; }
    .lb-card {
        max-width: 460px;
        width: 100%;
        border-radius: 20px;
        overflow: hidden;
        border: 2px solid rgba(255,61,127,0.5);
        background: linear-gradient(160deg, #3d0080, #1a0050);
        transform: scale(0.85);
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
    }
    .lb-overlay.open .lb-card { transform: scale(1); }
    .lb-card img { width: 100%; max-height: 65vh; object-fit: contain; background: #000; display: block; }
    .lb-info { padding: 16px 20px 20px; }
    .lb-info .wish-label { font-size: 13px; margin-bottom: 6px; }
    .lb-info .caption { font-size: 17px; font-weight: 800; color: #fff; overflow-wrap: anywhere; }
    .lb-info .meta { font-size: 12px; color: rgba(255,255,255,0.55); margin-top: 5px; }
    .lb-close {
        position: absolute;
        top: 12px; right: 12px;
        background: rgba(255,61,127,0.3);
        border: 1px solid rgba(255,61,127,0.5);
        color: white;
        width: 34px; height: 34px;
        border-radius: 50%;
        font-size: 16px;
        cursor: pointer;
        z-index: 10;
    }
    .lb-close:hover { background: rgba(255,61,127,0.7); }
</style>
@endpush

@section('content')

<div class="wall-container" id="wall-container">

    <div class="bg-layer" id="bg-layer"></div>
    <div class="wave-deco"></div>

    <div class="dots" style="top:8%;left:3%">
        @for ($i = 0; $i < 18; $i++)<span></span>@endfor
    </div>
    <div class="dots" style="bottom:12%;left:38%">
        @for ($i = 0; $i < 12; $i++)<span></span>@endfor
    </div>

    <div id="wall">
        <div class="empty-state" id="empty-state">
            <h2>BELUM ADA FOTO</h2>
            <p>Scan QR Code untuk upload foto pertama</p>
        </div>
    </div>

    <div class="live-badge">
        <span class="live-dot"></span>
        LIVE WALL
        <span class="foto-count" id="live-count">0 foto</span>
    </div>

    <div class="right-panel">
        <div class="qr-wrapper"><div id="qr-container"></div></div>
        <div class="qr-info">
            <div class="qr-title">Upload Foto Kamu!</div>
            <div class="qr-desc">
                Scan QR Code berikut ini<br>
                untuk upload foto kamu<br>
                ke Live Wall
            </div>
            <div class="qr-url" id="upload-url-display"></div>
        </div>
    </div>

    <div class="spot-overlay" id="spotlight"></div>

    <div class="lb-overlay" id="lightbox">
        <div class="lb-card">
            <button class="lb-close" id="lb-close" aria-label="Tutup">&#10005;</button>
            <img id="lb-img" src="" alt="">
            <div class="lb-info">
                <div class="wish-label">Wish</div>
                <div class="caption" id="lb-caption"></div>
                <div class="meta" id="lb-meta"></div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
// Pengaturan dari dashboard (/dashboard). Berubah otomatis lewat event "pengaturan.diubah".
let settings = @json($pengaturan);

const UPLOAD_URL = window.location.origin + '/upload';
const CARD_W = 260;
let   cardH  = 292;

// cards: urut dari foto TERLAMA ke TERBARU
const state = {
    cards: [], moveTimer: null, spotQueue: [], spotBusy: false, spotNow: null,
    seen: new Set(),        // id semua foto yang sudah dikenal wall
    addedAt: new Map(),     // id → waktu masuk, supaya foto yang baru masuk tidak salah dihapus
    memuat: false,
};

const container  = document.getElementById('wall-container');
const bgLayer    = document.getElementById('bg-layer');
const wall       = document.getElementById('wall');
const emptyState = document.getElementById('empty-state');
const liveCount  = document.getElementById('live-count');
const spotlight  = document.getElementById('spotlight');
const lightbox   = document.getElementById('lightbox');

const rand    = (min, max) => Math.random() * (max - min) + min;
const randInt = (min, max) => Math.floor(rand(min, max + 1));
const wait    = (ms) => new Promise(r => setTimeout(r, ms));
const isGrid  = () => settings.wall_mode === 'grid';

// ── QR code ───────────────────────────────────────────
document.getElementById('upload-url-display').textContent = UPLOAD_URL;
new QRCode(document.getElementById('qr-container'), {
    text: UPLOAD_URL, width: 188, height: 188,
    colorDark: '#1a0033', colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
});

// ── Background ────────────────────────────────────────
function applyBackground() {
    const type = settings.bg_type;
    if (type === 'image' && settings.bg_image) {
        bgLayer.style.background = '#000';
        bgLayer.style.backgroundImage = 'url(' + JSON.stringify(settings.bg_image) + ')';
        bgLayer.style.backgroundSize = 'cover';
        bgLayer.style.backgroundPosition = 'center';
    } else if (type === 'color') {
        bgLayer.style.background = settings.bg_color;
    } else {
        bgLayer.removeAttribute('style');   // kembali ke gradient bawaan di CSS
    }
    container.classList.toggle('bg-polos', type !== 'gradient');
}

// Frame aktif → kartu lebih tinggi (rasio 2:3). Ukur ulang tinggi kartu setelahnya.
function applyFrameMode() {
    container.classList.toggle('pakai-frame', settings.frame === 'aktif');
    if (state.cards[0] && state.cards[0].el.offsetHeight > 0) cardH = state.cards[0].el.offsetHeight;
}

// ── Isi kartu (dipakai wall dan spotlight) ────────────
function buildCardBody(foto, besar) {
    const frag = document.createDocumentFragment();

    const img = document.createElement('img');
    img.src = besar ? foto.url : (foto.thumbnail_url || foto.url);
    img.alt = foto.caption || '';
    frag.appendChild(img);

    const bottom = document.createElement('div');
    bottom.className = 'card-bottom';

    const dots = document.createElement('div');
    dots.className = 'card-dots';
    for (let i = 0; i < 16; i++) dots.appendChild(document.createElement('span'));

    const wish = document.createElement('div');
    wish.className = 'wish-label';
    wish.textContent = 'Wish';

    const cap = document.createElement('div');
    cap.className = 'caption-text';
    cap.textContent = foto.caption || '';

    bottom.append(dots, wish, cap);

    if (besar) {
        const meta = document.createElement('div');
        meta.className = 'spot-meta';
        meta.textContent = (foto.uploader || 'Anonim') + ' · ' + (foto.waktu || '');
        bottom.appendChild(meta);
    } else {
        const chev = document.createElement('div');
        chev.className = 'chevrons';
        chev.textContent = '«««««';
        bottom.appendChild(chev);
    }

    frag.appendChild(bottom);
    return frag;
}

// ── Posisi kartu ──────────────────────────────────────
function setCard(c, x, y, rot, scale, z, dur) {
    c.el.style.transition = dur ? `left ${dur}s cubic-bezier(0.4,0,0.2,1), top ${dur}s cubic-bezier(0.4,0,0.2,1)` : 'none';
    c.el.style.left   = x + 'px';
    c.el.style.top    = y + 'px';
    c.el.style.zIndex = z;
    c.inner.style.transition = dur ? `transform ${dur}s cubic-bezier(0.4,0,0.2,1)` : 'none';
    c.inner.style.setProperty('--rot', rot + 'deg');
    c.inner.style.setProperty('--s', scale);
}

function placeRandom(c, dur) {
    const maxX = Math.max(10, wall.clientWidth  - CARD_W - 10);
    const maxY = Math.max(60, wall.clientHeight - cardH  - 10);
    setCard(c, rand(10, maxX), rand(60, maxY), rand(-9, 9), 1, randInt(1, 60), dur);
}

// Mode grid: kartu disusun rapi, ukurannya menyesuaikan jumlah foto dan ukuran layar
function layoutGrid(dur) {
    const n = state.cards.length;
    if (!n) return;

    const padX = 16, top = 64, bottom = 16, gap = 18;
    const availW = wall.clientWidth - padX * 2;
    const availH = wall.clientHeight - top - bottom;

    let best = { cols: 1, rows: n, scale: 0 };
    for (let cols = 1; cols <= n; cols++) {
        const rows  = Math.ceil(n / cols);
        const scale = Math.min((availW / cols - gap) / CARD_W, (availH / rows - gap) / cardH);
        if (scale > best.scale) best = { cols, rows, scale };
    }

    const { cols, rows } = best;
    const scale = Math.max(0.2, Math.min(best.scale, 1.5));
    const cellW = availW / cols;
    const cellH = Math.min(availH / rows, cardH * scale + gap);
    const offY  = top + (availH - cellH * rows) / 2;

    // foto terbaru di kiri atas
    [...state.cards].reverse().forEach((c, i) => {
        const col = i % cols;
        const row = Math.floor(i / cols);
        const isiBaris = row === rows - 1 ? n - cols * (rows - 1) : cols;
        const offX = padX + (cols - isiBaris) * cellW / 2;   // baris terakhir ditengahkan
        const cx = offX + cellW * (col + 0.5);
        const cy = offY + cellH * (row + 0.5);
        setCard(c, cx - CARD_W / 2, cy - cardH / 2, 0, scale, 10, dur);
    });
}

function relayout(dur) {
    if (isGrid()) layoutGrid(dur);
    else state.cards.forEach(c => placeRandom(c, dur));
}

// ── Gerak acak (hanya mode "acak") ────────────────────
function startMotion() {
    clearInterval(state.moveTimer);
    state.moveTimer = null;
    if (isGrid()) return;

    const jeda = Math.max(2, Number(settings.move_interval) || 3);
    state.moveTimer = setInterval(() => {
        state.cards.forEach((c, i) => {
            setTimeout(() => {
                if (!isGrid() && c.el.isConnected) {
                    placeRandom(c, Math.min(rand(1.5, 2.5), jeda * 0.9));
                }
            }, i * 150);
        });
    }, jeda * 1000);
}

// ── Tambah / hapus kartu ──────────────────────────────
function updateCount() {
    liveCount.textContent = state.cards.length + ' foto';
    emptyState.style.display = state.cards.length === 0 ? 'flex' : 'none';
}

function createCard(foto, anim, delay) {
    const el = document.createElement('div');
    el.className = 'foto-card';
    el.style.animation = `${anim} 0.7s cubic-bezier(0.34,1.56,0.64,1) ${delay}s forwards`;
    el.addEventListener('animationend', () => {
        el.style.opacity = '1';
        el.style.animation = 'none';
    }, { once: true });

    const inner = document.createElement('div');
    inner.className = 'card-inner';
    inner.appendChild(buildCardBody(foto, false));
    el.appendChild(inner);

    el.addEventListener('click', () => openLightbox(foto));
    wall.appendChild(el);

    const c = { foto, el, inner };
    state.cards.push(c);
    if (el.offsetHeight > 0) cardH = el.offsetHeight;
    return c;
}

function dropCard(c) {
    c.el.style.animation  = 'none';
    c.el.style.opacity    = '1';
    void c.el.offsetWidth;
    c.el.style.transition = 'opacity 0.45s ease';
    c.el.style.opacity    = '0';
    c.inner.style.transition = 'transform 0.45s ease';
    c.inner.style.setProperty('--s', 0.3);
    setTimeout(() => c.el.remove(), 480);
}

function insertCard(foto) {
    while (state.cards.length >= settings.max_cards) dropCard(state.cards.shift());

    const c = createCard(foto, 'joinWall', 0);
    if (isGrid()) layoutGrid(0.8);
    else placeRandom(c, 0);
    updateCount();
}

function removeCard(fotoId) {
    state.seen.delete(Number(fotoId));
    state.spotQueue = state.spotQueue.filter(f => f.id != fotoId);
    const idx = state.cards.findIndex(c => c.foto.id == fotoId);
    if (idx === -1) return;
    dropCard(state.cards.splice(idx, 1)[0]);
    if (isGrid()) layoutGrid(0.8);
    updateCount();
}

// ── Spotlight: foto baru tampil di tengah, lalu bergabung ke wall ──
function addFoto(foto) {
    const sudahAda = state.cards.some(c => c.foto.id == foto.id)
        || state.spotQueue.some(f => f.id == foto.id)
        || state.spotNow == foto.id;
    if (sudahAda) return;
    state.seen.add(Number(foto.id));
    state.addedAt.set(Number(foto.id), Date.now());
    state.spotQueue.push(foto);
    runSpotlight();
}

async function runSpotlight() {
    if (state.spotBusy) return;
    state.spotBusy = true;

    while (state.spotQueue.length) {
        const foto = state.spotQueue.shift();
        state.spotNow = foto.id;
        const tahan = Math.max(1, Number(settings.spotlight_duration) || 3) * 1000;

        const card = document.createElement('div');
        card.className = 'spot-card';
        card.appendChild(buildCardBody(foto, true));
        spotlight.appendChild(card);

        void card.offsetWidth;
        spotlight.classList.add('on');
        card.classList.add('show');           // fade in
        await wait(600 + tahan);              // tampil di tengah

        card.classList.add('hide');           // fade out
        spotlight.classList.remove('on');
        await wait(700);
        card.remove();

        insertCard(foto);                     // bergabung dengan foto lain
        state.spotNow = null;
        await wait(300);
    }

    state.spotBusy = false;
}

// ── Lightbox ──────────────────────────────────────────
function openLightbox(foto) {
    document.getElementById('lb-img').src = foto.url;
    document.getElementById('lb-caption').textContent = foto.caption || '';
    document.getElementById('lb-meta').textContent = (foto.uploader || 'Anonim') + ' · ' + (foto.waktu || '');
    lightbox.classList.add('open');
}
const closeLightbox = () => lightbox.classList.remove('open');
document.getElementById('lb-close').addEventListener('click', closeLightbox);
lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// ── Muat foto (hanya N terbaru; foto lama tetap ada di database) ──
async function loadFotos() {
    state.memuat = true;
    try {
        const res  = await fetch('/api/fotos');
        const data = await res.json();
        state.seen = new Set(data.map(f => Number(f.id)));

        state.cards.forEach(c => c.el.remove());
        state.cards = [];

        const anims = ['cardEnterBottom', 'cardEnterLeft', 'cardEnterRight', 'cardEnterTop'];
        data.slice(0, settings.max_cards).reverse().forEach(foto => {
            const c = createCard(foto, isGrid() ? 'cardFade' : anims[randInt(0, 3)], rand(0, 0.6));
            if (!isGrid()) placeRandom(c, 0);
        });
        if (isGrid()) layoutGrid(0);
        updateCount();
    } catch (e) {
        console.error('Gagal load fotos:', e);
    } finally {
        state.memuat = false;
    }
}

// ── Cek berkala ke server ─────────────────────────────
// Wall tetap menerima foto baru, foto yang dihapus, dan perubahan pengaturan
// walaupun websocket (Reverb/Pusher) atau queue worker sedang tidak jalan.
async function sinkron() {
    if (state.memuat) return;
    try {
        const [resFoto, resAtur] = await Promise.all([fetch('/api/fotos'), fetch('/api/pengaturan')]);
        if (!resFoto.ok || !resAtur.ok) return;
        const data = await resFoto.json();
        const atur = await resAtur.json();
        if (state.memuat) return;

        const ids  = new Set(data.map(f => Number(f.id)));
        const baruSaja = (id) => Date.now() - (state.addedAt.get(id) || 0) < 15000;

        // foto yang dihapus / disembunyikan
        state.cards.map(c => Number(c.foto.id))
            .filter(id => !ids.has(id) && !baruSaja(id))
            .forEach(removeCard);
        [...state.seen].forEach(id => { if (!ids.has(id) && !baruSaja(id)) state.seen.delete(id); });

        // foto baru (yang paling lama masuk tampil lebih dulu)
        data.filter(f => !state.seen.has(Number(f.id))).reverse().forEach(addFoto);

        if (JSON.stringify(atur) !== JSON.stringify(settings)) applySettings(atur);
    } catch (e) {
        // server sedang tidak terjangkau: coba lagi di putaran berikutnya
    }
}

// ── Pengaturan berubah dari dashboard ─────────────────
function applySettings(baru) {
    const lama = settings;
    settings = Object.assign({}, settings, baru);

    applyBackground();
    applyFrameMode();
    if (Number(lama.max_cards) !== Number(settings.max_cards)) {
        loadFotos();
    } else {
        relayout(1.2);
    }
    startMotion();
}

let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => relayout(0.4), 200);
});

// ── Mulai ─────────────────────────────────────────────
applyBackground();
applyFrameMode();

window.addEventListener('load', async () => {
    await loadFotos();
    startMotion();
    setInterval(sinkron, 5000);

    if (!window.EchoInstance) {
        console.warn('EchoInstance tidak ditemukan. Wall tetap diperbarui lewat cek berkala tiap 5 detik.');
        return;
    }

    window.EchoInstance.channel('gallery')
        .listen('.foto.baru',         (data) => addFoto(data))
        .listen('.foto.dihapus',      (data) => removeCard(data.id))
        .listen('.pengaturan.diubah', (data) => applySettings(data));
});
</script>
@endpush
