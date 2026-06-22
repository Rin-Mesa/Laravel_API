<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category = Category::first();

        if (!$category) {
            return;
        }

        Product::updateOrCreate(
            ['slug' => 'smartphone'],
            [
                'category_id' => $category->id,
                'name' => 'Smartphone',
                'description' => 'A modern smartphone with essential features.',
                'image' => 'https://example.com/images/smartphone.jpg',
                'price' => 499.99,
                'stock' => 50,
                'is_active' => true,
            ]
        );
    }
}
