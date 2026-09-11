<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action, // 'created', 'updated', 'deleted'
        public ?array $studentData = null
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
        return 'StudentUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'student' => $this->studentData,
        ];
    }
}
