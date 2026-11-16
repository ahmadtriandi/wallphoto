<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Foto extends Model
{
    use HasFactory;

    protected $fillable = [
        'caption',
        'url',
        'thumbnail_url',
        'public_id',
        'uploader',
        'file_size',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Format waktu upload untuk ditampilkan
     */
    public function getWaktuAttribute(): string
    {
        return $this->created_at->format('H:i');
    }

    /**
     * Format tanggal lengkap
     */
    public function getTanggalAttribute(): string
    {
        return $this->created_at->translatedFormat('d F Y');
    }

    /**
     * Format ukuran file
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '';
        $kb = $this->file_size / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';
        return round($kb / 1024, 1) . ' MB';
    }

    /**
     * Array untuk broadcast event
     */
    public function toBroadcastArray(): array
    {
        return [
            'id'            => $this->id,
            'caption'       => $this->caption,
            'url'           => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'uploader'      => $this->uploader ?? 'Anonim',
            'waktu'         => $this->waktu,
            'tanggal'       => $this->tanggal,
        ];
    }
}
