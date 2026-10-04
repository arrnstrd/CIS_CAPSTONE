<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    public function __construct(public array $attendance)
    {
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('attendance.monitoring')];

        if (!empty($this->attendance['section_id'])) {
            $channels[] = new PrivateChannel('room-attendance.' . $this->attendance['section_id']);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'AttendanceRecorded';
    }

    public function broadcastWith(): array
    {
        return $this->attendance;
    }
}