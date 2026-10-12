<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The store's product categories, in storefront display order.
 * Safe to run on production and safe to re-run: categories are matched by slug,
 * so existing ones are updated (name and order) rather than duplicated.
 *
 * php artisan db:seed --class=CategorySeeder --force
 */
class CategorySeeder extends Seeder
{
    public const CATEGORIES = [
        'Laptops',
        'Desktops',
        'Monitors',
        'CCTV Cameras',
        'NVR & DVR',
        'Networking',
        'Storage Devices',
        'Memory',
        'Graphics Cards',
        'Printers',
        'Accessories',
    ];

    // Earlier names that should be renamed rather than kept alongside the new ones
    private const RENAMED = [
        'storage' => 'Storage Devices',
        'cctv' => 'CCTV Cameras',
    ];

    public function run(): void
    {
        foreach (self::RENAMED as $oldSlug => $newName) {
            if (! Category::where('slug', Str::slug($newName))->exists()) {
                Category::where('slug', $oldSlug)->update(['name' => $newName, 'slug' => Str::slug($newName)]);
            }
        }

        foreach (self::CATEGORIES as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'position' => $index + 1]
            );
        }

        $this->command?->info('Categories ready: '.count(self::CATEGORIES).'.');
    }
}
