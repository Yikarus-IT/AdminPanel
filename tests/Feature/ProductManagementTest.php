<?php

namespace Tests\Feature;

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
            ->assertJsonCount(2, 'data');
    }

    public function test_product_can_be_created(): void
    {
        $response = $this->postJson('/products', [
            'name' => 'Desk Shelf',
            'category' => 'Furniture',
            'sku' => 'FUR-777S',
            'price' => 89.99,
            'stock' => 14,
            'status' => 'Healthy',
            'description' => 'Raised wood shelf for monitor setups.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Desk Shelf');

        $this->assertDatabaseHas('products', [
            'sku' => 'FUR-777S',
        ]);
    }

    public function test_product_creation_validates_required_fields(): void
    {
        $response = $this->postJson('/products', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'category',
                'sku',
                'price',
                'stock',
                'status',
            ]);
    }
}
