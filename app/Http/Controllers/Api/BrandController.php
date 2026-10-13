<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    // GET /brands
    public function index(Request $request)
    {
        // Admins see every product in the count; the website only counts published ones
        return Brand::withCount(['products' => fn ($q) => $q->when(! $request->user(), fn ($p) => $p->published())])
            ->orderBy('name')->get();
    }

    // GET /brands/{id}
    public function show($id)
    {
        return Brand::withCount('products')->findOrFail($id);
    }

    // POST /brands
    public function store(Request $request)
    {
        $data = $this->validateBrand($request);

        $brand = new Brand([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);
        if ($request->hasFile('logo')) {
            $brand->logo = $request->file('logo')->store('brands', 'public');
        }
        $brand->save();

        return response()->json($brand->loadCount('products'), 201);
    }

    // PUT /brands/{id}
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        $data = $this->validateBrand($request, $brand);

        $brand->name = $data['name'];
        $brand->slug = Str::slug($data['name']);

        if ($request->boolean('remove_logo') && $brand->logo) {
            Storage::disk('public')->delete($brand->logo);
            $brand->logo = null;
        }
        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $brand->logo = $request->file('logo')->store('brands', 'public');
        }
        $brand->save();

        return $brand->loadCount('products');
    }

    // DELETE /brands/{id} (products keep existing, just without a brand)
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
        $brand->delete();

        return response()->json(['message' => 'Brand deleted successfully']);
    }

    private function validateBrand(Request $request, ?Brand $brand = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                // Slugs must be unique, so names that slug the same are rejected
                function ($attribute, $value, $fail) use ($brand) {
                    $exists = Brand::where('slug', Str::slug($value))
                        ->when($brand, fn ($q) => $q->whereKeyNot($brand->id))
                        ->exists();
                    if ($exists) {
                        $fail('A brand with this name already exists.');
                    }
                },
            ],
            // No SVG uploads: SVGs can carry scripts and are served from the admin's domain
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo' => ['nullable', Rule::in(['0', '1', 0, 1, true, false])],
        ]);
    }
}
