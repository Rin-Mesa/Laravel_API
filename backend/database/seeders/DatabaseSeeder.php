<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'mesa@email.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Seed Categories
        $categories = [
            'Electronics' => 'Electronic devices and high-end tech stands.',
            'Peripherals' => 'Keyboards, mice, and desk accessories.',
            'Audio' => 'Studio-grade headphones and audio gear.',
            'Smart Home' => 'Sensors, lighting, and automated home tech.',
            'Wearables' => 'Smartwatches, fitness bands, and smart wearables.',
            'Components' => 'CPUs, GPUs, SSDs, and computer hardware.',
            'Accessories' => 'Cables, cleaning kits, and device cases.',
        ];

        $categoryModels = [];
        foreach ($categories as $name => $desc) {
            $categoryModels[$name] = Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $desc,
                'is_active' => true,
            ]);
        }

        // 3. Seed Products with Unsplash URLs
        $productsData = [
            [
                'category' => 'Electronics',
                'name' => 'Apex Precision Stand',
                'sku' => 'ST-APX-01',
                'price' => 129.00,
                'stock' => 42,
                'description' => 'Premium aluminum stand designed for high-performance laptops and professional setups. Features dual hinges and an ergonomic riser for optimal neck comfort.',
                'image' => 'https://images.unsplash.com/photo-1616440347437-b1c73416efc2?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Peripherals',
                'name' => 'Titanium Mechanical Keys',
                'sku' => 'KB-TIT-X2',
                'price' => 249.50,
                'stock' => 12,
                'description' => 'Ultra-responsive mechanical keyboard with customized linear switches, premium titanium keycaps, and custom RGB lighting.',
                'image' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Audio',
                'name' => 'Sonic Aura Elite',
                'sku' => 'HP-SNC-99',
                'price' => 399.00,
                'stock' => 88,
                'description' => 'Studio-grade wireless headphones featuring hybrid active noise cancelling and spatial audio technology with 40-hour battery life.',
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Smart Home',
                'name' => 'Lumina Air Sensor',
                'sku' => 'SH-LUM-01',
                'price' => 89.00,
                'stock' => 0, // Out of stock
                'description' => 'High-precision indoor air quality monitor tracking temperature, humidity, and volatile compounds with instant app notifications.',
                'image' => 'https://images.unsplash.com/photo-1558002038-1055907df827?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Wearables',
                'name' => 'Precision Chrono V2',
                'sku' => 'WA-CH-V2',
                'price' => 349.00,
                'stock' => 25,
                'description' => 'Elegant smartwatch with a precision titanium bezel, 44mm casing, and advanced health tracking features including blood oxygen monitoring.',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Audio',
                'name' => 'Acoustix Elite Headphones',
                'sku' => 'HP-AE-01',
                'price' => 199.00,
                'stock' => 30,
                'description' => 'Premium noise-cancelling headphones featuring studio-grade drivers and ergonomic comfort for prolonged listening sessions.',
                'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Accessories',
                'name' => 'VoltMax 20K Power Bank',
                'sku' => 'ACC-VM-20',
                'price' => 59.00,
                'stock' => 50,
                'description' => 'High-capacity 20,000mAh external battery pack with dual USB-C Power Delivery ports for ultra-fast charging.',
                'image' => 'https://images.unsplash.com/photo-1609592424109-dd9892f1b17c?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Audio',
                'name' => 'Precision Echo Pro',
                'sku' => 'HP-EP-02',
                'price' => 120.00,
                'stock' => 15,
                'description' => 'Wireless earbuds with deep bass, clear highs, active noise cancellation, and IPX7 water resistance.',
                'image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Peripherals',
                'name' => 'Precision Tactile K1',
                'sku' => 'KB-PT-K1',
                'price' => 80.50,
                'stock' => 40,
                'description' => 'Compact tenkeyless mechanical keyboard with customizable RGB backlighting and hot-swappable brown switches.',
                'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Wearables',
                'name' => 'Vantage Fit Ultra',
                'sku' => 'WA-VF-03',
                'price' => 249.00,
                'stock' => 18,
                'description' => 'Sleek and lightweight fitness tracker with continuous heart rate monitoring, built-in GPS, and 7-day battery life.',
                'image' => 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Components',
                'name' => 'Precision Core CPU',
                'sku' => 'CPU-PC-09',
                'price' => 699.99,
                'stock' => 10,
                'description' => 'Next-generation multi-core processor optimized for high-performance computing, heavy multitasking, and gaming.',
                'image' => 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Peripherals',
                'name' => 'Precision Mechanical K1',
                'sku' => 'KB-PM-K1',
                'price' => 189.00,
                'stock' => 20,
                'description' => 'Elite mechanical keyboard with custom-tuned tactile switches, premium aluminum construction, and dedicated macro keys.',
                'image' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Audio',
                'name' => 'Acoustic Pro Headphones',
                'sku' => 'HP-AP-03',
                'price' => 349.99,
                'stock' => 15,
                'description' => 'Professional studio monitor headphones with natural frequency response and superior isolation for mixing and editing.',
                'image' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Electronics',
                'name' => 'Vision Ultra 4K Display',
                'sku' => 'DIS-VU-4K',
                'price' => 599.00,
                'stock' => 8,
                'description' => '27-inch IPS panel with 144Hz refresh rate, HDR10 support, razor-thin bezels, and built-in USB hub.',
                'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Peripherals',
                'name' => 'Glide X Wireless Mouse',
                'sku' => 'MS-GX-01',
                'price' => 95.00,
                'stock' => 35,
                'description' => 'Ergonomic wireless mouse with 8,000 DPI sensor, silent click technology, and fast magnetic charging.',
                'image' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Components',
                'name' => 'SpeedVault 2TB SSD',
                'sku' => 'ST-SV-2TB',
                'price' => 210.00,
                'stock' => 22,
                'description' => 'Ultra-fast NVMe PCIe M.2 SSD with read speeds up to 7000MB/s for super-fast boot times and application loading.',
                'image' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Smart Home',
                'name' => 'Lumina Smart Desk Lamp',
                'sku' => 'SH-LL-02',
                'price' => 125.00,
                'stock' => 14,
                'description' => 'Premium LED desk lamp with adjustable color temperature, brightness slider, and built-in wireless charging pad.',
                'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?q=80&w=400&auto=format&fit=crop',
            ],
            // Recommended for you items
            [
                'category' => 'Accessories',
                'name' => 'V2 Rugged Case',
                'sku' => 'ACC-RC-V2',
                'price' => 29.00,
                'stock' => 100,
                'description' => 'Shock-resistant protective case designed for smartwatches and wearables with raised bezels for screen protection.',
                'image' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Accessories',
                'name' => 'Braided USB-C Cable',
                'sku' => 'ACC-BC-06',
                'price' => 15.00,
                'stock' => 200,
                'description' => 'Durable double-braided nylon USB-C to USB-C charging and high-speed data sync cable (6.6ft / 2m).',
                'image' => 'https://images.unsplash.com/photo-1541660721243-111f96404547?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Accessories',
                'name' => 'Screen Care Pro Kit',
                'sku' => 'ACC-SC-PRO',
                'price' => 24.00,
                'stock' => 75,
                'description' => 'Complete screen cleaning solution with microfiber cloth, alcohol-free cleaning spray, and dust blower.',
                'image' => 'https://images.unsplash.com/photo-1563453392212-326f5e854473?q=80&w=400&auto=format&fit=crop',
            ],
            [
                'category' => 'Accessories',
                'name' => 'Aero Charge Stand',
                'sku' => 'ACC-AC-01',
                'price' => 45.00,
                'stock' => 60,
                'description' => 'Fast wireless charging stand (15W) designed for Qi-enabled smartphones and accessories with portrait/landscape support.',
                'image' => 'https://images.unsplash.com/photo-1622445262465-2481c4574875?q=80&w=400&auto=format&fit=crop',
            ],
        ];

        $productModels = [];
        foreach ($productsData as $p) {
            $catModel = $categoryModels[$p['category']];
            $productModels[$p['sku']] = Product::create([
                'category_id' => $catModel->id,
                'name' => $p['name'],
                'slug' => Str::slug($p['name']),
                'sku' => $p['sku'],
                'price' => $p['price'],
                'stock' => $p['stock'],
                'description' => $p['description'],
                'image' => $p['image'],
                'is_active' => true,
            ]);
        }
    }
}
