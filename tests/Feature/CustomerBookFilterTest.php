<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerBookFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_filter_books_by_category()
    {
        // Arrange
        $user = User::factory()->create(['type' => 'customer']);
        $category1 = Category::create(['name' => 'Fiction']);
        $category2 = Category::create(['name' => 'Non-Fiction']);

        $book1 = Book::create([
            'title' => 'Fiction Book',
            'publish_year' => 2021,
            'price' => 100,
            'isbn' => '111111',
            'category_id' => $category1->id,
            'status' => 'published'
        ]);

        $book2 = Book::create([
            'title' => 'Non-Fiction Book',
            'publish_year' => 2021,
            'price' => 100,
            'isbn' => '222222',
            'category_id' => $category2->id,
            'status' => 'published'
        ]);

        $this->actingAs($user);

        // Act
        $response = $this->getJson("/api/customer/book?category_id={$category1->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $book1->id)
            ->assertJsonPath('data.0.category.id', $category1->id);
    }

    public function test_customer_can_view_all_books()
    {
        // Arrange
        $user = User::factory()->create(['type' => 'customer']);
        $category = Category::create(['name' => 'Fiction']);

        Book::create([
            'title' => 'Book 1',
            'publish_year' => 2021,
            'price' => 100,
            'isbn' => '111111',
            'category_id' => $category->id,
            'status' => 'published'
        ]);

        Book::create([
            'title' => 'Book 2',
            'publish_year' => 2021,
            'price' => 100,
            'isbn' => '222222',
            'category_id' => $category->id,
            'status' => 'published'
        ]);

        $this->actingAs($user);

        // Act
        $response = $this->getJson("/api/customer/book");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
