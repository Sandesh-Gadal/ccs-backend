<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Basic product details
            $table->string('name');
            $table->string('label')->nullable(); // added label
            $table->text('description')->nullable();

            // Price
            $table->decimal('price', 10, 2)->default(0);

            // Image path
            $table->string('image')->nullable();

            // Category relation
            $table->unsignedBigInteger('category_id')->nullable();

            $table->timestamps();

            // Foreign key (optional but recommended)
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
