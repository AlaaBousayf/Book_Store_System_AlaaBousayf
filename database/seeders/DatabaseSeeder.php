<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Category;
use App\Models\Author;
use App\Models\Book;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'admin',
                'type' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // Create a specific author for testing
        $authorUser = User::updateOrCreate(
            ['username' => 'author'],
            [
                'name' => 'Test Author',
                'type' => 'author',
                'password' => Hash::make('password'),
                'status' => 'approve',
            ]
        );
        Author::firstOrCreate(
            ['user_id' => $authorUser->id],
            ['bio' => 'Seeded test author', 'country' => 'Lebanon']
        );

        // Create a pending author for testing approval
        $pendingAuthorUser = User::updateOrCreate(
            ['username' => 'pending_author'],
            [
                'name' => 'Pending Author',
                'type' => 'author',
                'password' => Hash::make('password'),
                'status' => 'pending',
            ]
        );
        Author::firstOrCreate(
            ['user_id' => $pendingAuthorUser->id],
            ['bio' => 'Seeded pending author', 'country' => 'Lebanon']
        );

        // Create a NEW author specifically for testing the "Join" feature
        $applicantUser = User::updateOrCreate(
            ['username' => 'applicant'],
            [
                'name' => 'Applicant Author',
                'type' => 'author',
                'password' => Hash::make('password'),
                'status' => 'approve',
            ]
        );
        Author::firstOrCreate(
            ['user_id' => $applicantUser->id],
            ['bio' => 'Seeded applicant author', 'country' => 'Lebanon']
        );

        // Create a specific customer for testing
        $customerUser = User::updateOrCreate(
            ['username' => 'customer'],
            [
                'name' => 'Test Customer',
                'type' => 'customer',
                'password' => Hash::make('password'),
            ]
        );
        Customer::firstOrCreate(
            ['user_id' => $customerUser->id],
            ['email' => 'customer@example.com', 'phone_number' => '00000000', 'address' => 'Beirut']
        );

        PaymentMethod::firstOrCreate(['name' => 'Cash on Delivery']);

        $categories = Category::factory(5)->create();

        $authors = Author::factory(10)->create();

        $customers = Customer::factory(10)->create();

        // Create a specific book for the Test Author
        $authorBook = Book::factory()->create([
            'title' => 'The Author Book',
            'isbn' => '1111111111111',
            'category_id' => $categories->first()->id
        ]);
        $authorBook->user()->attach($authorUser->id, ['status' => 'approved', 'is_main' => true]);

        // Create a co-author request from Pending Author to Test Author's book
        $authorBook->user()->attach($pendingAuthorUser->id, ['status' => 'pending', 'is_main' => false]);

        $books = Book::factory(20)
            ->recycle($categories)
            ->create()
            ->each(function ($book) use ($authors) {
                $book->user()->attach($authors->random()->user_id, ['status' => 'approved', 'is_main' => true]);
            });

        Order::factory(10)
            ->recycle($customers->map(fn($c) => $c->user))
            ->create()
            ->each(function ($order) use ($books) {
                OrderItem::factory(rand(1, 3))->create([
                    'order_id' => $order->id,
                    'book_id' => $books->random()->id
                ]);
            });
    }
}
