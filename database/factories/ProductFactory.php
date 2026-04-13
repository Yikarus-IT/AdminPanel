<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $stock = fake()->numberBetween(0, 60);

        return [
            'name' => fake()->randomElement([
                'Starter Kit',
                'Wireless Mouse',
                'Standing Desk',
                'Laptop Sleeve',
                'Office Lamp',
                'Mechanical Keyboard',
            ]),
            'category_id' => Category::factory(),
            'sku' => strtoupper(fake()->bothify('PRD-###??')),
            'price' => fake()->randomFloat(2, 19, 599),
            'stock' => $stock,
            'status' => $stock <= 8 ? 'Urgent' : ($stock <= 18 ? 'Low stock' : 'Healthy'),
            'description' => fake()->sentence(10),
        ];
    }
}
