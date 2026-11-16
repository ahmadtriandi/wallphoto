DEPLOY KE VPS (Ubuntu 24.04)
Alur: laptop → GitHub (repo private) → VPS

══════════════════════════════════════════════════════════════════
A. DI LAPTOP (sekali)
══════════════════════════════════════════════════════════════════

1. Salin file-file baru dari paket ini ke project:
     config/wall.php                                   (baru)
     app/Http/Middleware/DashboardAuth.php             (berubah)
     app/Http/Controllers/FotoController.php           (berubah)
     resources/views/layouts/partials/echo.blade.php   (baru)
     deploy/                                           (folder baru, seluruhnya)

2. resources/views/layouts/app.blade.php:
   Hapus tiga blok ini:
     - <script src="...pusher.min.js"></script>
     - <script src="...echo.iife.js"></script>
     - <script> window.EchoInstance = new Echo({...}) </script>
   Ganti dengan satu baris (tetap SEBELUM script Alpine.js):
     @include('layouts.partials.echo')

   Alamat Reverb sekarang dibaca dari .env, jadi file yang sama
   jalan di laptop maupun di VPS.

3. Pastikan file frame ada di public/frames/frame.png (ikut ter-push).

4. Coba dulu di laptop (php artisan serve, reverb:start, queue:work).

5. Push ke GitHub. Buat repo PRIVATE kosong di github.com, lalu di folder project:
     git init
     git add .
     git status          ← pastikan .env TIDAK ada di daftar
     git commit -m "Live photo wall"
     git branch -M main
     git remote add origin git@github.com:USERNAME/gallery-app.git
     git push -u origin main

══════════════════════════════════════════════════════════════════
B. DI VPS (sekali)
══════════════════════════════════════════════════════════════════

1. Di pengelola domain, buat record A:  wall.domainkamu.com → IP VPS

2. Masuk ke VPS:
     ssh root@IP_VPS

3. Beri VPS akses ke repo private (deploy key):
     apt update && apt install -y git
     ssh-keygen -t ed25519 -N "" -f ~/.ssh/id_ed25519
     cat ~/.ssh/id_ed25519.pub
   Salin teks yang muncul ke GitHub: repo → Settings → Deploy keys → Add deploy key.

4. Ambil kode:
     git clone git@github.com:USERNAME/gallery-app.git /var/www/gallery-app

5. Isi DOMAIN dan EMAIL di bagian atas skrip, lalu jalankan:
     nano /var/www/gallery-app/deploy/setup-server.sh
     bash /var/www/gallery-app/deploy/setup-server.sh
   Skrip memasang PHP, MySQL, Nginx, Supervisor, SSL, membuat database,
   dan membuat .env. Di akhir, skrip menampilkan password dashboard.

6. Isi data Cloudinary (sama dengan .env di laptop), lalu terapkan:
     nano /var/www/gallery-app/.env
     bash /var/www/gallery-app/deploy/deploy.sh

7. Buka:
     https://wall.domainkamu.com/gallery
     https://wall.domainkamu.com/dashboard
     https://wall.domainkamu.com/moderasi

══════════════════════════════════════════════════════════════════
C. UPDATE BERIKUTNYA
══════════════════════════════════════════════════════════════════

Di laptop:
     git add .
     git commit -m "keterangan perubahan"
     git push

Di VPS:
     bash /var/www/gallery-app/deploy/deploy.sh

══════════════════════════════════════════════════════════════════
D. KALAU ADA MASALAH
══════════════════════════════════════════════════════════════════

Status queue worker dan Reverb (harus RUNNING):
     supervisorctl status

Log:
     tail -n 50 /var/www/gallery-app/storage/logs/laravel-*.log
     tail -n 50 /var/www/gallery-app/storage/logs/reverb.log
     tail -n 50 /var/log/nginx/error.log

Setelah mengubah .env, selalu jalankan:
     bash /var/www/gallery-app/deploy/deploy.sh

"migrate" gagal karena tabel jobs sudah ada:
     Kamu pernah menjalankan "php artisan queue:table" di Laravel 11, padahal
     tabel jobs sudah ada di migrasi bawaan. Hapus file migrasi
     ..._create_jobs_table.php yang dibuat queue:table, push, lalu deploy lagi.

Foto lama di laptop:
     Database VPS mulai kosong. File fotonya sudah di Cloudinary, tetapi
     datanya di tabel fotos perlu dipindahkan: export tabel fotos dari MySQL
     laptop (phpMyAdmin → Export), lalu import di VPS.
