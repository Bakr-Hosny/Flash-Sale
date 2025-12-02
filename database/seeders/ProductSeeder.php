<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Flash Sale Product - Smartphone X',
            'description' => 'Limited edition smartphone at flash sale price',
            'price' => 299.99,
            'stock' => 100,
            'original_stock' => 100,
            'is_flash_sale' => true,
        ]);

        $this->command->info('Flash sale product seeded successfully!');
        $this->command->info('Initial stock: 100');
    }
}
