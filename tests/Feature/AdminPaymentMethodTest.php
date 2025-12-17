<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_payment_methods()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        PaymentMethod::create(['name' => 'Cash']);
        PaymentMethod::create(['name' => 'Card']);

        $response = $this->actingAs($admin)->getJson('/api/admin/payment-method');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_create_payment_method()
    {
        $admin = User::factory()->create(['type' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/admin/payment-method', [
            'name' => 'PayPal'
        ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Payment method created successfully']);

        $this->assertDatabaseHas('payment_methods', ['name' => 'PayPal']);
    }

    public function test_admin_can_show_payment_method()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $response = $this->actingAs($admin)->getJson("/api/admin/payment-method/{$paymentMethod->id}");

        $response->assertStatus(200)
            ->assertJson(['data' => ['name' => 'Cash']]);
    }

    public function test_admin_can_update_payment_method()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $response = $this->actingAs($admin)->putJson("/api/admin/payment-method/{$paymentMethod->id}", [
            'name' => 'Credit Card'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Payment method updated successfully']);

        $this->assertDatabaseHas('payment_methods', ['name' => 'Credit Card']);
    }

    public function test_admin_can_delete_payment_method()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $response = $this->actingAs($admin)->deleteJson("/api/admin/payment-method/{$paymentMethod->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Payment method deleted successfully']);

        $this->assertDatabaseMissing('payment_methods', ['id' => $paymentMethod->id]);
    }

    public function test_non_admin_cannot_manage_payment_methods()
    {
        $customer = User::factory()->create(['type' => 'customer']);

        $this->actingAs($customer)->getJson('/api/admin/payment-method')->assertStatus(401);
        $this->actingAs($customer)->postJson('/api/admin/payment-method', ['name' => 'Test'])->assertStatus(401);
    }
}
