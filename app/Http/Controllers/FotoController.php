<?php

namespace App\Http\Controllers;

use App\Events\FotoBaru;
use App\Models\Foto;
use App\Models\Pengaturan;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FotoController extends Controller
{
    /** Lokasi frame photobooth (PNG transparan 1200 x 1800 px) */
    public static function pathFrame(): string
    {
        return public_path('frames/frame.png');
    }

    /** Halaman wall (dibuka di laptop) */
    public function gallery(): View
    {
        return view('gallery.index');
    }

    /** Halaman upload (dibuka di HP) */
    public function uploadPage(): View
    {
        return view('upload.index');
    }

    /** Proses upload foto dari HP */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'foto'     => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'caption'  => 'required|string|max:255',
            'uploader' => 'nullable|string|max:100',
        ]);

        $tempPath = null;

        try {
            $file   = $request->file('foto');
            $sumber = $file->getRealPath();

            // Frame dipakai hanya kalau diaktifkan di dashboard DAN file frame-nya ada
            $pakaiFrame = Pengaturan::semua()['frame'] === 'aktif' && is_file(self::pathFrame());

            if ($pakaiFrame) {
                $tempPath = $this->applyFrame($sumber);
                $sumber   = $tempPath;
                $opsi     = ['folder' => 'gallery-foto'];
                $ukuranThumb = 'w_400,h_600,c_fill';
            } else {
                $opsi = [
                    'folder'         => 'gallery-foto',
                    'transformation' => [
                        'width'   => 1200,
                        'height'  => 1200,
                        'crop'    => 'limit',
                        'quality' => 'auto',
                    ],
                ];
                $ukuranThumb = 'w_400,h_400,c_fill';
            }

            $uploaded  = Cloudinary::upload($sumber, $opsi);
            $publicId  = $uploaded->getPublicId();
            $cloudName = config('wall.cloudinary_cloud_name');

            $foto = Foto::create([
                'caption'       => $request->caption,
                'url'           => $uploaded->getSecurePath(),
                'thumbnail_url' => "https://res.cloudinary.com/{$cloudName}/image/upload/{$ukuranThumb},q_auto,f_auto/{$publicId}",
                'public_id'     => $publicId,
                'uploader'      => $request->uploader ?: 'Anonim',
                'file_size'     => $file->getSize(),
            ]);

            // Kalau moderasi aktif, event ini otomatis tidak dikirim (lihat FotoBaru::broadcastWhen)
            broadcast(new FotoBaru($foto));

            $menunggu = $foto->status === 'menunggu';

            return response()->json([
                'success'  => true,
                'menunggu' => $menunggu,
                'message'  => $menunggu
                    ? 'Foto terkirim dan akan tampil setelah disetujui.'
                    : 'Foto berhasil diupload!',
                'foto'     => $foto->toBroadcastArray(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Upload error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());

            return response()->json([
                'success' => false,
                'message' => 'Upload gagal: ' . $e->getMessage(),
            ], 500);
        } finally {
            if ($tempPath && is_file($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /** Tempel frame photobooth di atas foto, hasil 1200 x 1800 px */
    private function applyFrame(string $fotoPath): string
    {
        $manager = new ImageManager(new Driver());

        $foto = $manager->read($fotoPath);
        $foto->cover(1200, 1800);

        $frame = $manager->read(self::pathFrame());
        $frame->resize(1200, 1800);

        $foto->place($frame, 'top-left', 0, 0);

        $tempPath = storage_path('app/temp_' . uniqid() . '.jpg');
        $foto->toJpeg(90)->save($tempPath);

        return $tempPath;
    }

    /** Daftar foto yang boleh tampil di wall */
    public function apiIndex(): JsonResponse
    {
        $fotos = Foto::where('status', 'disetujui')->latest()->get()->map->toBroadcastArray();

        return response()->json($fotos);
    }
}
