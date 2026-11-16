<?php

namespace App\Observers;

use App\Events\FotoDihapus;
use App\Models\Foto;
use App\Models\Pengaturan;

class FotoObserver
{
    /** Foto baru: kalau moderasi aktif, tahan dulu sampai disetujui */
    public function creating(Foto $foto): void
    {
        if (! $foto->status) {
            $foto->status = Pengaturan::semua()['moderasi'] === 'aktif' ? 'menunggu' : 'disetujui';
        }
    }

    /** Foto dihapus dari mana pun: hilangkan dari wall */
    public function deleted(Foto $foto): void
    {
        broadcast(new FotoDihapus($foto->id));
    }
}
