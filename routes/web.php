<?php
use App\Http\Controllers\ModerasiController;
use App\Http\Controllers\FotoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengaturanController;
use App\Http\Middleware\DashboardAuth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root ke gallery
Route::get('/', fn() => redirect('/gallery'));

// Halaman gallery — buka di laptop
Route::get('/gallery', [FotoController::class, 'gallery'])->name('gallery');

// Halaman upload — buka di HP
Route::get('/upload', [FotoController::class, 'uploadPage'])->name('upload');

// API upload foto (POST dari form HP)
Route::post('/upload', [FotoController::class, 'upload'])->name('foto.upload');

// API lainnya
Route::prefix('api')->group(function () {
    Route::get('/fotos', [FotoController::class, 'apiIndex'])->name('api.fotos');
    Route::delete('/fotos/{foto}', [FotoController::class, 'delete'])->name('api.fotos.delete');
});
// Dibaca halaman wall
Route::get('/api/pengaturan', [PengaturanController::class, 'api']);

// Dashboard pengaturan (dilindungi user + password dari .env)
Route::middleware(DashboardAuth::class)->group(function () {
    Route::get('/dashboard', [PengaturanController::class, 'index']);
    Route::post('/dashboard', [PengaturanController::class, 'update']);

    Route::get('/moderasi', [ModerasiController::class, 'index']);
    Route::get('/moderasi/data', [ModerasiController::class, 'data']);
    Route::post('/moderasi/{foto}/setujui', [ModerasiController::class, 'setujui']);
    Route::post('/moderasi/{foto}/tolak', [ModerasiController::class, 'tolak']);
    Route::delete('/moderasi/{foto}', [ModerasiController::class, 'hapus']);
});

