<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoAuthorController extends Controller
{
    public function join(Request $request)
    {
        $request->validate([
            'isbn' => ['required', 'exists:books,isbn']
        ]);

        $book = Book::where('isbn', $request->isbn)->firstOrFail();
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->books()->where('book_id', $book->id)->exists()) {
            return response()->json(['message' => 'Already associated with this book'], 400);
        }

        $user->books()->attach($book->id, ['is_main' => false, 'status' => 'pending']);

        return response()->json(['message' => 'Request sent successfully']);
    }

    public function requests()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $mainBooks = $user->books()->wherePivot('is_main', true)->pluck('books.id');

        $requests = Book::whereIn('id', $mainBooks)
            ->with(['user' => function ($query) {
                $query->wherePivot('status', 'pending');
            }])
            ->get()
            ->flatMap(function ($book) {
                return $book->user->map(function ($user) use ($book) {
                    return [
                        'book_id' => $book->id,
                        'book_title' => $book->title,
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'status' => $user->pivot->status
                    ];
                });
            });

        return response()->json(['data' => $requests]);
    }

    public function approve(Request $request, $bookId, $userId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->books()->where('book_id', $bookId)->wherePivot('is_main', true)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $book = Book::findOrFail($bookId);
        $book->user()->updateExistingPivot($userId, ['status' => 'approved']);

        return response()->json(['message' => 'Request approved']);
    }

    public function reject(Request $request, $bookId, $userId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->books()->where('book_id', $bookId)->wherePivot('is_main', true)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $book = Book::findOrFail($bookId);
        $book->user()->detach($userId);

        return response()->json(['message' => 'Request rejected']);
    }
}
