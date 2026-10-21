<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The authentication of the API.
 *
 * The guard of the API is the one of the token, so the tests ask the
 * framework for a token of a user and send it in the header.
 *
 * The answer of the API holds the payload under the key "result" and the
 * message of a failure under the key "error".
 */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/auth';

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

    public function test_a_user_is_registered(): void
    {
        $response = $this->postJson(self::ENDPOINT.'/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ]);

        $response->assertOk()
            ->assertJsonPath('result.user.name', 'John Doe')
            ->assertJsonPath('result.user.email', 'john@example.com')
            ->assertJsonStructure([
                'result' => [
                    'user',
                    'authorization' => ['access_token', 'token_type', 'expires_in'],
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_a_registration_without_a_name_is_refused(): void
    {
        $this->postJson(self::ENDPOINT.'/register', [
            'email' => 'john@example.com',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ])->assertStatus(422);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_registration_with_an_address_that_is_taken_is_refused(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $this->postJson(self::ENDPOINT.'/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ])->assertStatus(422);
    }

    public function test_a_registration_with_a_short_password_is_refused(): void
    {
        $this->postJson(self::ENDPOINT.'/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertStatus(422);
    }

    public function test_a_user_logs_in(): void
    {
        User::factory()->create(['email' => 'john@example.com', 'password' => 'secret12345']);

        $response = $this->postJson(self::ENDPOINT.'/login', [
            'email' => 'john@example.com',
            'password' => 'secret12345',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['result' => ['access_token', 'token_type', 'expires_in']]);
    }

    public function test_a_login_with_the_wrong_password_is_refused(): void
    {
        User::factory()->create(['email' => 'john@example.com', 'password' => 'secret12345']);

        $this->postJson(self::ENDPOINT.'/login', [
            'email' => 'john@example.com',
            'password' => 'the-wrong-password',
        ])->assertStatus(401);
    }

    public function test_the_profile_of_the_caller_is_answered(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $this->withHeaders($this->tokenOf($user))
            ->getJson(self::ENDPOINT.'/me')
            ->assertOk()
            ->assertJsonPath('result.name', 'John Doe');
    }

    public function test_the_profile_without_a_token_is_refused(): void
    {
        $this->getJson(self::ENDPOINT.'/me')->assertStatus(401);
    }

    public function test_the_profile_is_changed(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $this->withHeaders($this->tokenOf($user))
            ->putJson(self::ENDPOINT.'/me', ['name' => 'Jane Doe'])
            ->assertOk()
            ->assertJsonPath('result.user.name', 'Jane Doe');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Jane Doe']);
    }

    public function test_the_token_is_invalidated_by_the_logout(): void
    {
        $user = User::factory()->create();
        $headers = $this->tokenOf($user);

        $this->withHeaders($headers)
            ->postJson(self::ENDPOINT.'/logout')
            ->assertOk();

        $this->withHeaders($headers)
            ->getJson(self::ENDPOINT.'/me')
            ->assertStatus(401);
    }
}
