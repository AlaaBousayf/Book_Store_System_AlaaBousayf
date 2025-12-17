<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Resources\Author\BookResource;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $books = $user->books()->with('category')->get();
        return BookResource::collection($books);
    }

    public function store(Request $request)
    {
        $inputs = $request->validate([
            'title' => ['required','max:255'],
            'publish_year' => ['required','digits:4','integer'],
            'price' => ['required','numeric'],
            'isbn' => ['required', 'unique:books,isbn'],
            'category_id' => ['required','exists:categories,id'],
            'stock' => ['sometimes', 'integer', 'min:0'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $book = Book::create($inputs);
        $user->books()->attach($book->id, ['is_main' => true, 'status' => 'approved']);
        $book->refresh();

        return new BookResource($book->load('category'));
    }

    public function show(string $id)
    {
          /** @var \App\Models\User $user */
        $user = Auth::user();
        $book = $user->books()->with('category')->findOrFail($id);
        return new BookResource($book);
    }

    public function update(Request $request, string $id)
    {
        $inputs = $request->validate([
            'title' => ['required','max:255'],
            'publish_year' => ['required','digits:4','integer'],
            'price' => ['required','numeric'],
            'isbn' => ['required', 'unique:books,isbn,'.$id],
            'category_id' => ['required','exists:categories,id'],
            'stock' => ['sometimes', 'integer', 'min:0'],
        ]);
          /** @var \App\Models\User $user */
        $user = Auth::user();
        $book = $user->books()->findOrFail($id);
        $book->update($inputs);

        return new BookResource($book->load('category'));
    }

    public function destroy(string $id)
    {
          /** @var \App\Models\User $user */
        $user = Auth::user();
        $book = $user->books()->findOrFail($id);
        $book->delete();
        return response()->noContent();
    }

    public function publish(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $book = $user->books()->findOrFail($id);
        $book->update(['status' => 'published']);
        return new BookResource($book->load('category'));
    }

    public function updateStock(Request $request, string $id)
    {
        $inputs = $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $book = $user->books()->findOrFail($id);
        $book->update(['stock' => $inputs['stock']]);

        return new BookResource($book->load('category'));
    }
}
