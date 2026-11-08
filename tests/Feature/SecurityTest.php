<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for the security audit of 2026-11-08.
 * Every signed-in user is an admin, so who can sign in and what guests can reach is the whole security model.
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test laptop',
            'price' => 100000,
            'quantity' => 3,
            'is_published' => true,
        ], $attributes));
    }

    public function test_public_registration_is_closed(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'password-123456',
            'password_confirmation' => 'password-123456',
        ])->assertStatus(404);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
    }

    public function test_guests_cannot_change_the_catalogue(): void
    {
        $product = $this->product();
        $category = Category::create(['name' => 'Laptops', 'slug' => 'laptops']);
        $brand = Brand::create(['name' => 'Dell', 'slug' => 'dell']);

        // Without a session these are rejected before reaching a controller
        // (the CSRF check is skipped in tests, so this checks the auth middleware itself)
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $requests = [
            ['post', '/products', ['name' => 'X', 'price' => 1, 'quantity' => 1]],
            ['put', "/products/{$product->id}", ['name' => 'X', 'price' => 1, 'quantity' => 1]],
            ['patch', "/products/{$product->id}/publish", ['is_published' => false]],
            ['delete', "/products/{$product->id}", []],
            ['post', '/categories', ['name' => 'X']],
            ['put', "/categories/{$category->id}", ['name' => 'X']],
            ['delete', "/categories/{$category->id}", []],
            ['post', '/brands', ['name' => 'X']],
            ['put', "/brands/{$brand->id}", ['name' => 'X']],
            ['delete', "/brands/{$brand->id}", []],
        ];

        foreach ($requests as [$method, $uri, $data]) {
            $this->{$method.'Json'}($uri, $data)->assertUnauthorized();
        }

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Test laptop', 'is_published' => true]);
        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseCount('brands', 1);
    }

    public function test_guests_cannot_open_the_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/products')->assertRedirect('/login');
    }

    public function test_drafts_are_hidden_from_the_public(): void
    {
        $draft = $this->product(['name' => 'Unreleased', 'is_published' => false]);

        $this->getJson("/products/{$draft->id}")->assertNotFound();
        $this->getJson('/products')->assertJsonMissing(['name' => 'Unreleased']);

        $this->actingAs(User::factory()->create())->getJson("/products/{$draft->id}")->assertOk();
    }

    public function test_svg_uploads_are_rejected(): void
    {
        $svg = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
        );

        $this->actingAs(User::factory()->create())
            ->postJson('/brands', ['name' => 'Evil', 'logo' => $svg])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('logo');
    }

    public function test_search_treats_sql_wildcards_as_text(): void
    {
        $this->product(['name' => 'Ordinary laptop']);

        // A bare % would match every product if it reached LIKE unescaped
        $this->getJson('/products?page=1&q=%25')->assertOk()->assertJsonPath('total', 0);
    }

    public function test_public_api_is_rate_limited(): void
    {
        $this->getJson('/products/facets')->assertOk()->assertHeader('X-RateLimit-Limit', '300');
    }
}
