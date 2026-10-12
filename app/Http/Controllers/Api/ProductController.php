<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    private const MAX_IMAGES = 8;

    private const MAX_PER_PAGE = 48;

    /**
     * GET /products
     * Without `page`/`per_page` this returns every product (used by the admin panel).
     * With them it returns a filtered, sorted page:
     * ?page=1&per_page=12&category=laptops&brand=asus,dell&min_price=&max_price=&in_stock=1&q=&sort=price-asc&ids=1,2
     */
    public function index(Request $request)
    {
        $query = Product::query()->filter($this->filters($request))->sorted($request->query('sort'));

        if (! $request->hasAny(['page', 'per_page'])) {
            return $query->get();
        }

        $perPage = min(max((int) $request->query('per_page', 12), 1), self::MAX_PER_PAGE);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * GET /products/facets
     * Counts for each filter option given the other active filters, plus the catalogue price range.
     */
    public function facets(Request $request)
    {
        $f = $this->filters($request);

        $categoryCounts = Product::query()->filter($f, 'category')
            ->selectRaw('category_id, count(*) as count')->groupBy('category_id')->pluck('count', 'category_id');
        $brandCounts = Product::query()->filter($f, 'brand')
            ->selectRaw('brand_id, count(*) as count')->groupBy('brand_id')->pluck('count', 'brand_id');

        $categories = Category::has('products')->withCount('products')->orderBy('position')->orderBy('id')->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'total' => $c->products_count, 'count' => (int) ($categoryCounts[$c->id] ?? 0)]);

        // Ordered by catalogue size so the list doesn't reshuffle as filters change
        $brands = Brand::has('products')->withCount('products')->orderByDesc('products_count')->orderBy('name')->get()
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'slug' => $b->slug, 'logo' => $b->logo, 'total' => $b->products_count, 'count' => (int) ($brandCounts[$b->id] ?? 0)]);

        $prices = Product::where('price', '>', 0)->selectRaw('min(price) as min, max(price) as max')->first();

        return [
            'categories' => $categories,
            'brands' => $brands,
            'price' => ['min' => (float) ($prices->min ?? 0), 'max' => (float) ($prices->max ?? 0)],
            'total' => Product::query()->filter($f)->count(),
        ];
    }

    /**
     * GET /products/overview?limit=4
     * Each category that has products, with its total and a few products (in stock first, newest first).
     */
    public function overview(Request $request)
    {
        $limit = min(max((int) $request->query('limit', 4), 1), 12);

        return Category::has('products')->withCount('products')->orderBy('position')->orderBy('id')->get()
            ->map(fn ($category) => [
                'category' => ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug],
                'total' => $category->products_count,
                'products' => $category->products()->orderByRaw('quantity <= 0')->orderByDesc('id')->limit($limit)->get(),
            ]);
    }

    public function show($id)
    {
        return Product::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);
        $uploads = $this->uploadedImages($request);
        $this->ensureImageLimit(count($uploads));

        $order = $request->input('image_order', []);

        $product = DB::transaction(function () use ($data, $uploads, $order) {
            $product = Product::create($data);
            $created = $this->attachImages($product, $uploads);
            $this->applyOrder($product, $order, $created);
            $product->syncCoverImage();

            return $product;
        });

        return $product->fresh();
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $this->validateProduct($request);
        $uploads = $this->uploadedImages($request);
        $removeIds = array_map('intval', $request->input('remove_images', []));
        $order = $request->input('image_order', []);

        $remaining = $product->images()->whereNotIn('id', $removeIds)->count();
        $this->ensureImageLimit($remaining + count($uploads));

        DB::transaction(function () use ($product, $data, $uploads, $removeIds, $order) {
            $product->update($data);

            // Remove photos the admin deleted (only ones belonging to this product)
            $product->images()->whereIn('id', $removeIds)->get()->each(function ($image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });

            $created = $this->attachImages($product, $uploads);
            $this->applyOrder($product, $order, $created);
            $product->syncCoverImage();
        });

        return $product->fresh();
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    /**
     * Storefront filters from the query string, all optional.
     */
    private function filters(Request $request): array
    {
        $list = fn (string $key) => array_values(array_filter(array_map('trim', explode(',', (string) $request->query($key, '')))));

        return [
            'category' => $request->query('category') ?: null,
            'brands' => $list('brand'),
            'min' => is_numeric($request->query('min_price')) ? (float) $request->query('min_price') : null,
            'max' => is_numeric($request->query('max_price')) ? (float) $request->query('max_price') : null,
            'in_stock' => $request->boolean('in_stock'),
            'q' => trim((string) $request->query('q', '')) ?: null,
            'ids' => array_map('intval', $list('ids')),
        ];
    }

    private function validateProduct(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'images' => 'nullable|array|max:'.self::MAX_IMAGES,
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
            'image_order' => 'nullable|array',
            'image_order.*' => ['string', 'regex:/^[en]\d+$/'],
        ]);

        return collect($data)->only(['name', 'label', 'description', 'price', 'quantity', 'category_id', 'brand_id'])->all();
    }

    /**
     * New photos from `images[]`, plus the older single `image` field for compatibility.
     *
     * @return UploadedFile[]
     */
    private function uploadedImages(Request $request): array
    {
        $files = $request->file('images', []);
        if ($request->hasFile('image')) {
            $files[] = $request->file('image');
        }

        return array_values(array_filter($files));
    }

    private function ensureImageLimit(int $total): void
    {
        if ($total > self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'images' => 'A product can have at most '.self::MAX_IMAGES.' photos.',
            ]);
        }
    }

    /**
     * @param  UploadedFile[]  $files
     * @return \App\Models\ProductImage[] created images, in upload order
     */
    private function attachImages(Product $product, array $files): array
    {
        $next = (int) $product->images()->max('sort_order') + ($product->images()->exists() ? 1 : 0);
        $created = [];

        foreach ($files as $file) {
            $created[] = $product->images()->create([
                'path' => $file->store('products', 'public'),
                'sort_order' => $next++,
            ]);
        }

        return $created;
    }

    /**
     * Apply the admin's photo order. Tokens are "e{id}" for saved photos and
     * "n{index}" for photos uploaded in this request. The first photo is the cover.
     *
     * @param  string[]  $order
     * @param  \App\Models\ProductImage[]  $created
     */
    private function applyOrder(Product $product, array $order, array $created): void
    {
        $position = 0;
        foreach ($order as $token) {
            $id = $token[0] === 'n' ? ($created[(int) substr($token, 1)]->id ?? null) : (int) substr($token, 1);
            if ($id && $product->images()->whereKey($id)->exists()) {
                $product->images()->whereKey($id)->update(['sort_order' => $position++]);
            }
        }
    }
}
