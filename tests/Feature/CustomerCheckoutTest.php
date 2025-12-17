<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Cart;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_checkout()
    {
        // Arrange
        $user = User::factory()->create(['type' => 'customer']);
        $user->customer()->create([
            'phone_number' => '1234567890',
            'address' => 'Test Address',
            'email' => 'test@example.com'
        ]);

        $category = Category::create(['name' => 'Test Category']);
        $book = Book::create([
            'title' => 'Test Book',
            'publish_year' => 2021,
            'price' => 100,
            'isbn' => '1234567890',
            'category_id' => $category->id,
            'stock' => 10
        ]);

        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $cart = Cart::create([
            'user_id' => $user->id,
            'payment_method_id' => $paymentMethod->id,
            'address' => 'Test Address'
        ]);

        $cart->items()->create([
            'book_id' => $book->id,
            'qty' => 2
        ]);

        $this->actingAs($user);

        // Act
        $response = $this->postJson('/api/customer/checkout');

        // Assert
        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total', 200); // 100 * 2

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 200,
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('order_items', [
            'book_id' => $book->id,
            'qty' => 2,
            'price' => 100
        ]);

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    public function test_customer_cannot_checkout_with_empty_cart()
    {
        $user = User::factory()->create(['type' => 'customer']);
        $this->actingAs($user);

        $response = $this->postJson('/api/customer/checkout');

        $response->assertStatus(400)
            ->assertJson(['message' => 'Cart is empty']);
    }

    public function test_customer_cannot_checkout_if_stock_is_low()
    {
        // Arrange
        $user = User::factory()->create(['type' => 'customer']);
        $user->customer()->create([
            'phone_number' => '1234567890',
            'address' => 'Test Address',
            'email' => 'test@example.com'
        ]);

        $category = Category::create(['name' => 'Test Category']);
        $book = Book::create([
            'title' => 'Test Book',
            'publish_year' => 2021,
            'price' => 100,
            'isbn' => '1234567890',
            'category_id' => $category->id,
            'stock' => 1
        ]);

        $paymentMethod = PaymentMethod::create(['name' => 'Cash']);

        $cart = Cart::create([
            'user_id' => $user->id,
            'payment_method_id' => $paymentMethod->id,
            'address' => 'Test Address'
        ]);

        $cart->items()->create([
            'book_id' => $book->id,
            'qty' => 2
        ]);

        $this->actingAs($user);

        // Act
        $response = $this->postJson('/api/customer/checkout');

        // Assert
        $response->assertStatus(400)
            ->assertJson(['message' => "Not enough stock for book: {$book->title}"]);
    }
}
