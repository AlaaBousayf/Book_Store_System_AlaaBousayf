<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        User::factory()->create(['type' => 'customer']);
        User::factory()->create(['type' => 'author']);

        $response = $this->actingAs($admin)->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_block_user()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $user = User::factory()->create(['type' => 'customer', 'status' => 'active']);

        $response = $this->actingAs($admin)->putJson("/api/admin/users/{$user->id}/block");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User blocked successfully',
                'user' => [
                    'id' => $user->id,
                    'status' => 'blocked'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'blocked'
        ]);
    }

    public function test_admin_cannot_block_admin()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $otherAdmin = User::factory()->create(['type' => 'admin']);

        $response = $this->actingAs($admin)->putJson("/api/admin/users/{$otherAdmin->id}/block");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Cannot block an admin']);
    }

    public function test_non_admin_cannot_block_user()
    {
        $customer = User::factory()->create(['type' => 'customer']);
        $otherUser = User::factory()->create(['type' => 'customer']);

        $this->actingAs($customer)->putJson("/api/admin/users/{$otherUser->id}/block")
            ->assertStatus(401);
    }
}
