<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PengaturanDiubah implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $pengaturan) {}

    public function broadcastOn(): Channel
    {
        return new Channel('gallery');
    }

    public function broadcastAs(): string
    {
        return 'pengaturan.diubah';
    }

    public function broadcastWith(): array
    {
        return $this->pengaturan;
    }
}
