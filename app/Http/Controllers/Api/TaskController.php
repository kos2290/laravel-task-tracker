<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a list of tasks
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tasks = Task::where('assigned_user_id', $user->id)
            ->orWhere('created_by_user_id', $user->id)
            ->with(['assignedUser:id,name,email', 'createdByUser:id,name,email'])
            ->latest()
            ->get();
        return response()->json($tasks);
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'status'           => 'sometimes|in:new,in_progress,done',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $task = $request->user()->createdTasks()->create([
            'title'            => $request->title,
            'description'      => $request->description,
            'status'           => $request->status ?? 'new',
            'assigned_user_id' => $request->assigned_user_id,
        ]);

        return response()->json($task->load(['assignedUser:id,name,email', 'createdByUser:id,name,email']), 201);
    }

    /**
     * Display the specified task
     */
    public function show(Request $request, Task $task)
    {
        $user = $request->user();
        if ($task->created_by_user_id !== $user->id && $task->assigned_user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return response()->json($task->load(['assignedUser:id,name,email', 'createdByUser:id,name,email']));
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, Task $task)
    {
        $user = $request->user();
        if ($task->created_by_user_id !== $user->id && $task->assigned_user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'            => 'sometimes|required|string|max:255',
            'description'      => 'sometimes|required|string',
            'status'           => 'sometimes|required|in:new,in_progress,done',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $originalStatus = $task->status;
        $task->update($request->all());

        if ($request->has('status') && $originalStatus !== $task->status) {
            // Broadcast event
        }

        return response()->json($task->load(['assignedUser:id,name,email', 'createdByUser:id,name,email']));
    }

    /**
     * Remove the specified task
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
}
