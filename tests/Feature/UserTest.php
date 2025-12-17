<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('password')
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'testuser',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'type', 'user']);
    }

    public function test_user_can_view_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $user->id, 'name' => $user->name]]);
    }

    public function test_user_can_update_profile()
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'name' => 'New Name',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Profile updated successfully']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name'
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }
}
