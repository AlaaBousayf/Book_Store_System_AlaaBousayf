<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_remove_item_from_cart()
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
            'category_id' => $category->id
        ]);

        PaymentMethod::create(['name' => 'Cash']);

        $this->actingAs($user);

        // Add item to cart
        $this->postJson("/api/customer/cart/{$book->id}");

        // Act
        $response = $this->deleteJson("/api/customer/cart/{$book->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson(['message' => 'item removed']);

        $this->assertDatabaseMissing('cart_items', [
            'book_id' => $book->id
        ]);
    }
}
