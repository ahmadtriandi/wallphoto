<?php

// Nilai dari .env untuk fitur wall. Kode lain membaca lewat config('wall.…'),
// karena env() bernilai kosong setelah "php artisan config:cache" di server.
return [
    'dashboard_user'        => env('DASHBOARD_USER', 'admin'),
    'dashboard_password'    => env('DASHBOARD_PASSWORD'),
    'cloudinary_cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
];
