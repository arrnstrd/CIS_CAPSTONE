<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailLogCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    public function __construct(public array $emailLog)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('attendance.monitoring')];
    }

    public function broadcastAs(): string
    {
        return 'EmailLogCreated';
    }

    public function broadcastWith(): array
    {
        return $this->emailLog;
    }
}
