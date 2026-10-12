<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Display order on the storefront (lower first)
            $table->unsignedSmallInteger('position')->default(0)->after('slug');
        });

        // Keep the current order for existing categories
        DB::table('categories')->update(['position' => DB::raw('id')]);
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
