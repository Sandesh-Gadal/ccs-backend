<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BrandController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/admin', function () {
    return view('admin');
})->middleware(['auth', 'verified'])->name('admin');

Route::get('/admin/{any?}', function () {
    return view('admin');
})->where('any', '.*')->middleware('auth');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public read-only endpoints (used by the storefront)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/facets', [ProductController::class, 'facets']);
Route::get('/products/overview', [ProductController::class, 'overview']);
Route::get('/products/{id}', [ProductController::class, 'show'])->whereNumber('id');
Route::get('/categories',[CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{id}', [BrandController::class, 'show']);

// Write endpoints (admin panel only)
Route::middleware('auth')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);   // use PUT for update
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    Route::post('/brands', [BrandController::class, 'store']);
    Route::put('/brands/{id}', [BrandController::class, 'update']);
    Route::delete('/brands/{id}', [BrandController::class, 'destroy']);
});

require __DIR__.'/auth.php';
