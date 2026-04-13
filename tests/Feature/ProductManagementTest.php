<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_endpoint_returns_records(): void
    {
        Product::factory()->count(2)->create();

        $response = $this->getJson('/products');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'category_id',
                        'category',
                        'sku',
                    ],
                ],
            ]);
    }

    public function test_product_can_be_created(): void
    {
        $category = Category::factory()->create([
            'name' => 'Furniture',
        ]);

        $response = $this->postJson('/products', [
            'name' => 'Desk Shelf',
            'category_id' => $category->id,
            'sku' => 'FUR-777S',
            'price' => 89.99,
            'stock' => 14,
            'status' => 'Healthy',
            'description' => 'Raised wood shelf for monitor setups.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Desk Shelf')
            ->assertJsonPath('data.category', 'Furniture');

        $this->assertDatabaseHas('products', [
            'sku' => 'FUR-777S',
            'category_id' => $category->id,
        ]);
    }

    public function test_product_creation_validates_required_fields(): void
    {
        $response = $this->postJson('/products', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'category_id',
                'sku',
                'price',
                'stock',
                'status',
            ]);
    }

    public function test_product_creation_requires_a_valid_category(): void
    {
        $response = $this->postJson('/products', [
            'name' => 'Desk Shelf',
            'category_id' => 999,
            'sku' => 'FUR-777S',
            'price' => 89.99,
            'stock' => 14,
            'status' => 'Healthy',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_product_is_soft_deleted_and_hidden_from_index(): void
    {
        $product = Product::factory()->create([
            'name' => 'Archive Me',
        ]);

        $deleteResponse = $this->deleteJson("/products/{$product->id}");

        $deleteResponse->assertOk();

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);

        $indexResponse = $this->getJson('/products');

        $indexResponse
            ->assertOk()
            ->assertJsonMissing([
                'id' => $product->id,
                'name' => 'Archive Me',
            ]);
    }

    public function test_deleted_products_can_be_requested_explicitly(): void
    {
        $product = Product::factory()->create([
            'name' => 'Archived Desk',
        ]);

        $product->delete();

        $response = $this->getJson('/products?view=deleted');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $product->id,
                'name' => 'Archived Desk',
            ]);
    }

    public function test_product_categories_endpoint_returns_sorted_categories(): void
    {
        Category::factory()->create(['name' => 'Furniture']);
        Category::factory()->create(['name' => 'Accessories']);

        $response = $this->getJson('/product-categories');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Accessories')
            ->assertJsonPath('data.1.name', 'Furniture');
    }
}
