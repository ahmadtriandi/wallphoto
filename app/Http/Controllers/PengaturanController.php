<?php

namespace App\Http\Controllers;

use App\Events\PengaturanDiubah;
use App\Models\Pengaturan;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    /** Halaman dashboard */
    public function index(): View
    {
        return view('dashboard.index', [
            'p'        => Pengaturan::semua(),
            'frameAda' => is_file(FotoController::pathFrame()),
        ]);
    }

    /** Dibaca halaman wall saat pertama dibuka */
    public function api(): JsonResponse
    {
        return response()->json(Pengaturan::semua());
    }

    /** Simpan pengaturan dari dashboard */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'wall_mode'          => 'required|in:acak,grid',
            'move_interval'      => 'required|integer|min:2|max:60',
            'spotlight_duration' => 'required|integer|min:1|max:30',
            'max_cards'          => 'required|integer|min:4|max:40',
            'bg_type'            => 'required|in:gradient,color,image',
            'bg_color'           => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'moderasi'           => 'required|in:mati,aktif',
            'frame'              => 'required|in:aktif,mati',
            'bg_image_file'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);
        unset($data['bg_image_file']);

        try {
            if ($request->hasFile('bg_image_file')) {
                $uploaded = Cloudinary::upload($request->file('bg_image_file')->getRealPath(), [
                    'folder'         => 'gallery-wall',
                    'transformation' => [
                        'width'   => 1920,
                        'height'  => 1080,
                        'crop'    => 'limit',
                        'quality' => 'auto',
                    ],
                ]);
                $data['bg_image'] = $uploaded->getSecurePath();
            }
        } catch (\Throwable $e) {
            Log::error('Upload background gagal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Upload gambar background gagal: ' . $e->getMessage(),
            ], 500);
        }

        $sudahAdaGambar = ! empty($data['bg_image']) || ! empty(Pengaturan::semua()['bg_image']);
        if ($data['bg_type'] === 'image' && ! $sudahAdaGambar) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih file gambar untuk background terlebih dahulu.',
            ], 422);
        }

        Pengaturan::simpan($data);
        $semua = Pengaturan::semua();

        // Kirim ke wall yang sedang terbuka. Kalau websocket mati, pengaturan tetap tersimpan.
        try {
            broadcast(new PengaturanDiubah($semua));
        } catch (\Throwable $e) {
            Log::warning('Broadcast pengaturan gagal: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'pengaturan' => $semua]);
    }
}
