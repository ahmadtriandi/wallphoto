<?php

namespace App\Http\Controllers;

use App\Events\FotoBaru;
use App\Events\FotoDihapus;
use App\Models\Foto;
use App\Models\Pengaturan;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ModerasiController extends Controller
{
    private const STATUS = ['menunggu', 'disetujui', 'ditolak'];

    public function index(): View
    {
        return view('moderasi.index', ['moderasiAktif' => Pengaturan::semua()['moderasi'] === 'aktif']);
    }

    /** Daftar foto per status + jumlah tiap status (dipanggil berkala oleh halaman moderasi) */
    public function data(Request $request): JsonResponse
    {
        $status = in_array($request->query('status'), self::STATUS, true) ? $request->query('status') : 'menunggu';

        $fotos = Foto::where('status', $status)->latest()->limit(100)->get()->map(fn (Foto $f) => [
            'id'            => $f->id,
            'caption'       => $f->caption,
            'uploader'      => $f->uploader ?: 'Anonim',
            'url'           => $f->url,
            'thumbnail_url' => $f->thumbnail_url ?: $f->url,
            'waktu'         => $f->created_at?->format('d/m H:i'),
        ]);

        $hitung = Foto::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $jumlah = [];
        foreach (self::STATUS as $s) {
            $jumlah[$s] = (int) ($hitung[$s] ?? 0);
        }

        return response()->json(['status' => $status, 'jumlah' => $jumlah, 'fotos' => $fotos]);
    }

    /** Setujui: foto tampil di wall */
    public function setujui(Foto $foto): JsonResponse
    {
        if ($foto->status !== 'disetujui') {
            $foto->forceFill(['status' => 'disetujui'])->save();
            $this->kirim(fn () => broadcast(new FotoBaru($foto)));
        }

        return response()->json(['success' => true]);
    }

    /** Tolak / sembunyikan: foto hilang dari wall, tetapi tetap tersimpan */
    public function tolak(Foto $foto): JsonResponse
    {
        if ($foto->status !== 'ditolak') {
            $foto->forceFill(['status' => 'ditolak'])->save();
            $this->kirim(fn () => broadcast(new FotoDihapus($foto->id)));
        }

        return response()->json(['success' => true]);
    }

    /** Hapus permanen dari database dan Cloudinary */
    public function hapus(Foto $foto): JsonResponse
    {
        try {
            if ($foto->public_id) {
                Cloudinary::destroy($foto->public_id);
            }
        } catch (\Throwable $e) {
            Log::warning('Hapus dari Cloudinary gagal: ' . $e->getMessage());
        }

        $foto->delete();   // FotoObserver mengirim event ke wall

        return response()->json(['success' => true]);
    }

    /** Status sudah tersimpan; kalau broadcast gagal jangan sampai aksi dianggap gagal */
    private function kirim(callable $aksi): void
    {
        try {
            $aksi();
        } catch (\Throwable $e) {
            Log::warning('Broadcast moderasi gagal: ' . $e->getMessage());
        }
    }
}
