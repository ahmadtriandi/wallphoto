<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FotoDihapus implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $fotoId) {}

    public function broadcastOn(): Channel
    {
        return new Channel('gallery');
    }

    public function broadcastAs(): string
    {
        return 'foto.dihapus';
    }

    public function broadcastWith(): array
    {
        return ['id' => $this->fotoId];
    }
}
