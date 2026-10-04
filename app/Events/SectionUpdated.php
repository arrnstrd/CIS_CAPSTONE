<?php

namespace App\Events;

use App\Models\Section;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SectionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action, // 'created', 'updated', 'deleted'
        public ?array $sectionData = null
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('attendance.monitoring'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SectionUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'section' => $this->sectionData,
        ];
    }
}
