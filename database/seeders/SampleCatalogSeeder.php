<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\Support\ProductArt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 102 sample products across 11 categories, each with a 3-photo generated SVG gallery.
 * Prices and stock are illustrative only. Safe to re-run: rows are matched by name.
 *
 * php artisan db:seed --class=SampleCatalogSeeder
 */
class SampleCatalogSeeder extends Seeder
{
    private const IMAGE_DIR = 'products/sample';

    // Accent colours for the generated product images
    private const BRAND_COLORS = [
        'ASUS' => '#00539B', 'Acer' => '#83B81A', 'Dell' => '#007DB8', 'HP' => '#0096D6',
        'Lenovo' => '#E2231A', 'MSI' => '#E60012', 'Gigabyte' => '#F39200', 'Zotac' => '#F5A100',
        'Sapphire' => '#1D4ED8', 'Samsung' => '#1428A0', 'LG' => '#A50034', 'Kingston' => '#E4002B',
        'WD' => '#0067B4', 'Crucial' => '#0072CE', 'Lexar' => '#C8102E', 'Corsair' => '#E5B400',
        'Brother' => '#0D2C6C', 'Epson' => '#003399', 'Canon' => '#CC0000', 'UNV' => '#2682B6',
        'Hikvision' => '#E60012', 'Dahua' => '#D7000F', 'TP-Link' => '#16A3B4', 'Tenda' => '#F26522',
        'Mercusys' => '#00A3E0', 'Logitech' => '#00A9E0', 'Fantech' => '#E11D48', 'Redragon' => '#D7000F',
        'HyperX' => '#E31837', 'APC' => '#FF6600', 'Prolink' => '#1E40AF', 'Kstar' => '#0EA5E9',
        'Custom' => '#FF6B35',
    ];

    public function run(): void
    {
        // Categories and brands come from their own seeders, which are safe for production
        $this->call([CategorySeeder::class, BrandSeeder::class]);

        $catalog = $this->catalog();
        $brands = Brand::all()->keyBy('slug');

        // Insert in reverse so the first item listed ends up as the newest product
        $rows = [];
        foreach ($catalog as $categoryName => $items) {
            $category = Category::where('slug', Str::slug($categoryName))->firstOrFail();
            foreach ($items as $item) {
                $rows[] = [$category, ...$item];
            }
        }

        foreach (array_reverse($rows) as [$category, $name, $brand, $price, $qty, $label, $art, $specs]) {
            [$type, $variant] = array_pad(explode(':', $art), 2, 'dark');
            $accent = self::BRAND_COLORS[$brand] ?? '#0F52BA';

            // A small gallery per product: front, tinted backdrop, close-up
            $paths = [];
            foreach (ProductArt::VIEWS as $view) {
                $path = self::IMAGE_DIR.'/'.Str::slug($name).($view === 'main' ? '' : "-{$view}").'.svg';
                Storage::disk('public')->put($path, ProductArt::svg($type, $accent, strtoupper($brand), $variant, $view));
                $paths[] = $path;
            }

            $product = Product::updateOrCreate(
                ['name' => $name],
                [
                    'label' => $label,
                    'description' => $this->describe($name, $specs),
                    'price' => $price,
                    'quantity' => $qty,
                    'category_id' => $category->id,
                    'brand_id' => $brands[Str::slug($brand)]->id ?? null,
                ]
            );

            $product->images()->delete();
            foreach ($paths as $position => $path) {
                $product->images()->create(['path' => $path, 'sort_order' => $position]);
            }
            $product->syncCoverImage();
        }

        $this->command?->info('Seeded '.count($rows).' sample products in '.count($catalog).' categories.');
    }

    private function describe(string $name, array $specs): string
    {
        return $name."\n\n".implode("\n", array_map(fn ($s) => '• '.$s, $specs))
            ."\n\nGenuine product, available at our Lionschowk store.";
    }

    /**
     * [name, brand, price (NPR), quantity, label, art[:variant], specs]
     */
    private function catalog(): array
    {
        return [
            'Laptops' => [
                ['ASUS TUF Gaming F16 FX608JMR', 'ASUS', 189000, 8, 'New', 'laptop', ['Intel Core i7-14650HX', 'NVIDIA GeForce RTX 4060 8GB', '16GB DDR5 RAM, 512GB NVMe SSD', '16" FHD+ 165Hz display']],
                ['ASUS Vivobook 15 X1504VA', 'ASUS', 82000, 12, null, 'laptop:silver', ['Intel Core i5-1335U', '8GB RAM, 512GB SSD', '15.6" FHD display', 'Windows 11 Home']],
                ['ASUS ROG Strix G16 G614JV', 'ASUS', 215000, 3, 'Hot deal', 'laptop', ['Intel Core i7-13650HX', 'NVIDIA GeForce RTX 4060 8GB', '16GB DDR5, 1TB SSD', '16" QHD+ 240Hz display']],
                ['Acer Aspire 7 A715 RTX 3050', 'Acer', 115000, 5, 'Hot deal', 'laptop', ['Intel Core i5-12450H', 'NVIDIA GeForce RTX 3050 4GB', '16GB RAM, 512GB SSD', '15.6" FHD 144Hz display']],
                ['Acer Nitro V 15 ANV15-51', 'Acer', 142000, 7, 'Best seller', 'laptop', ['Intel Core i5-13420H', 'NVIDIA GeForce RTX 4050 6GB', '16GB DDR5, 512GB SSD', '15.6" FHD 144Hz display']],
                ['Acer Aspire Lite 15 Core i3', 'Acer', 58500, 15, null, 'laptop:silver', ['Intel Core i3-1215U', '8GB RAM, 512GB SSD', '15.6" FHD display', 'Metal top cover']],
                ['Dell Inspiron 15 3530 i5 13th Gen', 'Dell', 92000, 12, null, 'laptop:silver', ['Intel Core i5-1335U', '8GB RAM, 512GB SSD', '15.6" FHD 120Hz display', 'Windows 11 + Office']],
                ['Dell Vostro 3520 Core i3', 'Dell', 64500, 9, null, 'laptop:silver', ['Intel Core i3-1215U', '8GB RAM, 256GB SSD', '15.6" FHD display', 'Business-grade build']],
                ['Dell Latitude 3540 i5 13th Gen', 'Dell', 112000, 4, null, 'laptop', ['Intel Core i5-1335U', '16GB RAM, 512GB SSD', '15.6" FHD display', 'Backlit keyboard, fingerprint reader']],
                ['Dell G15 5530 RTX 4060', 'Dell', 198000, 2, null, 'laptop', ['Intel Core i7-13650HX', 'NVIDIA GeForce RTX 4060 8GB', '16GB DDR5, 1TB SSD', '15.6" FHD 165Hz display']],
                ['HP 15s Core i3 12th Gen', 'HP', 61500, 0, null, 'laptop:silver', ['Intel Core i3-1215U', '8GB RAM, 512GB SSD', '15.6" FHD display', 'Fast charge battery']],
                ['HP Victus 15 RTX 3050', 'HP', 125000, 6, 'Best seller', 'laptop', ['Intel Core i5-13420H', 'NVIDIA GeForce RTX 3050 6GB', '16GB RAM, 512GB SSD', '15.6" FHD 144Hz display']],
                ['HP ProBook 450 G10', 'HP', 118000, 5, null, 'laptop:silver', ['Intel Core i5-1335U', '16GB RAM, 512GB SSD', '15.6" FHD display', 'Business security features']],
                ['HP Pavilion x360 14 2-in-1', 'HP', 108000, 3, 'New', 'laptop:silver', ['Intel Core i5-1335U', '8GB RAM, 512GB SSD', '14" FHD touchscreen', '360° convertible design']],
                ['Lenovo IdeaPad Slim 3 Ryzen 5', 'Lenovo', 78500, 3, null, 'laptop:silver', ['AMD Ryzen 5 7520U', '8GB RAM, 512GB SSD', '15.6" FHD display', 'Thin and light, 1.6 kg']],
                ['Lenovo ThinkPad E14 Gen 5', 'Lenovo', 118000, 9, null, 'laptop', ['Intel Core i5-1335U', '16GB RAM, 512GB SSD', '14" WUXGA display', 'Spill-resistant keyboard']],
                ['Lenovo LOQ 15IRH8 RTX 4050', 'Lenovo', 138000, 6, 'New', 'laptop', ['Intel Core i5-12450H', 'NVIDIA GeForce RTX 4050 6GB', '16GB RAM, 512GB SSD', '15.6" FHD 144Hz display']],
                ['Lenovo Legion 5 Pro RTX 4070', 'Lenovo', 265000, 1, null, 'laptop', ['AMD Ryzen 7 7745HX', 'NVIDIA GeForce RTX 4070 8GB', '16GB DDR5, 1TB SSD', '16" WQXGA 240Hz display']],
                ['MSI Thin GF63 RTX 2050', 'MSI', 98000, 8, 'Hot deal', 'laptop', ['Intel Core i5-12450H', 'NVIDIA GeForce RTX 2050 4GB', '8GB RAM, 512GB SSD', '15.6" FHD 144Hz display']],
                ['MSI Modern 14 C12M', 'MSI', 76000, 0, null, 'laptop:silver', ['Intel Core i5-1235U', '8GB RAM, 512GB SSD', '14" FHD display', 'Lightweight 1.4 kg']],
            ],
            'Desktops' => [
                ['Custom Gaming PC Ryzen 5 7600 + RTX 4060', 'Custom', 165000, 2, 'Best seller', 'desktop', ['AMD Ryzen 5 7600', 'NVIDIA GeForce RTX 4060 8GB', '16GB DDR5, 1TB NVMe SSD', '650W 80+ Bronze PSU, RGB case']],
                ['Custom Gaming PC i5-13400F + RTX 4060 Ti', 'Custom', 182000, 3, 'New', 'desktop', ['Intel Core i5-13400F', 'NVIDIA GeForce RTX 4060 Ti 8GB', '16GB DDR5, 1TB NVMe SSD', '750W 80+ Gold PSU']],
                ['Custom Creator PC i7-14700K + RTX 4070 Super', 'Custom', 298000, 1, null, 'desktop', ['Intel Core i7-14700K', 'NVIDIA GeForce RTX 4070 Super 12GB', '32GB DDR5, 2TB NVMe SSD', '360mm liquid cooler']],
                ['Budget Gaming PC Ryzen 5 5600 + RX 6600', 'Custom', 105000, 4, 'Hot deal', 'desktop', ['AMD Ryzen 5 5600', 'AMD Radeon RX 6600 8GB', '16GB DDR4, 512GB NVMe SSD', '550W PSU']],
                ['Office Desktop Core i3-12100', 'Custom', 52000, 10, null, 'desktop', ['Intel Core i3-12100', '8GB DDR4, 256GB SSD', 'Compact mATX case', 'Wi-Fi adapter included']],
                ['Office Desktop i5-12400 + 22" Monitor', 'Custom', 72000, 6, null, 'desktop', ['Intel Core i5-12400', '8GB DDR4, 512GB SSD', '22" FHD monitor', 'Keyboard and mouse included']],
                ['Dell OptiPlex 7010 SFF', 'Dell', 98000, 3, null, 'desktop', ['Intel Core i5-13500', '8GB RAM, 512GB SSD', 'Small form factor', 'Windows 11 Pro']],
                ['HP Pro Tower 280 G9', 'HP', 89000, 0, null, 'desktop', ['Intel Core i5-12400', '8GB RAM, 512GB SSD', 'Tower chassis', 'Windows 11 Pro']],
            ],
            'Monitors' => [
                ['Samsung 24" S3 LS24C310 IPS', 'Samsung', 16500, 14, null, 'monitor', ['24" FHD IPS panel', '75Hz refresh rate', 'Borderless design', 'HDMI + D-Sub']],
                ['Samsung Odyssey G3 27" 165Hz', 'Samsung', 32500, 5, 'Hot deal', 'monitor', ['27" FHD VA panel', '165Hz, 1ms', 'AMD FreeSync Premium', 'Height-adjustable stand']],
                ['LG 24MP400 24" IPS', 'LG', 17800, 9, null, 'monitor', ['24" FHD IPS panel', '75Hz with FreeSync', 'Reader mode, flicker safe', 'HDMI + D-Sub']],
                ['LG UltraGear 27GS65F 27" 180Hz', 'LG', 36500, 4, 'New', 'monitor', ['27" FHD IPS panel', '180Hz, 1ms GtG', 'HDR10', 'G-Sync compatible']],
                ['Dell P2422H 24" IPS', 'Dell', 31000, 6, null, 'monitor', ['24" FHD IPS panel', 'ComfortView Plus', 'Height, tilt, swivel, pivot', 'HDMI, DP, USB hub']],
                ['Dell S2721DGF 27" QHD 165Hz', 'Dell', 58000, 2, null, 'monitor', ['27" QHD Fast IPS', '165Hz, 1ms', 'HDR400', 'Height-adjustable stand']],
                ['ASUS VA24EHF 24" IPS 100Hz', 'ASUS', 17200, 11, 'Best seller', 'monitor', ['24" FHD IPS panel', '100Hz, Adaptive-Sync', 'Eye Care technology', 'Frameless design']],
                ['ASUS TUF VG249Q3A 24" 180Hz', 'ASUS', 24500, 7, null, 'monitor', ['24" FHD Fast IPS', '180Hz, 1ms', 'ELMB Sync', 'FreeSync Premium']],
                ['MSI PRO MP243X 24" 100Hz', 'MSI', 16800, 0, null, 'monitor', ['24" FHD IPS panel', '100Hz refresh rate', 'Anti-flicker, less blue light', 'Built-in speakers']],
                ['Acer Nitro VG240Y E 24" 100Hz', 'Acer', 16000, 8, null, 'monitor', ['24" FHD IPS panel', '100Hz, 1ms VRB', 'AMD FreeSync', 'Zero-frame design']],
            ],
            'Graphics Cards' => [
                ['MSI GeForce RTX 4060 Ventus 2X 8GB', 'MSI', 52000, 3, null, 'gpu', ['8GB GDDR6', 'DLSS 3 frame generation', 'Dual-fan cooling', '1x 8-pin power']],
                ['ASUS Dual GeForce RTX 4060 OC 8GB', 'ASUS', 54500, 4, null, 'gpu', ['8GB GDDR6', 'Axial-tech dual fans', '0dB fan stop', 'OC mode boost clock']],
                ['Gigabyte GeForce RTX 4060 Ti Eagle 8GB', 'Gigabyte', 66000, 2, 'New', 'gpu:triple', ['8GB GDDR6', 'Windforce triple-fan cooling', 'DLSS 3', 'Metal back plate']],
                ['Zotac Gaming RTX 3050 Twin Edge 6GB', 'Zotac', 32000, 6, 'Hot deal', 'gpu', ['6GB GDDR6', 'Compact dual-fan design', 'No extra power connector', 'Ideal for 1080p gaming']],
                ['MSI GeForce RTX 4070 Super Ventus 3X 12GB', 'MSI', 105000, 1, null, 'gpu:triple', ['12GB GDDR6X', 'Triple-fan TORX cooling', 'DLSS 3', '1440p high-refresh gaming']],
                ['ASUS TUF Gaming RTX 4070 Ti Super 16GB', 'ASUS', 142000, 0, null, 'gpu:triple', ['16GB GDDR6X', 'Military-grade components', 'Triple axial-tech fans', '4K-ready performance']],
                ['Gigabyte Radeon RX 7600 Gaming OC 8GB', 'Gigabyte', 46000, 3, null, 'gpu:triple', ['8GB GDDR6', 'AMD RDNA 3', 'Windforce cooling', 'FSR 3 support']],
                ['Sapphire Pulse Radeon RX 6600 8GB', 'Sapphire', 36500, 5, null, 'gpu', ['8GB GDDR6', 'Dual-X cooling', 'Low power draw', 'Great value 1080p card']],
            ],
            'Storage Devices' => [
                ['Kingston NV3 500GB NVMe SSD', 'Kingston', 6200, 25, null, 'ssd', ['500GB M.2 2280', 'PCIe 4.0 NVMe', 'Up to 5,000 MB/s read', '3-year warranty']],
                ['Kingston NV3 1TB NVMe SSD', 'Kingston', 11500, 30, 'Best seller', 'ssd', ['1TB M.2 2280', 'PCIe 4.0 NVMe', 'Up to 6,000 MB/s read', '3-year warranty']],
                ['Samsung 980 1TB NVMe SSD', 'Samsung', 13500, 12, null, 'ssd', ['1TB M.2 2280', 'PCIe 3.0 NVMe', 'Up to 3,500 MB/s read', '5-year warranty']],
                ['Samsung 990 EVO 2TB NVMe SSD', 'Samsung', 26500, 4, 'New', 'ssd', ['2TB M.2 2280', 'PCIe 4.0 x4 / 5.0 x2', 'Up to 5,000 MB/s read', '5-year warranty']],
                ['WD Blue SN580 1TB NVMe SSD', 'WD', 12000, 10, null, 'ssd', ['1TB M.2 2280', 'PCIe 4.0 NVMe', 'Up to 4,150 MB/s read', '5-year warranty']],
                ['WD Black SN770 1TB NVMe SSD', 'WD', 15500, 6, null, 'ssd', ['1TB M.2 2280', 'PCIe 4.0 NVMe', 'Up to 5,150 MB/s read', 'Built for gaming']],
                ['Crucial P3 Plus 1TB NVMe SSD', 'Crucial', 12800, 0, null, 'ssd', ['1TB M.2 2280', 'PCIe 4.0 NVMe', 'Up to 5,000 MB/s read', '5-year warranty']],
                ['Lexar NM620 512GB NVMe SSD', 'Lexar', 5800, 18, 'Hot deal', 'ssd', ['512GB M.2 2280', 'PCIe 3.0 NVMe', 'Up to 3,300 MB/s read', 'Budget upgrade pick']],
            ],
            'Memory' => [
                ['Kingston Fury Beast 8GB DDR4 3200', 'Kingston', 3800, 40, null, 'ram', ['8GB single module', 'DDR4 3200 MHz', 'Low-profile heat spreader', 'Intel XMP ready']],
                ['Kingston Fury Beast 16GB DDR5 5600', 'Kingston', 7600, 15, null, 'ram', ['16GB single module', 'DDR5 5600 MHz', 'Intel XMP 3.0 / AMD EXPO', 'Lifetime warranty']],
                ['Corsair Vengeance LPX 16GB (2x8) DDR4 3200', 'Corsair', 7200, 9, null, 'ram', ['16GB kit (2x8GB)', 'DDR4 3200 MHz', 'Low-profile aluminium heatsink', 'Lifetime warranty']],
                ['Corsair Vengeance RGB 32GB (2x16) DDR5 6000', 'Corsair', 18500, 3, 'New', 'ram', ['32GB kit (2x16GB)', 'DDR5 6000 MHz', 'Dynamic RGB lighting', 'Intel XMP 3.0']],
                ['Crucial 16GB DDR4 3200 Laptop RAM', 'Crucial', 5200, 20, null, 'ram', ['16GB SODIMM', 'DDR4 3200 MHz', 'For laptops and mini PCs', 'Lifetime warranty']],
            ],
            'Printers' => [
                ['Brother DCP-T420W Ink Tank Printer', 'Brother', 24500, 7, 'Best seller', 'printer:ink', ['Print, scan, copy', 'Wi-Fi printing', 'Up to 7,500 colour pages per ink set', 'Mobile app support']],
                ['Brother DCP-T520W Ink Tank Printer', 'Brother', 29500, 4, null, 'printer:ink', ['Print, scan, copy', 'Wi-Fi and Wi-Fi Direct', '150-sheet paper tray', 'Low cost per page']],
                ['Brother HL-L2321D Mono Laser Printer', 'Brother', 22000, 5, null, 'printer', ['Mono laser printing', 'Automatic duplex', 'Up to 30 pages per minute', 'USB connectivity']],
                ['Brother DCP-L2541DW Laser Multifunction', 'Brother', 36000, 2, null, 'printer', ['Print, scan, copy', 'Wi-Fi and network', 'Automatic duplex', '50-sheet document feeder']],
                ['Epson L3250 Wi-Fi Ink Tank Printer', 'Epson', 26800, 0, null, 'printer:ink', ['Print, scan, copy', 'Wi-Fi Direct', 'Borderless photo printing', 'Spill-free refilling']],
                ['Epson L3210 Ink Tank Printer', 'Epson', 22500, 8, 'Hot deal', 'printer:ink', ['Print, scan, copy', 'USB connectivity', 'Up to 4,500 colour pages', 'Compact design']],
                ['Epson L5290 Ink Tank Printer with ADF', 'Epson', 39500, 3, null, 'printer:ink', ['Print, scan, copy, fax', 'Wi-Fi and Ethernet', '30-sheet document feeder', 'Ideal for small offices']],
                ['Canon PIXMA G3010 Ink Tank Printer', 'Canon', 24000, 6, null, 'printer:ink', ['Print, scan, copy', 'Wi-Fi printing', 'Up to 7,000 colour pages', 'Canon PRINT app']],
                ['HP LaserJet M111w', 'HP', 21000, 2, null, 'printer', ['Mono laser printing', 'Wi-Fi and HP Smart app', 'Up to 20 pages per minute', 'Compact footprint']],
                ['HP Smart Tank 580 All-in-One', 'HP', 26000, 5, 'New', 'printer:ink', ['Print, scan, copy', 'Wi-Fi and Bluetooth', 'Up to 6,000 colour pages', 'Self-healing Wi-Fi']],
            ],
            'CCTV Cameras' => [
                ['UNV 2MP Bullet Camera IPC2122LB-SF28-A', 'UNV', 5200, 30, null, 'bullet', ['2MP Full HD IP camera', '2.8mm fixed lens', 'Up to 30m IR night vision', 'IP67 weatherproof']],
                ['UNV 4MP Bullet Camera IPC2124LE-ADF28KM', 'UNV', 6500, 25, 'Best seller', 'bullet', ['4MP IP camera', 'Built-in microphone', 'Smart IR up to 30m', 'IP67 weatherproof']],
                ['UNV 4MP ColorHunter Bullet Camera', 'UNV', 8900, 12, 'New', 'bullet', ['4MP IP camera', 'Full-colour night vision', 'F1.0 super aperture', 'IP67 weatherproof']],
                ['UNV 2MP Dome Camera IPC322LB-SF28-A', 'UNV', 4800, 22, null, 'dome', ['2MP Full HD IP camera', '2.8mm fixed lens', 'Up to 30m IR night vision', 'Indoor ceiling mount']],
                ['UNV 4MP Dome Camera IPC3614LE-ADF28K', 'UNV', 6200, 18, null, 'dome', ['4MP IP camera', 'Built-in microphone', 'Smart IR up to 30m', 'Vandal-resistant']],
                ['UNV 4MP Camera Kit (NVR + 4 Cameras)', 'UNV', 38000, 4, 'Hot deal', 'bullet', ['8-channel NVR', '4x 4MP IP cameras', '1TB surveillance HDD', 'Installation available']],
                ['Hikvision 2MP Dome Camera DS-2CE56D0T', 'Hikvision', 2600, 40, null, 'dome', ['2MP HD analog camera', '2.8mm fixed lens', 'Up to 20m IR', 'Indoor use']],
                ['Hikvision 2MP Bullet Camera DS-2CE16D0T', 'Hikvision', 2800, 35, null, 'bullet', ['2MP HD analog camera', '3.6mm fixed lens', 'Up to 20m IR', 'IP66 weatherproof']],
                ['Dahua 2MP Bullet Camera HAC-HFW1200', 'Dahua', 2700, 0, null, 'bullet', ['2MP HDCVI camera', '3.6mm fixed lens', 'Up to 30m IR', 'IP67 weatherproof']],
                ['Dahua 4MP Dome Camera IPC-HDW1430', 'Dahua', 5900, 6, null, 'dome', ['4MP IP camera', '2.8mm fixed lens', 'Up to 30m IR', 'PoE powered']],
            ],
            'NVR & DVR' => [
                ['UNV 8-Channel NVR NVR301-08S3', 'UNV', 9500, 8, null, 'nvr', ['8-channel network recorder', 'Up to 8MP recording', '1 SATA bay, up to 10TB', 'Mobile app viewing']],
                ['UNV 16-Channel NVR NVR302-16S', 'UNV', 18500, 4, 'New', 'nvr', ['16-channel network recorder', 'Up to 12MP recording', '2 SATA bays, up to 20TB', 'Smart search and playback']],
                ['Hikvision 4-Channel DVR DS-7104HGHI', 'Hikvision', 5600, 10, null, 'nvr', ['4-channel digital recorder', '1080p lite recording', '1 SATA bay', 'Hik-Connect app']],
                ['Dahua 8-Channel DVR XVR5108HS', 'Dahua', 8200, 0, null, 'nvr', ['8-channel penta-brid recorder', 'Supports HDCVI, AHD, TVI, IP', '1 SATA bay', 'DMSS mobile app']],
            ],
            'Networking' => [
                ['TP-Link Archer C6 AC1200 Router', 'TP-Link', 4200, 20, 'Best seller', 'router', ['Dual-band AC1200 Wi-Fi', '4 external antennas', 'Gigabit ports', 'MU-MIMO']],
                ['TP-Link Archer AX23 Wi-Fi 6 Router', 'TP-Link', 7200, 9, 'New', 'router', ['Wi-Fi 6 AX1800', 'Dual-band', 'Gigabit ports', 'OneMesh support']],
                ['TP-Link TL-SG1008D 8-Port Gigabit Switch', 'TP-Link', 2600, 15, null, 'nvr', ['8 Gigabit ports', 'Plug and play', 'Desktop design', 'Energy-efficient']],
                ['Tenda AC10 AC1200 Router', 'Tenda', 3600, 12, null, 'router', ['Dual-band AC1200 Wi-Fi', '4 antennas', 'Gigabit ports', 'Easy app setup']],
                ['Mercusys MR30G AC1200 Router', 'Mercusys', 2900, 0, null, 'router', ['Dual-band AC1200 Wi-Fi', 'Gigabit ports', '4 antennas', 'Budget-friendly']],
                ['TP-Link Deco X20 Mesh Wi-Fi (2-pack)', 'TP-Link', 18500, 3, null, 'router', ['Wi-Fi 6 whole-home mesh', 'Covers up to 4,000 sq ft', 'Gigabit ports on each unit', 'Deco app setup']],
            ],
            'Accessories' => [
                ['Logitech MK270 Wireless Keyboard & Mouse', 'Logitech', 3200, 40, null, 'keyboard', ['Wireless 2.4 GHz', 'Full-size keyboard', 'Long battery life', 'Plug-and-play receiver']],
                ['Logitech K120 USB Keyboard', 'Logitech', 1300, 50, null, 'keyboard', ['Full-size USB keyboard', 'Spill-resistant', 'Quiet keys', 'Plug and play']],
                ['Logitech M331 Silent Plus Mouse', 'Logitech', 1900, 35, null, 'mouse', ['Wireless 2.4 GHz', '90% less click noise', '24-month battery life', 'Right-handed design']],
                ['Logitech G102 Lightsync Gaming Mouse', 'Logitech', 2600, 22, 'Best seller', 'mouse', ['8,000 DPI sensor', 'RGB Lightsync', '6 programmable buttons', 'Wired USB']],
                ['Fantech MK853 Mechanical Keyboard', 'Fantech', 4500, 10, null, 'keyboard', ['Mechanical switches', 'RGB backlight', 'Anti-ghosting', 'Detachable cable']],
                ['Redragon K552 Kumara Mechanical Keyboard', 'Redragon', 4800, 0, null, 'keyboard', ['Tenkeyless layout', 'Red mechanical switches', 'Rainbow backlight', 'Metal top plate']],
                ['Redragon M612 Predator Gaming Mouse', 'Redragon', 2200, 14, null, 'mouse', ['8,000 DPI', 'RGB lighting', '11 programmable buttons', 'Ergonomic grip']],
                ['Logitech H390 USB Headset', 'Logitech', 3800, 9, null, 'headset', ['USB plug and play', 'Noise-cancelling mic', 'In-line controls', 'Padded ear cups']],
                ['HyperX Cloud Stinger 2 Headset', 'HyperX', 6500, 5, 'New', 'headset', ['50mm drivers', 'DTS Headphone:X spatial audio', 'Swivel-to-mute mic', 'Lightweight 275g']],
                ['Fantech HG11 Captain Gaming Headset', 'Fantech', 2900, 12, null, 'headset', ['7.1 virtual surround', 'RGB lighting', 'Flexible microphone', 'USB connection']],
                ['APC Back-UPS 650VA BX650', 'APC', 9500, 8, null, 'ups', ['650VA / 360W', 'Line-interactive AVR', '3 output sockets', 'Ideal for one PC']],
                ['Prolink 1200VA UPS PRO1201SFC', 'Prolink', 13500, 4, null, 'ups', ['1200VA / 720W', 'Automatic voltage regulation', 'LCD status display', 'Cold-start function']],
                ['Kstar 650VA Line-Interactive UPS', 'Kstar', 6200, 6, 'Hot deal', 'ups', ['650VA / 390W', 'AVR protection', 'LED indicators', 'Compact design']],
            ],
        ];
    }
}
