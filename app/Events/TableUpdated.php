namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TableUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tableName; // e.g., 'students', 'products', 'orders'
    public $rows;      // The fresh data array for that specific table

    // Accept BOTH variables when the event is triggered
    public function __construct($tableName, $rows)
    {
        $this->tableName = $tableName;
        $this->rows = $rows;
    }

    // Broadcast to a channel specific to that table!
    public function broadcastOn(): array
    {
        return [
            new Channel('table-channel.' . $this->tableName),
        ];
    }
}
