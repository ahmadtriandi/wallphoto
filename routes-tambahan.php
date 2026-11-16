<?php
// ─────────────────────────────────────────────────────────────
// TAMBAHKAN ke routes/web.php (jangan timpa route yang sudah ada)
// ─────────────────────────────────────────────────────────────

use App\Http\Controllers\ModerasiController;
use App\Http\Controllers\PengaturanController;
use App\Http\Middleware\DashboardAuth;

// Dibaca halaman wall
Route::get('/api/pengaturan', [PengaturanController::class, 'api']);

// Dashboard + moderasi (dilindungi user + password dari .env)
Route::middleware(DashboardAuth::class)->group(function () {
    Route::get('/dashboard', [PengaturanController::class, 'index']);
    Route::post('/dashboard', [PengaturanController::class, 'update']);

    Route::get('/moderasi', [ModerasiController::class, 'index']);
    Route::get('/moderasi/data', [ModerasiController::class, 'data']);
    Route::post('/moderasi/{foto}/setujui', [ModerasiController::class, 'setujui']);
    Route::post('/moderasi/{foto}/tolak', [ModerasiController::class, 'tolak']);
    Route::delete('/moderasi/{foto}', [ModerasiController::class, 'hapus']);
});
