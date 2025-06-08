<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use App\Models\Task;

class NewTaskAdded extends Notification
{
    use Queueable;

    protected $task;

    /**
     * Create a new notification instance.
     * @param  \App\Models\Task  $task
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [TelegramChannel::class];
    }

    /**
     * Get the Telegram representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \NotificationChannels\Telegram\TelegramMessage
     */
    public function toTelegram($notifiable): TelegramMessage
    {
        $message = "🎉 <b>New task added!</b> 🎉\n\n";
        $message .= "<b>Title:</b> " . $this->task->title . "\n";
        $message .= "<b>Created By:</b> " . $this->task->createdByUser->name . "\n";
        $message .= "<b>Assigned To:</b> " . ($this->task->assignedUser ? $this->task->assignedUser->name : 'Unassigned') . "\n";
        $message .= "<b>Status:</b> " . $this->task->status . "\n";
        $message .= "<b>View Task:</b> " . url('/api/tasks/' . $this->task->id);

        return TelegramMessage::create($message)
            ->to(config('telegram.chat_id'))
            ->token(config('telegram.token'))
            ->parseMode('HTML');
    }
}
