<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The brands the store sells, each with a logo. Uses the real logo from
 * database/seeders/brand-logos/{slug}.svg when present, otherwise a coloured wordmark.
 * Safe to run on production and safe to re-run: brands are matched by slug.
 * A logo uploaded through the admin panel is never overwritten.
 *
 * php artisan db:seed --class=BrandSeeder --force
 */
class BrandSeeder extends Seeder
{
    /** Brand name => brand colour (used for the generated wordmark when there's no logo file) */
    public const BRANDS = [
        'ASUS' => '#00539B', 'Acer' => '#83B81A', 'Dell' => '#007DB8', 'HP' => '#0096D6',
        'Lenovo' => '#E2231A', 'MSI' => '#E60012', 'Gigabyte' => '#F39200', 'Zotac' => '#F5A100',
        'Sapphire' => '#1D4ED8', 'Intel' => '#0071C5', 'NVIDIA' => '#76B900', 'Samsung' => '#1428A0',
        'LG' => '#A50034', 'Kingston' => '#E4002B', 'WD' => '#0067B4', 'Crucial' => '#0072CE',
        'Lexar' => '#C8102E', 'Corsair' => '#E5B400', 'Brother' => '#0D2C6C', 'Epson' => '#003399',
        'Canon' => '#CC0000', 'UNV' => '#2682B6', 'Hikvision' => '#E60012', 'Dahua' => '#D7000F',
        'TP-Link' => '#16A3B4', 'Tenda' => '#F26522', 'Mercusys' => '#00A3E0', 'Logitech' => '#00A9E0',
        'Fantech' => '#E11D48', 'Redragon' => '#D7000F', 'HyperX' => '#E31837', 'APC' => '#FF6600',
        'Prolink' => '#1E40AF', 'Kstar' => '#0EA5E9',
    ];

    private const LOGO_DIR = 'brands';

    public function run(): void
    {
        foreach (self::BRANDS as $name => $color) {
            $slug = Str::slug($name);
            $brand = Brand::firstOrNew(['slug' => $slug]);
            $brand->name = $name;

            // Keep a logo the shop uploaded in the admin panel; only fill in missing or seeded ones
            $seeded = $brand->logo === null || str_starts_with($brand->logo, self::LOGO_DIR.'/seeded/') || str_starts_with($brand->logo, self::LOGO_DIR.'/sample/');
            if ($seeded) {
                $path = self::LOGO_DIR."/seeded/{$slug}.svg";
                $file = __DIR__."/brand-logos/{$slug}.svg";
                Storage::disk('public')->put($path, is_file($file) ? file_get_contents($file) : self::wordmark($name, $color));
                $brand->logo = $path;
            }

            $brand->save();
        }

        $this->command?->info('Brands ready: '.count(self::BRANDS).'.');
    }

    public static function wordmark(string $name, string $color): string
    {
        $text = htmlspecialchars(strtoupper($name), ENT_XML1);
        $width = 30 + mb_strlen($name) * 30;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} 60"><text x="50%" y="44" text-anchor="middle" font-family="Arial Black, Arial, Helvetica, sans-serif" font-weight="900" font-size="40" letter-spacing="1" fill="{$color}">{$text}</text></svg>
SVG;
    }
}
