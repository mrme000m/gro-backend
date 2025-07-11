<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting sample data population...');

        // Read JSON file
        $jsonPath = base_path('sample_sata.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("JSON file not found: $jsonPath");
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Invalid JSON file: " . json_last_error_msg());
            return;
        }

        $this->command->info('Found ' . count($data['hits']) . ' products to process');

        // Create categories
        $this->createCategories($data['hits']);

        // Create products
        $this->createProducts($data['hits']);

        $this->command->info('Sample data population completed!');
    }

    private function createCategories($products)
    {
        $this->command->info('Creating categories...');

        $categories = [];
        foreach ($products as $product) {
            if (isset($product['recursiveCategories'])) {
                foreach ($product['recursiveCategories'] as $categoryId) {
                    if (!in_array($categoryId, $categories)) {
                        $categories[] = $categoryId;
                    }
                }
            }
        }

        $categoryNames = [
            1 => 'Grocery',
            2 => 'Fresh Produce',
            7 => 'Vegetables',
            12 => 'Fresh Vegetables',
            1484 => 'Fruits',
            1505 => 'Citrus Fruits',
            1506 => 'Seasonal Fruits'
        ];

        foreach ($categories as $categoryId) {
            $categoryName = $categoryNames[$categoryId] ?? "Category $categoryId";

            $existing = DB::table('categories')->where('id', $categoryId)->first();

            if (!$existing) {
                DB::table('categories')->insert([
                    'id' => $categoryId,
                    'name' => $categoryName,
                    'image' => 'category-placeholder.png',
                    'parent_id' => 0,
                    'position' => $categoryId,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $this->command->line("Created category: $categoryName (ID: $categoryId)");
            }
        }
    }

    private function createProducts($products)
    {
        $this->command->info('Creating products...');

        $successCount = 0;
        $errorCount = 0;

        foreach ($products as $product) {
            try {
                $productData = $this->mapProductData($product);

                $existing = DB::table('products')->where('name', $productData['name'])->first();

                if (!$existing) {
                    DB::table('products')->insert($productData);
                    $successCount++;
                    $this->command->line("Created product: " . $productData['name']);
                } else {
                    $this->command->line("Product already exists: " . $productData['name']);
                }

            } catch (\Exception $e) {
                $errorCount++;
                $this->command->error("Error creating product " . ($product['name'] ?? 'Unknown') . ": " . $e->getMessage());
            }
        }

        $this->command->info("Successfully created: $successCount products");
        $this->command->info("Errors encountered: $errorCount products");
    }

    private function mapProductData($product)
    {
        // Extract category IDs
        $categoryIds = [];
        if (isset($product['categories'])) {
            foreach ($product['categories'] as $catId) {
                $categoryIds[] = ['id' => (string)$catId, 'position' => 1];
            }
        }

        // Download and process images
        $images = $this->downloadProductImages($product);

        return [
            'name' => $product['name'] ?? 'Unknown Product',
            'description' => $product['longDesc'] ?? $product['shortDesc'] ?? 'No description available',
            'image' => json_encode($images),
            'price' => (float)($product['price'] ?? 0),
            'variations' => '[]',
            'tax' => 0.00,
            'status' => 1,
            'attributes' => '[]',
            'category_ids' => json_encode($categoryIds),
            'choice_options' => '[]',
            'discount' => 0.00,
            'discount_type' => 'percent',
            'tax_type' => 'percent',
            'unit' => $this->extractUnit($product['subText'] ?? ''),
            'total_stock' => 100,
            'capacity' => (float)($product['capacity']['weight'] ?? 1000) / 1000,
            'daily_needs' => 0,
            'popularity_count' => 0,
            'is_featured' => 0,
            'view_count' => 0,
            'maximum_order_quantity' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    private function downloadProductImages($product)
    {
        $images = [];

        if (isset($product['picturesUrls']) && is_array($product['picturesUrls'])) {
            foreach ($product['picturesUrls'] as $index => $imageUrl) {
                $imageName = $this->downloadImage($imageUrl, $product['slug'] ?? 'product', $index);
                if ($imageName) {
                    $images[] = $imageName;
                }

                // Limit to 3 images per product to avoid too many downloads
                if (count($images) >= 3) {
                    break;
                }
            }
        }

        // If no images were downloaded, use placeholder
        if (empty($images)) {
            $images = ['placeholder.png'];
        }

        return $images;
    }

    private function downloadImage($imageUrl, $productSlug, $index = 0)
    {
        try {
            // Create a context with user agent to avoid blocking
            $context = stream_context_create([
                'http' => [
                    'user_agent' => 'Mozilla/5.0 (compatible; GroFresh Data Populator)',
                    'timeout' => 15,
                    'method' => 'GET',
                    'header' => [
                        'Accept: image/*',
                        'Accept-Language: en-US,en;q=0.9'
                    ]
                ]
            ]);

            $imageContent = file_get_contents($imageUrl, false, $context);
            if ($imageContent === false) {
                $this->command->warn("Failed to download image: $imageUrl");
                return null;
            }

            // Get file extension from URL or default to jpg
            $extension = 'jpg';
            $urlPath = parse_url($imageUrl, PHP_URL_PATH);
            if ($urlPath) {
                $pathExtension = pathinfo($urlPath, PATHINFO_EXTENSION);
                if (in_array(strtolower($pathExtension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $extension = strtolower($pathExtension);
                }
            }

            // Generate unique filename
            $imageName = date('Y-m-d-His') . '-' . uniqid() . '-' . $productSlug . '-' . $index . '.' . $extension;
            $imagePath = storage_path('app/public/product/' . $imageName);

            // Create directory if it doesn't exist
            $directory = dirname($imagePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if (file_put_contents($imagePath, $imageContent)) {
                $this->command->line("Downloaded image: $imageName");
                return $imageName;
            }

        } catch (\Exception $e) {
            $this->command->warn("Error downloading image $imageUrl: " . $e->getMessage());
        }

        return null;
    }

    private function extractUnit($subText)
    {
        if (empty($subText)) return 'kg';

        $subText = strtolower($subText);

        if (strpos($subText, 'kg') !== false) return 'kg';
        if (strpos($subText, 'gm') !== false || strpos($subText, 'gram') !== false) return 'gm';
        if (strpos($subText, 'pcs') !== false || strpos($subText, 'piece') !== false) return 'pcs';
        if (strpos($subText, 'bundle') !== false) return 'bundle';
        if (strpos($subText, 'liter') !== false || strpos($subText, 'ml') !== false) return 'liter';

        return 'kg';
    }
}
