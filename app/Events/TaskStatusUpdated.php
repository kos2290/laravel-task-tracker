<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Task $task;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task->load(['assignedUser:id,name,email', 'createdByUser:id,name,email']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('task.' . $this->task->id)];
    }

    /**
     * Get the data to broadcast with the task status update event.
     *
     * @return array<string, mixed> The data array containing task details such as id, title, description, and status.
     */

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->task->id,
            'title'       => $this->task->title,
            'description' => $this->task->description,
            'status'      => $this->task->status,
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'task.status.updated';
    }
}
