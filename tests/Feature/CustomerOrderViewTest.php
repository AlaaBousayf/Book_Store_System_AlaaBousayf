<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerOrderViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_their_order()
    {
        // Arrange
        $user = User::factory()->create(['type' => 'customer']);
        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 100,
            'payment_method_id' => $paymentMethod->id,
            'address' => 'Test Address',
            'status' => 'pending'
        ]);

        $this->actingAs($user);

        // Act
        $response = $this->getJson("/api/customer/orders/{$order->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.total', 100);
    }

    public function test_customer_cannot_view_others_order()
    {
        // Arrange
        $user1 = User::factory()->create(['type' => 'customer']);
        $user2 = User::factory()->create(['type' => 'customer']);
        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $order = Order::create([
            'user_id' => $user1->id,
            'total' => 100,
            'payment_method_id' => $paymentMethod->id,
            'address' => 'Test Address',
            'status' => 'pending'
        ]);

        $this->actingAs($user2);

        // Act
        $response = $this->getJson("/api/customer/orders/{$order->id}");

        // Assert
        $response->assertStatus(404);
    }
}
