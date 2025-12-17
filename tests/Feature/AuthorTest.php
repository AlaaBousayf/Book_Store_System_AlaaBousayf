<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_list_categories()
    {
        $author = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        Sanctum::actingAs($author, ['*']);
        Category::create(['name' => 'Fiction']);
        Category::create(['name' => 'Non-Fiction']);

        $response = $this->getJson('/api/author/category');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at']
                ]
            ]);
    }

    public function test_author_can_create_book()
    {
        $author = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        Sanctum::actingAs($author, ['*']);
        $category = Category::create(['name' => 'Fiction']);

        $response = $this->postJson('/api/author/book', [
            'title' => 'My Book',
            'publish_year' => 2023,
            'price' => 19.99,
            'isbn' => '1234567890',
            'category_id' => $category->id
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'category', 'status']
            ])
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_author_can_publish_book()
    {
        $author = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        Sanctum::actingAs($author, ['*']);
        $category = Category::create(['name' => 'Fiction']);
        $book = Book::create([
            'title' => 'My Book',
            'publish_year' => 2023,
            'price' => 19.99,
            'isbn' => '1234567890',
            'category_id' => $category->id,
            'status' => 'draft'
        ]);
        $author->books()->attach($book->id);

        $response = $this->putJson("/api/author/book/{$book->id}/publish");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'published');
    }

    public function test_author_can_list_books()
    {
        $author = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        Sanctum::actingAs($author, ['*']);
        $category = Category::create(['name' => 'Fiction']);
        $book = Book::create([
            'title' => 'My Book',
            'publish_year' => 2023,
            'price' => 19.99,
            'isbn' => '1234567890',
            'category_id' => $category->id
        ]);
        $author->books()->attach($book->id);

        $response = $this->getJson('/api/author/book');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'category']
                ]
            ]);
    }

    public function test_author_can_view_orders_for_their_books()
    {
        $author = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        Sanctum::actingAs($author, ['*']);
        $category = Category::create(['name' => 'Fiction']);

        $book1 = Book::create([
            'title' => 'Author Book',
            'publish_year' => 2023,
            'price' => 20,
            'isbn' => '1234567890',
            'category_id' => $category->id
        ]);
        $author->books()->attach($book1->id);

        $otherBook = Book::create([
            'title' => 'Other Book',
            'publish_year' => 2023,
            'price' => 15,
            'isbn' => '0987654321',
            'category_id' => $category->id
        ]);

        $customer = User::factory()->create(['type' => 'customer']);
        $paymentMethod = PaymentMethod::create(['name' => 'Credit Card', 'type' => 'card']);

        $order = Order::create([
            'user_id' => $customer->id,
            'payment_method_id' => $paymentMethod->id,
            'address' => '123 Main St',
            'total' => 35,
            'status' => 'pending'
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book1->id,
            'qty' => 1,
            'price' => 20
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $otherBook->id,
            'qty' => 1,
            'price' => 15
        ]);

        $response = $this->getJson('/api/author/orders');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order->id)
            ->assertJsonCount(1, 'data.0.items') // Should only see their own book
            ->assertJsonPath('data.0.items.0.book_id', $book1->id);
    }

    public function test_author_can_update_book_stock()
    {
        $author = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        Sanctum::actingAs($author, ['*']);
        $category = Category::create(['name' => 'Fiction']);
        $book = Book::create([
            'title' => 'My Book',
            'publish_year' => 2023,
            'price' => 19.99,
            'isbn' => '1234567890',
            'category_id' => $category->id,
            'stock' => 0
        ]);
        $author->books()->attach($book->id);

        $response = $this->putJson("/api/author/book/{$book->id}/stock", [
            'stock' => 50
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.stock', 50);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'stock' => 50
        ]);
    }

    public function test_author_can_request_to_join_book()
    {
        $mainAuthor = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        $subAuthor = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        $category = Category::create(['name' => 'Fiction']);

        Sanctum::actingAs($mainAuthor, ['*']);
        $book = Book::create([
            'title' => 'Main Author Book',
            'publish_year' => 2023,
            'price' => 20,
            'isbn' => '1234567890',
            'category_id' => $category->id
        ]);
        $mainAuthor->books()->attach($book->id, ['is_main' => true, 'status' => 'approved']);

        Sanctum::actingAs($subAuthor, ['*']);
        $response = $this->postJson('/api/author/co-author/join', [
            'isbn' => '1234567890'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Request sent successfully']);

        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'user_id' => $subAuthor->id,
            'status' => 'pending',
            'is_main' => false
        ]);
    }

    public function test_main_author_can_approve_request()
    {
        $mainAuthor = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        $subAuthor = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        $category = Category::create(['name' => 'Fiction']);

        $book = Book::create([
            'title' => 'Main Author Book',
            'publish_year' => 2023,
            'price' => 20,
            'isbn' => '1234567890',
            'category_id' => $category->id
        ]);
        $mainAuthor->books()->attach($book->id, ['is_main' => true, 'status' => 'approved']);
        $subAuthor->books()->attach($book->id, ['is_main' => false, 'status' => 'pending']);

        Sanctum::actingAs($mainAuthor, ['*']);

        // List requests
        $response = $this->getJson('/api/author/co-author/requests');
        $response->assertStatus(200)
            ->assertJsonPath('data.0.user_id', $subAuthor->id);

        // Approve
        $response = $this->putJson("/api/author/co-author/requests/{$book->id}/{$subAuthor->id}/approve");
        $response->assertStatus(200)
            ->assertJson(['message' => 'Request approved']);

        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'user_id' => $subAuthor->id,
            'status' => 'approved'
        ]);
    }

    public function test_main_author_can_approve_multiple_co_authors()
    {
        $mainAuthor = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        $subAuthor1 = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        $subAuthor2 = User::factory()->create(['type' => 'author', 'status' => 'approve']);
        $category = Category::create(['name' => 'Fiction']);

        $book = Book::create([
            'title' => 'Collaborative Book',
            'publish_year' => 2023,
            'price' => 20,
            'isbn' => '9999999999',
            'category_id' => $category->id
        ]);
        $mainAuthor->books()->attach($book->id, ['is_main' => true, 'status' => 'approved']);

        // Both authors request to join
        $subAuthor1->books()->attach($book->id, ['is_main' => false, 'status' => 'pending']);
        $subAuthor2->books()->attach($book->id, ['is_main' => false, 'status' => 'pending']);

        Sanctum::actingAs($mainAuthor, ['*']);

        // Approve first author
        $this->putJson("/api/author/co-author/requests/{$book->id}/{$subAuthor1->id}/approve")
            ->assertStatus(200);

        // Approve second author
        $this->putJson("/api/author/co-author/requests/{$book->id}/{$subAuthor2->id}/approve")
            ->assertStatus(200);

        // Verify both are approved
        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'user_id' => $subAuthor1->id,
            'status' => 'approved'
        ]);

        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'user_id' => $subAuthor2->id,
            'status' => 'approved'
        ]);
    }
}
