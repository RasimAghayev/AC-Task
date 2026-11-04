<?php

namespace Tests\Feature;

use App\Http\Controllers\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The tasks of the API.
 *
 * The answer of the API holds the payload under the key "result", and the
 * listing of the tasks under "result.data".
 */
class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/tasks';

    /**
     * The headers of an authenticated request.
     *
     * @return array<string, string>
     */
    private function tokenOf(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer '.$token];
    }

    /**
     * A request that holds every value of a task.
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Write the report',
            'description' => 'The report of the quarter.',
            'due_date' => '2026-12-01',
            'status' => 'pending',
            'priority' => 'medium',
        ], $overrides);
    }

    public function test_the_listing_answers_with_the_tasks_of_the_caller(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Task::factory()->count(2)->create(['user_id' => $user->id]);
        Task::factory()->count(3)->create(['user_id' => $other->id]);

        $response = $this->withHeaders($this->tokenOf($user))->getJson(self::ENDPOINT);

        $response->assertOk()
            ->assertJsonPath('result.message', 'Task successfully full list.')
            ->assertJsonCount(2, 'result.data');
    }

    public function test_the_listing_without_a_token_is_refused(): void
    {
        $this->getJson(self::ENDPOINT)->assertStatus(401);
    }

    public function test_a_task_is_created(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->tokenOf($user))
            ->postJson(self::ENDPOINT, $this->payload());

        $response->assertOk()
            ->assertJsonPath('result.message', 'Task successfully created')
            ->assertJsonPath('result.data.title', 'Write the report')
            ->assertJsonPath('result.data.status.value', 'pending');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Write the report',
            'user_id' => $user->id,
        ]);
    }

    public function test_a_task_without_a_title_is_refused(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->tokenOf($user))
            ->postJson(self::ENDPOINT, $this->payload(['title' => '']))
            ->assertStatus(422);

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_a_task_with_a_status_outside_the_list_is_refused(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->tokenOf($user))
            ->postJson(self::ENDPOINT, $this->payload(['status' => 'invented']))
            ->assertStatus(422);

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_one_task_is_answered(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'title' => 'Write the report']);

        $this->withHeaders($this->tokenOf($user))
            ->getJson(self::ENDPOINT.'/'.$task->id)
            ->assertOk()
            ->assertJsonPath('result.data.title', 'Write the report');
    }

    public function test_the_task_of_another_user_is_not_answered(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $other->id]);

        $this->withHeaders($this->tokenOf($user))
            ->getJson(self::ENDPOINT.'/'.$task->id)
            ->assertStatus(403);
    }

    public function test_a_task_that_does_not_exist_answers_not_found(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->tokenOf($user))
            ->getJson(self::ENDPOINT.'/999999')
            ->assertStatus(404);
    }

    public function test_a_task_is_changed(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'title' => 'Old title']);

        $this->withHeaders($this->tokenOf($user))
            ->putJson(self::ENDPOINT.'/'.$task->id, $this->payload(['title' => 'New title']))
            ->assertOk()
            ->assertJsonPath('result.data.title', 'New title');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'New title']);
    }

    public function test_the_task_of_another_user_is_not_changed(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $other->id, 'title' => 'Old title']);

        $this->withHeaders($this->tokenOf($user))
            ->putJson(self::ENDPOINT.'/'.$task->id, $this->payload(['title' => 'New title']))
            ->assertStatus(403);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Old title']);
    }

    public function test_a_task_is_removed(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->withHeaders($this->tokenOf($user))
            ->deleteJson(self::ENDPOINT.'/'.$task->id)
            ->assertOk()
            ->assertJsonPath('result.message', 'Task successfully deleted');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
