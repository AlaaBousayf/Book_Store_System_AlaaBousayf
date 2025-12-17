<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_order_status()
    {
        // Arrange
        $admin = User::factory()->create(['type' => 'admin']);
        $customer = User::factory()->create(['type' => 'customer']);
        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $order = Order::create([
            'user_id' => $customer->id,
            'total' => 100,
            'payment_method_id' => $paymentMethod->id,
            'address' => 'Test Address',
            'status' => 'pending'
        ]);

        $this->actingAs($admin);

        // Act
        $response = $this->putJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'shipped'
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order status updated successfully',
                'order' => [
                    'id' => $order->id,
                    'status' => 'shipped'
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped'
        ]);
    }

    public function test_non_admin_cannot_update_order_status()
    {
        // Arrange
        $customer = User::factory()->create(['type' => 'customer']);
        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $order = Order::create([
            'user_id' => $customer->id,
            'total' => 100,
            'payment_method_id' => $paymentMethod->id,
            'address' => 'Test Address',
            'status' => 'pending'
        ]);

        $this->actingAs($customer);

        // Act
        $response = $this->putJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'shipped'
        ]);

        // Assert
        $response->assertStatus(401); // Or 403 depending on middleware
    }
}
