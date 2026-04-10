<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_endpoint_returns_records(): void
    {
        User::factory()->count(2)->create();

        $response = $this->getJson('/users');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_be_created(): void
    {
        $response = $this->postJson('/users', [
            'name' => 'Camila Torres',
            'email' => 'camila@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.email', 'camila@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'camila@example.com',
        ]);
    }

    public function test_user_creation_validates_required_fields(): void
    {
        $response = $this->postJson('/users', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);
    }
}
