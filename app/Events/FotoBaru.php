<?php

namespace App\Events;

use App\Models\Foto;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FotoBaru implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Foto $foto) {}

    public function broadcastOn(): Channel
    {
        return new Channel('gallery');
    }

    public function broadcastAs(): string
    {
        return 'foto.baru';
    }

    /** Foto yang belum disetujui tidak dikirim ke wall */
    public function broadcastWhen(): bool
    {
        return ($this->foto->status ?? 'disetujui') === 'disetujui';
    }

    public function broadcastWith(): array
    {
        return $this->foto->toBroadcastArray();
    }
}
