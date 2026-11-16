<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';
    protected $primaryKey = 'kunci';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['kunci', 'nilai'];

    /** Nilai bawaan, dipakai selama belum diubah dari dashboard */
    public const BAWAAN = [
        'wall_mode'          => 'acak',      // acak | grid
        'move_interval'      => 3,           // detik, jeda gerak kartu (mode acak)
        'spotlight_duration' => 3,           // detik, lama foto baru tampil di tengah
        'max_cards'          => 20,          // jumlah foto terbaru di wall
        'bg_type'            => 'gradient',  // gradient | color | image
        'bg_color'           => '#1a0533',
        'bg_image'           => '',
        'frame'              => 'aktif',     // aktif | mati (frame photobooth pada foto baru)
        'moderasi'           => 'mati',      // mati | aktif (foto harus disetujui dulu)
    ];

    public static function semua(): array
    {
        $tersimpan = static::pluck('nilai', 'kunci')->all();
        $hasil = array_merge(self::BAWAAN, array_intersect_key($tersimpan, self::BAWAAN));

        foreach (['move_interval', 'spotlight_duration', 'max_cards'] as $kunci) {
            $hasil[$kunci] = (int) $hasil[$kunci];
        }
        $hasil['bg_image'] = (string) $hasil['bg_image'];

        return $hasil;
    }

    public static function simpan(array $data): void
    {
        foreach (array_intersect_key($data, self::BAWAAN) as $kunci => $nilai) {
            static::updateOrCreate(['kunci' => $kunci], ['nilai' => (string) $nilai]);
        }
    }
}
