<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // GET /categories
    public function index(Request $request)
    {
        // Admins see every product in the count; the website only counts published ones
        return response()->json(
            Category::withCount(['products' => fn ($q) => $q->when(! $request->user(), fn ($p) => $p->published())])
                ->orderBy('position')->orderBy('name')->get()
        );
    }

    // GET /categories/{id}
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    // POST /categories
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|integer|min:0|max:999',
        ]);

        $slug = Str::slug($request->name);

        $category = Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'position' => $request->input('position', (Category::max('position') ?? 0) + 1),
        ]);

        return response()->json($category, 201);
    }

    // PUT /categories/{id}
    public function update(Request $request, $id)
    {

       

        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|integer|min:0|max:999',
        ]);

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if ($request->filled('position')) {
            $category->position = $request->integer('position');
        }
        $category->save();

        return response()->json($category);
    }

    // DELETE /categories/{id}
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
