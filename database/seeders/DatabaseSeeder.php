<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([AdminUserSeeder::class, CategorySeeder::class, BrandSeeder::class]);

        // Sample products are for local development only, never production
        if (app()->environment('local')) {
            $this->call(SampleCatalogSeeder::class);
        }
    }
}
