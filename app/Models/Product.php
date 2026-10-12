<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'description',
        'price',
        'quantity',
        'image',
        'category_id',
        'brand_id',
    ];

    protected $with = ['category', 'brand', 'images'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Storefront filters. Pass $skip to leave one facet out, which is how the
     * filter counts are calculated (e.g. brand counts ignore the brand filter).
     *
     * @param  array{category?: ?string, brands?: string[], min?: ?float, max?: ?float, in_stock?: bool, q?: ?string, ids?: int[]}  $f
     */
    public function scopeFilter(Builder $query, array $f, ?string $skip = null): Builder
    {
        if ($skip !== 'category' && ! empty($f['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $f['category']));
        }

        if ($skip !== 'brand' && ! empty($f['brands'])) {
            $query->whereHas('brand', fn ($q) => $q->whereIn('slug', $f['brands']));
        }

        if ($skip !== 'price' && (isset($f['min']) || isset($f['max']))) {
            $query->where('price', '>', 0)
                ->when(isset($f['min']), fn ($q) => $q->where('price', '>=', $f['min']))
                ->when(isset($f['max']), fn ($q) => $q->where('price', '<=', $f['max']));
        }

        if ($skip !== 'stock' && ! empty($f['in_stock'])) {
            $query->where('quantity', '>', 0);
        }

        if (! empty($f['ids'])) {
            $query->whereIn('id', $f['ids']);
        }

        if (! empty($f['q'])) {
            $term = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $f['q']).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('label', 'like', $term)
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $term))
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', $term));
            });
        }

        return $query;
    }

    public function scopeSorted(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            // Products without a price go last in either direction
            'price-asc' => $query->orderByRaw('price <= 0')->orderBy('price')->orderByDesc('id'),
            'price-desc' => $query->orderByRaw('price <= 0')->orderByDesc('price')->orderByDesc('id'),
            'name' => $query->orderBy('name')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };
    }

    /**
     * Keep the `image` column pointing at the first gallery photo (the cover),
     * so anything reading a single image keeps working.
     */
    public function syncCoverImage(): void
    {
        $this->unsetRelation('images');
        $this->image = $this->images()->value('path');
        $this->save();
    }
}
