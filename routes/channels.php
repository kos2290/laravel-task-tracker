<?php

use App\Models\Task;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('task.{taskId}', function ($user, $taskId) {
    $task = Task::find($taskId);

    if ($task) {
        return $user->id === (int) $task->created_by_user_id || $user->id === (int) $task->assigned_user_id;
    }

    return false;
});
