<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Wall</title>
    <style>
        :root {
            --bg: #f6f4f9;
            --panel: #ffffff;
            --ink: #1d1326;
            --muted: #6b6076;
            --line: #e4dfeb;
            --accent: #7a1fb8;
            --accent-soft: #f3e8fb;
            --ok: #18794e;
            --err: #b42318;
        }
        * { box-sizing: border-box; margin: 0; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.5;
            padding: 24px 16px 96px;
        }
        .wrap { max-width: 760px; margin: 0 auto; }

        header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
        h1 { font-size: 22px; font-weight: 700; }
        header p { color: var(--muted); font-size: 14px; }
        .link-wall {
            font-size: 14px; font-weight: 600; color: var(--accent);
            text-decoration: none; border: 1px solid var(--line); background: var(--panel);
            padding: 8px 14px; border-radius: 10px; white-space: nowrap;
        }
        .link-wall:hover { border-color: var(--accent); }
        .catatan { font-size: 13px; color: var(--muted); margin-top: 10px; }
        .catatan.peringatan { color: #7a4d00; background: #fff6e0; border-radius: 8px; padding: 8px 12px; }
        .tautan { display: flex; gap: 8px; flex-wrap: wrap; }

        section { background: var(--panel); border: 1px solid var(--line); border-radius: 14px; padding: 20px; margin-bottom: 16px; }
        h2 { font-size: 16px; font-weight: 700; margin-bottom: 14px; }

        .pilihan { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
        .pilihan label {
            display: block; border: 1.5px solid var(--line); border-radius: 12px;
            padding: 12px 14px; cursor: pointer; position: relative;
        }
        .pilihan input { position: absolute; opacity: 0; }
        .pilihan label:has(input:checked) { border-color: var(--accent); background: var(--accent-soft); }
        .pilihan label:has(input:focus-visible) { outline: 2px solid var(--accent); outline-offset: 2px; }
        .pilihan strong { display: block; font-size: 14px; }
        .pilihan span { font-size: 13px; color: var(--muted); }

        .baris { display: grid; grid-template-columns: 1fr auto; gap: 4px 16px; align-items: center; padding: 12px 0; border-top: 1px solid var(--line); }
        .baris:first-of-type { border-top: 0; padding-top: 0; }
        .baris label { font-size: 14px; font-weight: 600; }
        .baris small { grid-column: 1; color: var(--muted); font-size: 13px; }
        .angka { display: flex; align-items: center; gap: 8px; grid-row: span 2; }
        .angka input {
            width: 84px; padding: 8px 10px; font: inherit; text-align: right;
            border: 1.5px solid var(--line); border-radius: 10px; font-variant-numeric: tabular-nums;
        }
        .angka input:focus, .file input:focus-visible { outline: 2px solid var(--accent); outline-offset: 1px; }
        .angka span { font-size: 13px; color: var(--muted); min-width: 34px; }

        .bg-opsi { margin-top: 14px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; font-size: 14px; }
        .bg-opsi input[type="color"] { width: 52px; height: 36px; border: 1.5px solid var(--line); border-radius: 8px; padding: 2px; background: #fff; cursor: pointer; }
        .file input { font: inherit; font-size: 13px; max-width: 100%; }
        .bg-opsi small { color: var(--muted); font-size: 13px; flex-basis: 100%; }

        /* Pratinjau wall */
        .pratinjau {
            margin-top: 16px; position: relative; aspect-ratio: 16 / 9; border-radius: 12px;
            overflow: hidden; border: 1px solid var(--line);
            background-size: cover; background-position: center;
        }
        .pratinjau.gradient {
            background:
                radial-gradient(ellipse at 15% 60%, rgba(140,20,180,0.7) 0%, transparent 45%),
                radial-gradient(ellipse at 70% 10%, rgba(200,20,80,0.6) 0%, transparent 40%),
                linear-gradient(135deg, #1a0030 0%, #2d0060 40%, #1a0040 70%, #2a0010 100%);
        }
        .pratinjau i {
            position: absolute; width: 13%; aspect-ratio: 9 / 10; border-radius: 6px;
            background: linear-gradient(#e9e2f1 68%, #6b0fa8 68%); border: 1px solid rgba(255,61,127,0.7);
            transition: all 0.5s ease;
        }
        .pratinjau .qr { position: absolute; right: 4%; top: 50%; translate: 0 -50%; width: 12%; aspect-ratio: 1; background: #fff; border-radius: 6px; }
        .pratinjau em { position: absolute; left: 10px; bottom: 8px; font-style: normal; font-size: 12px; color: #fff; background: rgba(0,0,0,0.55); padding: 2px 8px; border-radius: 6px; }

        .aksi {
            position: fixed; left: 0; right: 0; bottom: 0; background: var(--panel);
            border-top: 1px solid var(--line); padding: 12px 16px;
        }
        .aksi div { max-width: 760px; margin: 0 auto; display: flex; align-items: center; gap: 14px; }
        button {
            font: inherit; font-weight: 700; font-size: 15px; color: #fff; background: var(--accent);
            border: 0; border-radius: 10px; padding: 10px 22px; cursor: pointer;
        }
        button:hover { background: #65149c; }
        button:disabled { opacity: 0.6; cursor: wait; }
        button:focus-visible { outline: 2px solid var(--ink); outline-offset: 2px; }
        #status { font-size: 14px; }
        #status.ok { color: var(--ok); }
        #status.err { color: var(--err); }
        [hidden] { display: none !important; }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div>
            <h1>Dashboard Wall</h1>
            <p>Perubahan langsung diterapkan ke wall yang sedang terbuka.</p>
        </div>
        <div class="tautan">
            <a class="link-wall" href="/moderasi">Moderasi foto</a>
            <a class="link-wall" href="/gallery" target="_blank" rel="noopener">Buka wall ↗</a>
        </div>
    </header>

    <form id="form">
        <section>
            <h2>Tampilan foto</h2>
            <div class="pilihan">
                <label>
                    <input type="radio" name="wall_mode" value="acak" @checked($p['wall_mode'] === 'acak')>
                    <strong>Gerak acak</strong>
                    <span>Foto bertumpuk dan berpindah posisi terus-menerus.</span>
                </label>
                <label>
                    <input type="radio" name="wall_mode" value="grid" @checked($p['wall_mode'] === 'grid')>
                    <strong>Grid diam</strong>
                    <span>Foto tersusun rapi dan tidak bergerak.</span>
                </label>
            </div>
        </section>

        <section>
            <h2>Animasi dan jumlah foto</h2>

            <div class="baris">
                <label for="spotlight_duration">Durasi foto baru di tengah layar</label>
                <div class="angka">
                    <input type="number" id="spotlight_duration" name="spotlight_duration" min="1" max="30" step="1" value="{{ $p['spotlight_duration'] }}" required>
                    <span>detik</span>
                </div>
                <small>Lama foto baru tampil besar sebelum bergabung ke wall (1–30).</small>
            </div>

            <div class="baris" id="baris-gerak">
                <label for="move_interval">Jeda gerak foto</label>
                <div class="angka">
                    <input type="number" id="move_interval" name="move_interval" min="2" max="60" step="1" value="{{ $p['move_interval'] }}" required>
                    <span>detik</span>
                </div>
                <small>Seberapa sering foto berpindah posisi. Hanya untuk mode gerak acak (2–60).</small>
            </div>

            <div class="baris">
                <label for="max_cards">Jumlah foto di wall</label>
                <div class="angka">
                    <input type="number" id="max_cards" name="max_cards" min="4" max="40" step="1" value="{{ $p['max_cards'] }}" required>
                    <span>foto</span>
                </div>
                <small>Hanya foto terbaru yang tampil. Foto lama tetap tersimpan di database (4–40).</small>
            </div>
        </section>

        <section>
            <h2>Frame foto</h2>
            <div class="pilihan">
                <label>
                    <input type="radio" name="frame" value="aktif" @checked($p['frame'] === 'aktif')>
                    <strong>Pakai frame</strong>
                    <span>Setiap foto baru ditempel frame photobooth (1200 × 1800 px).</span>
                </label>
                <label>
                    <input type="radio" name="frame" value="mati" @checked($p['frame'] === 'mati')>
                    <strong>Tanpa frame</strong>
                    <span>Foto tampil apa adanya.</span>
                </label>
            </div>
            <p class="catatan">Berlaku untuk foto yang diupload setelah disimpan. Foto lama tidak berubah.</p>
            @unless ($frameAda)
                <p class="catatan peringatan">File frame belum ada di <code>public/frames/frame.png</code>, jadi foto diupload tanpa frame walaupun pilihan ini aktif.</p>
            @endunless
        </section>

        <section>
            <h2>Moderasi</h2>
            <div class="pilihan">
                <label>
                    <input type="radio" name="moderasi" value="mati" @checked($p['moderasi'] === 'mati')>
                    <strong>Langsung tampil</strong>
                    <span>Foto baru langsung muncul di wall.</span>
                </label>
                <label>
                    <input type="radio" name="moderasi" value="aktif" @checked($p['moderasi'] === 'aktif')>
                    <strong>Perlu disetujui</strong>
                    <span>Foto baru ditahan sampai disetujui di halaman moderasi.</span>
                </label>
            </div>
        </section>

        <section>
            <h2>Background wall</h2>
            <div class="pilihan">
                <label>
                    <input type="radio" name="bg_type" value="gradient" @checked($p['bg_type'] === 'gradient')>
                    <strong>Gradient bawaan</strong>
                    <span>Ungu dan pink.</span>
                </label>
                <label>
                    <input type="radio" name="bg_type" value="color" @checked($p['bg_type'] === 'color')>
                    <strong>Warna polos</strong>
                    <span>Satu warna pilihanmu.</span>
                </label>
                <label>
                    <input type="radio" name="bg_type" value="image" @checked($p['bg_type'] === 'image')>
                    <strong>Gambar</strong>
                    <span>Upload gambar sendiri.</span>
                </label>
            </div>

            <div class="bg-opsi" id="opsi-warna">
                <label for="bg_color">Warna background</label>
                <input type="color" id="bg_color" name="bg_color" value="{{ $p['bg_color'] }}">
            </div>

            <div class="bg-opsi file" id="opsi-gambar">
                <label for="bg_image_file">Gambar background</label>
                <input type="file" id="bg_image_file" name="bg_image_file" accept="image/jpeg,image/png,image/webp">
                <small>JPG, PNG, atau WebP, maksimal 10 MB. Ukuran yang disarankan 1920 × 1080 px.
                    <span id="info-gambar" @if (! $p['bg_image']) hidden @endif>Kosongkan untuk tetap memakai gambar yang sekarang.</span>
                </small>
            </div>

            <div class="pratinjau" id="pratinjau">
                <i></i><i></i><i></i><i></i><i></i><i></i>
                <div class="qr"></div>
                <em>Pratinjau</em>
            </div>
        </section>

        <div class="aksi">
            <div>
                <button type="submit" id="tombol">Simpan pengaturan</button>
                <span id="status" role="status"></span>
            </div>
        </div>
    </form>
</div>

<script>
const form      = document.getElementById('form');
const tombol    = document.getElementById('tombol');
const status    = document.getElementById('status');
const pratinjau = document.getElementById('pratinjau');
const fileInput = document.getElementById('bg_image_file');
let gambarTersimpan = @json($p['bg_image']);
let urlPratinjauFile = null;

const nilai = (name) => form.querySelector(`[name="${name}"]:checked`).value;

// Posisi kartu mini di pratinjau: [kiri %, atas %, rotasi]
const POS = {
    acak: [[6,14,-7],[24,40,5],[14,52,8],[42,10,-4],[52,44,6],[36,30,-8]],
    grid: [[8,12,0],[30,12,0],[52,12,0],[8,54,0],[30,54,0],[52,54,0]],
};

function segarkan() {
    const mode = nilai('wall_mode');
    const bg   = nilai('bg_type');

    document.getElementById('baris-gerak').hidden = mode !== 'acak';
    document.getElementById('opsi-warna').hidden  = bg !== 'color';
    document.getElementById('opsi-gambar').hidden = bg !== 'image';

    pratinjau.classList.toggle('gradient', bg === 'gradient');
    pratinjau.style.backgroundColor = '';
    pratinjau.style.backgroundImage = '';
    if (bg === 'color') {
        pratinjau.style.backgroundColor = document.getElementById('bg_color').value;
    } else if (bg === 'image') {
        const url = urlPratinjauFile || gambarTersimpan;
        pratinjau.style.backgroundColor = '#2b2533';
        if (url) pratinjau.style.backgroundImage = 'url(' + JSON.stringify(url) + ')';
    }

    pratinjau.querySelectorAll('i').forEach((el, i) => {
        const [x, y, r] = POS[mode][i];
        el.style.left = x + '%';
        el.style.top  = y + '%';
        el.style.rotate = r + 'deg';
    });
}

fileInput.addEventListener('change', () => {
    if (urlPratinjauFile) URL.revokeObjectURL(urlPratinjauFile);
    urlPratinjauFile = fileInput.files[0] ? URL.createObjectURL(fileInput.files[0]) : null;
    segarkan();
});
form.addEventListener('input', segarkan);
segarkan();

function tampilStatus(teks, jenis) {
    status.textContent = teks;
    status.className = jenis || '';
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (nilai('bg_type') === 'image' && !fileInput.files[0] && !gambarTersimpan) {
        tampilStatus('Pilih file gambar untuk background terlebih dahulu.', 'err');
        return;
    }

    tombol.disabled = true;
    tampilStatus('Menyimpan…');

    try {
        const res = await fetch('/dashboard', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: new FormData(form),
        });
        const json = await res.json().catch(() => ({}));

        if (res.ok && json.success) {
            gambarTersimpan = json.pengaturan.bg_image;
            document.getElementById('info-gambar').hidden = !gambarTersimpan;
            fileInput.value = '';
            if (urlPratinjauFile) { URL.revokeObjectURL(urlPratinjauFile); urlPratinjauFile = null; }
            segarkan();
            tampilStatus('Tersimpan. Wall sudah diperbarui.', 'ok');
        } else if (res.status === 422 && json.errors) {
            tampilStatus(Object.values(json.errors).flat().join(' '), 'err');
        } else if (res.status === 419) {
            tampilStatus('Sesi habis. Muat ulang halaman ini lalu simpan lagi.', 'err');
        } else {
            tampilStatus(json.message || 'Gagal menyimpan (kode ' + res.status + ').', 'err');
        }
    } catch (err) {
        tampilStatus('Tidak bisa terhubung ke server. Periksa koneksi lalu coba lagi.', 'err');
    } finally {
        tombol.disabled = false;
    }
});
</script>
</body>
</html>
