<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Events\TaskStatusUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Pusher\Pusher;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    /**
     * Display a list of tasks
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tasks = Task::where('assigned_user_id', $user->id)
            ->orWhere('created_by_user_id', $user->id)
            ->with(['assignedUser:id,name,email', 'createdByUser:id,name,email'])
            ->latest()
            ->get();
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created task
     *
     * @param  \App\Http\Requests\StoreTaskRequest  $request
     * @return \App\Http\Resources\TaskResource
     */
    public function store(StoreTaskRequest $request)
    {
        $task = $request->user()->createdTasks()->create($request->validated());
        return new TaskResource($task->load(['assignedUser:id,name,email', 'createdByUser:id,name,email']));
    }

    /**
     * Display the specified task
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \App\Http\Resources\TaskResource|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Task $task)
    {
        $user = $request->user();
        if ($task->created_by_user_id !== $user->id && $task->assigned_user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return new TaskResource($task->load(['assignedUser:id,name,email', 'createdByUser:id,name,email']));
    }

    /**
     * Update the specified task
     *
     * @param  \App\Http\Requests\UpdateTaskRequest  $request
     * @param  \App\Models\Task  $task
     * @return \App\Http\Resources\TaskResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $user = $request->user();
        if ($task->created_by_user_id !== $user->id && $task->assigned_user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $originalStatus = $task->status;
        $task->update($request->validated());

        if ($request->has('status') && $originalStatus !== $task->status) {
            $this->sendMessageToPusher($task);
        }

        return new TaskResource($task->load(['assignedUser:id,name,email', 'createdByUser:id,name,email']));
    }

    /**
     * Remove the specified task
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Task $task)
    {
        $user = $request->user();
        if ($task->created_by_user_id !== $user->id && $task->assigned_user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden - You cannot delete this task'], 403);
        }

        $task->delete();
        return response()->json(null, 204);
    }

    /**
     * Broadcasts a TaskStatusUpdated event to Pusher.
     * Sends a notification manually to prevent conflict with default BroadcastManager
     *
     * @param  Task  $task
     * @return bool
     */
    public function sendMessageToPusher(Task $task): bool
    {
        $pusherConfig = Config::get('broadcasting.connections.pusher');

        if (empty($pusherConfig['key'])
            || empty($pusherConfig['secret'])
            || empty($pusherConfig['app_id'])
        ) {
            Log::error('Pusher configuration is incomplete. Cannot broadcast.');
            return false;
        }

        $pusher = new Pusher(
            $pusherConfig['key'],
            $pusherConfig['secret'],
            $pusherConfig['app_id'],
            $pusherConfig['options'] ?? []
        );

        $broadcaster = new PusherBroadcaster($pusher, $pusherConfig);
        $event = new TaskStatusUpdated($task);
        $channelNames = collect($event->broadcastOn())->map(fn ($channel) => $channel->name)->all();
        $eventName = method_exists($event, 'broadcastAs') ? $event->broadcastAs() : class_basename($event);

        try {
            $broadcaster->broadcast(
                $channelNames,
                $eventName,
                $event->broadcastWith()
            );
            return true;
        } catch (\Exception $e) {
            Log::error('Error broadcasting event: ' . $e->getMessage());
            return false;
        }
    }
}
