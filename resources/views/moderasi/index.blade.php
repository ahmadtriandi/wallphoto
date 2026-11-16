<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Moderasi Foto</title>
    <style>
        :root {
            --bg: #f6f4f9; --panel: #ffffff; --ink: #1d1326; --muted: #6b6076; --line: #e4dfeb;
            --accent: #7a1fb8; --accent-soft: #f3e8fb; --ok: #18794e; --err: #b42318; --warn-bg: #fff6e0; --warn: #7a4d00;
        }
        * { box-sizing: border-box; margin: 0; }
        body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: var(--bg); color: var(--ink); line-height: 1.5; padding: 24px 16px 48px; }
        .wrap { max-width: 1100px; margin: 0 auto; }

        header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        h1 { font-size: 22px; font-weight: 700; }
        header p { color: var(--muted); font-size: 14px; }
        .tautan { display: flex; gap: 8px; flex-wrap: wrap; }
        .tautan a { font-size: 14px; font-weight: 600; color: var(--accent); text-decoration: none; border: 1px solid var(--line); background: var(--panel); padding: 8px 14px; border-radius: 10px; white-space: nowrap; }
        .tautan a:hover { border-color: var(--accent); }

        .info { background: var(--warn-bg); color: var(--warn); border-radius: 10px; padding: 10px 14px; font-size: 14px; margin-bottom: 16px; }
        .info a { color: inherit; font-weight: 600; }

        .tab { display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap; }
        .tab button { font: inherit; font-size: 14px; font-weight: 600; color: var(--muted); background: transparent; border: 1.5px solid transparent; border-radius: 999px; padding: 7px 14px; cursor: pointer; }
        .tab button:hover { color: var(--ink); }
        .tab button[aria-selected="true"] { color: var(--accent); background: var(--accent-soft); border-color: var(--accent); }
        .tab b { font-variant-numeric: tabular-nums; margin-left: 4px; }
        button:focus-visible, a:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

        #daftar { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
        .kartu { background: var(--panel); border: 1px solid var(--line); border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; }
        .kartu.sibuk { opacity: 0.5; pointer-events: none; }
        .kartu a.gambar { display: block; background: #1d1326; }
        .kartu img { width: 100%; aspect-ratio: 4 / 5; object-fit: contain; display: block; }
        .isi { padding: 10px 12px 4px; flex: 1; }
        .isi p { font-size: 14px; font-weight: 600; overflow-wrap: anywhere; }
        .isi small { color: var(--muted); font-size: 12px; }
        .aksi { display: flex; gap: 6px; padding: 10px 12px 12px; flex-wrap: wrap; }
        .aksi button { font: inherit; font-size: 13px; font-weight: 700; border-radius: 8px; padding: 7px 10px; cursor: pointer; border: 1.5px solid var(--line); background: var(--panel); color: var(--ink); flex: 1; white-space: nowrap; }
        .aksi .setuju { background: var(--ok); border-color: var(--ok); color: #fff; }
        .aksi .setuju:hover { background: #126240; }
        .aksi .tolak:hover { border-color: var(--ink); }
        .aksi .hapus { color: var(--err); flex: 0 0 auto; }
        .aksi .hapus:hover { border-color: var(--err); }

        #kosong { text-align: center; color: var(--muted); padding: 56px 16px; font-size: 15px; }
        #galat { color: var(--err); font-size: 14px; margin-bottom: 12px; }
        [hidden] { display: none !important; }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div>
            <h1>Moderasi Foto</h1>
            <p>Daftar diperbarui otomatis setiap beberapa detik.</p>
        </div>
        <div class="tautan">
            <a href="/dashboard">Pengaturan</a>
            <a href="/gallery" target="_blank" rel="noopener">Buka wall ↗</a>
        </div>
    </header>

    @unless ($moderasiAktif)
        <div class="info">
            Moderasi sedang mati, jadi foto baru langsung tampil di wall. Kamu tetap bisa menyembunyikan atau menghapus foto di sini.
            <a href="/dashboard">Aktifkan di pengaturan</a>
        </div>
    @endunless

    <div class="tab" role="tablist">
        <button role="tab" data-status="menunggu">Menunggu <b id="n-menunggu">0</b></button>
        <button role="tab" data-status="disetujui">Tampil di wall <b id="n-disetujui">0</b></button>
        <button role="tab" data-status="ditolak">Disembunyikan <b id="n-ditolak">0</b></button>
    </div>

    <div id="galat" role="alert" hidden></div>
    <div id="daftar"></div>
    <div id="kosong" hidden></div>
</div>

<script>
const TOKEN  = document.querySelector('meta[name="csrf-token"]').content;
const daftar = document.getElementById('daftar');
const kosong = document.getElementById('kosong');
const galat  = document.getElementById('galat');

const KOSONG = {
    menunggu:  'Tidak ada foto yang menunggu persetujuan.',
    disetujui: 'Belum ada foto yang tampil di wall.',
    ditolak:   'Tidak ada foto yang disembunyikan.',
};

let statusAktif = @json($moderasiAktif ? 'menunggu' : 'disetujui');
let sidikJari   = '';
let sedangAksi  = 0;

function tombol(teks, kelas, onClick) {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = kelas;
    b.textContent = teks;
    b.addEventListener('click', onClick);
    return b;
}

function buatKartu(f) {
    const kartu = document.createElement('div');
    kartu.className = 'kartu';

    const a = document.createElement('a');
    a.className = 'gambar';
    a.href = f.url;
    a.target = '_blank';
    a.rel = 'noopener';
    const img = document.createElement('img');
    img.src = f.thumbnail_url;
    img.alt = 'Foto dari ' + f.uploader;
    img.loading = 'lazy';
    a.appendChild(img);

    const isi = document.createElement('div');
    isi.className = 'isi';
    const p = document.createElement('p');
    p.textContent = f.caption;
    const s = document.createElement('small');
    s.textContent = f.uploader + ' · ' + (f.waktu || '');
    isi.append(p, s);

    const aksi = document.createElement('div');
    aksi.className = 'aksi';
    if (statusAktif !== 'disetujui') {
        aksi.appendChild(tombol(statusAktif === 'ditolak' ? 'Tampilkan' : 'Setujui', 'setuju', () => jalankan(kartu, 'POST', `/moderasi/${f.id}/setujui`)));
    }
    if (statusAktif !== 'ditolak') {
        aksi.appendChild(tombol(statusAktif === 'disetujui' ? 'Sembunyikan' : 'Tolak', 'tolak', () => jalankan(kartu, 'POST', `/moderasi/${f.id}/tolak`)));
    }
    aksi.appendChild(tombol('Hapus', 'hapus', () => {
        if (confirm('Hapus foto ini secara permanen? Foto juga dihapus dari Cloudinary dan tidak bisa dikembalikan.')) {
            jalankan(kartu, 'DELETE', `/moderasi/${f.id}`);
        }
    }));

    kartu.append(a, isi, aksi);
    return kartu;
}

async function jalankan(kartu, method, url) {
    kartu.classList.add('sibuk');
    sedangAksi++;
    try {
        const res = await fetch(url, { method, headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' } });
        if (res.status === 419) throw new Error('Sesi habis. Muat ulang halaman ini lalu coba lagi.');
        if (!res.ok) throw new Error('Aksi gagal (kode ' + res.status + '). Coba lagi.');
        tampilGalat('');
    } catch (e) {
        kartu.classList.remove('sibuk');
        tampilGalat(e instanceof TypeError ? 'Tidak bisa terhubung ke server. Periksa koneksi lalu coba lagi.' : e.message);
    } finally {
        sedangAksi--;
        muat(true);
    }
}

function tampilGalat(teks) {
    galat.textContent = teks;
    galat.hidden = !teks;
}

async function muat(paksa) {
    if (sedangAksi > 0 && !paksa) return;
    const diminta = statusAktif;
    try {
        const res = await fetch('/moderasi/data?status=' + diminta, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error();
        const data = await res.json();
        if (diminta !== statusAktif) return;   // tab sudah berpindah

        for (const s in data.jumlah) document.getElementById('n-' + s).textContent = data.jumlah[s];

        const sidik = diminta + '|' + data.fotos.map(f => f.id).join(',');
        if (sidik !== sidikJari || paksa) {
            sidikJari = sidik;
            daftar.replaceChildren(...data.fotos.map(buatKartu));
            kosong.textContent = KOSONG[diminta];
            kosong.hidden = data.fotos.length > 0;
        }
    } catch (e) {
        tampilGalat('Gagal memuat daftar foto. Mencoba lagi…');
    }
}

function pilihTab(status) {
    statusAktif = status;
    sidikJari = '';
    document.querySelectorAll('.tab button').forEach(b => b.setAttribute('aria-selected', b.dataset.status === status));
    daftar.replaceChildren();
    kosong.hidden = true;
    muat(true);
}

document.querySelectorAll('.tab button').forEach(b => b.addEventListener('click', () => pilihTab(b.dataset.status)));
pilihTab(statusAktif);
setInterval(() => muat(false), 5000);
</script>
</body>
</html>
