<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()->delete();

        $categories = Category::query()
            ->get()
            ->keyBy('name');

        Product::factory()->createMany([
            [
                'name' => 'Starter Kit',
                'category_id' => $categories['Bundles']->id,
                'sku' => 'KIT-100A',
                'price' => 129.00,
                'stock' => 48,
                'status' => 'Healthy',
                'description' => 'Onboarding bundle with the most requested workspace essentials.',
            ],
            [
                'name' => 'Wireless Mouse',
                'category_id' => $categories['Accessories']->id,
                'sku' => 'ACC-220M',
                'price' => 39.90,
                'stock' => 12,
                'status' => 'Low stock',
                'description' => 'Rechargeable mouse used in the most popular desk setups.',
            ],
            [
                'name' => 'Standing Desk',
                'category_id' => $categories['Furniture']->id,
                'sku' => 'FUR-410D',
                'price' => 499.00,
                'stock' => 6,
                'status' => 'Urgent',
                'description' => 'Best-selling adjustable desk with premium wood finish.',
            ],
            [
                'name' => 'Laptop Sleeve',
                'category_id' => $categories['Accessories']->id,
                'sku' => 'ACC-185S',
                'price' => 34.50,
                'stock' => 31,
                'status' => 'Healthy',
                'description' => 'Minimal protective sleeve for hybrid work teams.',
            ],
        ]);
    }
}
