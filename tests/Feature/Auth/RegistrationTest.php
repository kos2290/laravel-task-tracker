<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test a successful user registration.
     */
    public function test_user_can_register_successfully(): void
    {
        $password = 'password123';
        $userData = [
            'name'                  => $this->faker->name,
            'email'                 => $this->faker->unique()->safeEmail,
            'password'              => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201) // 201 Created
                 ->assertJsonStructure([
                     'message',
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'created_at',
                         'updated_at',
                     ],
                     'access_token'
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertTrue(Hash::check($password, $user->password));
    }

    /**
     * Test registration fails if required fields are missing.
     */
    public function test_registration_fails_if_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422) // Unprocessable Entity
                 ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test registration fails if email is invalid.
     */
    public function test_registration_fails_if_email_is_invalid(): void
    {
        $userData = [
            'name'                  => $this->faker->name,
            'email'                 => 'invalid-email',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test registration fails if email already exists.
     */
    public function test_registration_fails_if_email_already_exists(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $userData = [
            'name'                  => $this->faker->name,
            'email'                 => 'existing@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test registration fails if passwords do not match.
     */
    public function test_registration_fails_if_passwords_do_not_match(): void
    {
        $userData = [
            'name'                  => $this->faker->name,
            'email'                 => $this->faker->unique()->safeEmail,
            'password'              => 'password123',
            'password_confirmation' => 'mismatched_password',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test registration fails if password is too short.
     */
    public function test_registration_fails_if_password_is_too_short(): void
    {
        $userData = [
            'name'                  => $this->faker->name,
            'email'                 => $this->faker->unique()->safeEmail,
            'password'              => 'short',
            'password_confirmation' => 'short',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }
}
