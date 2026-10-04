<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeachingAssignmentUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action, // 'created', 'updated', 'deleted'
        public ?array $assignmentData = null
    ) {
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('attendance.monitoring')];

        if (!empty($this->assignmentData['section_id'])) {
            $channels[] = new PrivateChannel('room-attendance.' . $this->assignmentData['section_id']);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'TeachingAssignmentUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'assignment' => $this->assignmentData,
        ];
    }
}
