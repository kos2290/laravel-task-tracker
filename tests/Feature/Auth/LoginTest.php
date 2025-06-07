<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $password = 'secretpassword';

    /**
     * Setup the test environment.
     * Create a user before each test for login scenarios.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => Hash::make($this->password),
        ]);
    }

    /**
     * Test a successful user login.
     */
    public function test_user_can_login_successfully(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => $this->user->email,
            'password' => $this->password,
        ]);

        $response->assertStatus(200) // 200 OK
                 ->assertJsonStructure([
                     'token',
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'created_at',
                         'updated_at',
                     ],
                 ]);
    }

    /**
     * Test login fails with invalid email.
     */
    public function test_login_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'nonexistent@example.com',
            'password' => $this->password,
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'message' => 'The provided credentials do not match our records.'
                 ])
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login fails with incorrect password.
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => $this->user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'message' => 'The provided credentials do not match our records.'
                 ])
                 ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test login fails if required fields are missing.
     */
    public function test_login_fails_if_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422) // Unprocessable Entity
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test login fails if email is not in valid format.
     */
    public function test_login_fails_if_email_is_invalid_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'invalid-email-format',
            'password' => $this->password,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }
}
