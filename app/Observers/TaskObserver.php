<?php

namespace App\Observers;

use App\Models\Task;
use App\Notifications\NewTaskAdded;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $task->load(['assignedUser:id,name,email', 'createdByUser:id,name,email']);

        Notification::route(TelegramChannel::class, config('telegram.chat_id'))
                    ->notify(new NewTaskAdded($task));
    }
}
