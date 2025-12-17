<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerCartUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_update_cart_payment_method()
    {
        // Arrange
        $user = User::factory()->create(['type' => 'customer']);
        $user->customer()->create([
            'phone_number' => '1234567890',
            'address' => 'Test Address',
            'email' => 'test@example.com'
        ]);

        $paymentMethod1 = PaymentMethod::create(['name' => 'Cash']);
        $paymentMethod2 = PaymentMethod::create(['name' => 'Card']);

        $cart = Cart::create([
            'user_id' => $user->id,
            'payment_method_id' => $paymentMethod1->id,
            'address' => 'Test Address'
        ]);

        $this->actingAs($user);

        // Act
        $response = $this->putJson("/api/customer/cart/{$cart->id}", [
            'payment_method_id' => $paymentMethod2->id
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('data.payment_method.id', $paymentMethod2->id)
            ->assertJsonPath('data.payment_method.name', 'Card');

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'payment_method_id' => $paymentMethod2->id
        ]);
    }

    public function test_customer_can_update_cart_address()
    {
        // Arrange
        $user = User::factory()->create(['type' => 'customer']);
        $user->customer()->create([
            'phone_number' => '1234567890',
            'address' => 'Test Address',
            'email' => 'test@example.com'
        ]);

        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $cart = Cart::create([
            'user_id' => $user->id,
            'payment_method_id' => $paymentMethod->id,
            'address' => 'Old Address'
        ]);

        $this->actingAs($user);

        // Act
        $response = $this->putJson("/api/customer/cart/{$cart->id}", [
            'address' => 'New Address'
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('data.address', 'New Address');

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'address' => 'New Address'
        ]);
    }

    public function test_customer_cannot_update_other_customers_cart()
    {
        // Arrange
        $user1 = User::factory()->create(['type' => 'customer']);
        $user2 = User::factory()->create(['type' => 'customer']);

        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $cart1 = Cart::create([
            'user_id' => $user1->id,
            'payment_method_id' => $paymentMethod->id,
            'address' => 'Address 1'
        ]);

        $this->actingAs($user2);

        // Act
        $response = $this->putJson("/api/customer/cart/{$cart1->id}", [
            'payment_method_id' => $paymentMethod->id
        ]);

        // Assert
        $response->assertStatus(404);
    }
}
