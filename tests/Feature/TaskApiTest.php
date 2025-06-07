<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Laravel\Sanctum\Sanctum;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        Sanctum::actingAs($this->user, ['*']);
    }

    public function test_user_can_see_only_their_tasks()
    {
        $task1 = Task::factory()->create([
            'created_by_user_id' => $this->user->id,
            'assigned_user_id'   => $this->anotherUser->id,
        ]);

        $task2 = Task::factory()->create([
            'created_by_user_id' => $this->anotherUser->id,
            'assigned_user_id'   => $this->user->id,
        ]);

        $task3 = Task::factory()->create([
            'created_by_user_id' => $this->anotherUser->id,
            'assigned_user_id'   => $this->anotherUser->id,
        ]);

        $response = $this->getJson('/api/tasks');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $task1->id])
            ->assertJsonFragment(['id' => $task2->id])
            ->assertJsonMissing(['id' => $task3->id]);
    }

    /** @test */
    public function user_can_create_a_task(): void
    {
        $taskData = [
            'title'            => 'New test task',
            'description'      => 'Description of the task',
            'assigned_user_id' => $this->anotherUser->id,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response
            ->assertStatus(201) // 201 Created
            ->assertJsonFragment(['title' => $taskData['title']]);

        $this->assertDatabaseHas('tasks', [
            'title'              => $taskData['title'],
            'created_by_user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function create_task_fails_if_title_is_missing(): void
    {
        $taskData = [
            'description' => 'Description of the task without title',
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response
            ->assertStatus(422) // 422 Unprocessable Entity
            ->assertJsonFragment(['title' => ['The title field is required.']]);
    }

    /** @test */
    public function user_can_view_their_assigned_task(): void
    {
        $task = Task::factory()->create(['assigned_user_id' => $this->user->id]);

        $this->getJson('/api/tasks/' . $task->id)
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $task->id]);
    }

    /** @test */
    public function user_cannot_view_an_unrelated_task(): void
    {
        $task = Task::factory()->create([
            'created_by_user_id' => $this->anotherUser->id,
            'assigned_user_id'   => $this->anotherUser->id,
        ]);

        $this->getJson('/api/tasks/' . $task->id)->assertStatus(403);
    }

    /** @test */
    public function user_can_update_their_task(): void
    {
        $task = Task::factory()->create(['created_by_user_id' => $this->user->id]);

        $updateData = [
            'title'  => 'Updated title',
            'status' => 'in_progress',
        ];

        $this->putJson('/api/tasks/' . $task->id, $updateData)
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $updateData['title'], 'status' => 'in_progress']);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => $updateData['title']]);
    }

    /** @test */
    public function user_cannot_update_an_unrelated_task(): void
    {
        $task = Task::factory()->create([
            'created_by_user_id' => $this->anotherUser->id,
            'assigned_user_id'   => $this->anotherUser->id,
        ]);

        $updateData = ['title' => 'Updated title'];

        $this->putJson('/api/tasks/' . $task->id, $updateData)->assertStatus(403);
    }

    /** @test */
    public function user_can_delete_their_task(): void
    {
        $task = Task::factory()->create(['created_by_user_id' => $this->user->id]);

        $this->deleteJson('/api/tasks/' . $task->id)
            ->assertStatus(204); // 204 No Content

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function user_cannot_delete_an_unrelated_task(): void
    {
        $task = Task::factory()->create([
            'created_by_user_id' => $this->anotherUser->id,
            'assigned_user_id'   => $this->anotherUser->id,
        ]);

        $this->deleteJson('/api/tasks/' . $task->id)->assertStatus(403);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
