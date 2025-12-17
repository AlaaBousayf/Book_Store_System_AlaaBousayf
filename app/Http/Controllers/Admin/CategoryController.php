<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return $categories;
    }


    public function store(Request $request)
    {
        $inputs = $request->validate([
            'name'=>['required']
        ]);

        $category = Category::create($inputs);

        return response()->json([
            'message'=>'category created',
            'category'=> $category
        ]);
    }

    public function show(string $id)
    {
        $category = Category::findOrFail($id);
        return $category;
    }

    public function update(Request $request, string $id)
    {
        $inputs = $request->validate([
            'name'=>['required']
        ]);

        $category = Category::findOrFail($id);
        $category->update($inputs);

        return $category;
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json([
            'message'=>'category deleted'
        ]);
    }
}
