<?php
/**
 * GroFresh Sample Data Population Script
 * 
 * This script populates the GroFresh Laravel backend with sample data from JSON files.
 * Usage: php populate_sample_data.php /path/to/sample_data.json
 * 
 * Author: AI Assistant
 * Date: 2025-07-11
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;

class GroFreshDataPopulator
{
    private $capsule;
    private $config;
    
    public function __construct()
    {
        $this->initializeDatabase();
        $this->loadConfig();
    }
    
    private function initializeDatabase()
    {
        $this->capsule = new Capsule;
        
        // Load database configuration from Laravel .env
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $env = $this->parseEnvFile($envFile);

            $host = $env['DB_HOST'] ?? 'mysql';
            // Use localhost if running outside Docker
            if ($host === 'mysql') {
                $host = 'localhost';
            }

            $this->capsule->addConnection([
                'driver' => 'mysql',
                'host' => $host,
                'database' => $env['DB_DATABASE'] ?? 'grofresh',
                'username' => $env['DB_USERNAME'] ?? 'grofresh_user',
                'password' => $env['DB_PASSWORD'] ?? 'grofresh_password',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ]);
        } else {
            // Fallback to Docker defaults (use localhost when running outside Docker)
            $this->capsule->addConnection([
                'driver' => 'mysql',
                'host' => 'localhost',
                'database' => 'grofresh',
                'username' => 'grofresh_user',
                'password' => 'grofresh_password',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ]);
        }
        
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }
    
    private function parseEnvFile($envFile)
    {
        $env = [];
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
                list($key, $value) = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }

        return $env;
    }

    private function loadConfig()
    {
        $this->config = [
            'default_category_id' => 1,
            'default_tax' => 0.00,
            'default_status' => 1,
            'default_unit' => 'kg',
            'default_stock' => 100,
            'image_placeholder' => '["placeholder.png"]'
        ];
    }
    
    public function populateFromJson($jsonFile)
    {
        if (!file_exists($jsonFile)) {
            throw new Exception("JSON file not found: $jsonFile");
        }
        
        $jsonContent = file_get_contents($jsonFile);
        $data = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON file: " . json_last_error_msg());
        }
        
        echo "Starting data population from: $jsonFile\n";
        echo "Found " . count($data['hits']) . " products to process\n\n";
        
        $this->createCategories($data['hits']);
        $this->createProducts($data['hits']);
        
        echo "\nData population completed successfully!\n";
    }
    
    private function createCategories($products)
    {
        echo "Creating categories...\n";
        
        $categories = [];
        $categoryMap = [];
        
        foreach ($products as $product) {
            if (isset($product['recursiveCategories'])) {
                foreach ($product['recursiveCategories'] as $categoryId) {
                    if (!in_array($categoryId, $categories)) {
                        $categories[] = $categoryId;
                    }
                }
            }
        }
        
        foreach ($categories as $categoryId) {
            $categoryName = $this->generateCategoryName($categoryId);
            
            try {
                $existingCategory = $this->capsule->table('categories')
                    ->where('id', $categoryId)
                    ->first();
                
                if (!$existingCategory) {
                    $this->capsule->table('categories')->insert([
                        'id' => $categoryId,
                        'name' => $categoryName,
                        'image' => 'category-placeholder.png',
                        'parent_id' => 0,
                        'position' => $categoryId,
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    echo "Created category: $categoryName (ID: $categoryId)\n";
                } else {
                    echo "Category already exists: $categoryName (ID: $categoryId)\n";
                }
            } catch (Exception $e) {
                echo "Error creating category $categoryId: " . $e->getMessage() . "\n";
            }
        }
    }
    
    private function createProducts($products)
    {
        echo "\nCreating products...\n";
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($products as $index => $product) {
            try {
                $productData = $this->mapProductData($product);
                
                $existingProduct = $this->capsule->table('products')
                    ->where('name', $productData['name'])
                    ->first();
                
                if (!$existingProduct) {
                    $this->capsule->table('products')->insert($productData);
                    $successCount++;
                    echo "Created product: " . $productData['name'] . "\n";
                } else {
                    echo "Product already exists: " . $productData['name'] . "\n";
                }
                
            } catch (Exception $e) {
                $errorCount++;
                echo "Error creating product " . ($product['name'] ?? 'Unknown') . ": " . $e->getMessage() . "\n";
            }
        }
        
        echo "\nProduct creation summary:\n";
        echo "Successfully created: $successCount products\n";
        echo "Errors encountered: $errorCount products\n";
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
        
        // Handle images
        $images = [];
        if (isset($product['picturesUrls']) && is_array($product['picturesUrls'])) {
            foreach ($product['picturesUrls'] as $imageUrl) {
                $imageName = $this->downloadImage($imageUrl, $product['slug'] ?? 'product');
                if ($imageName) {
                    $images[] = $imageName;
                }
            }
        }
        
        if (empty($images)) {
            $images = ['placeholder.png'];
        }
        
        return [
            'name' => $product['name'] ?? 'Unknown Product',
            'description' => $product['longDesc'] ?? $product['shortDesc'] ?? 'No description available',
            'image' => json_encode($images),
            'price' => (float)($product['price'] ?? 0),
            'variations' => '[]',
            'tax' => $this->config['default_tax'],
            'status' => $this->config['default_status'],
            'attributes' => '[]',
            'category_ids' => json_encode($categoryIds),
            'choice_options' => '[]',
            'discount' => 0.00,
            'discount_type' => 'percent',
            'tax_type' => 'percent',
            'unit' => $this->extractUnit($product['subText'] ?? ''),
            'total_stock' => $this->config['default_stock'],
            'capacity' => (float)($product['capacity']['weight'] ?? 1000) / 1000, // Convert to kg
            'daily_needs' => 0,
            'popularity_count' => 0,
            'is_featured' => 0,
            'view_count' => 0,
            'maximum_order_quantity' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ];
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
        
        return 'kg'; // Default
    }
    
    private function downloadImage($imageUrl, $productSlug)
    {
        try {
            // Create a context with user agent to avoid blocking
            $context = stream_context_create([
                'http' => [
                    'user_agent' => 'Mozilla/5.0 (compatible; GroFresh Data Populator)',
                    'timeout' => 10
                ]
            ]);

            $imageContent = file_get_contents($imageUrl, false, $context);
            if ($imageContent === false) {
                return null;
            }

            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension)) {
                $extension = 'jpg';
            }

            $imageName = date('Y-m-d-His') . '-' . uniqid() . '-' . $productSlug . '.' . $extension;
            $imagePath = __DIR__ . '/storage/app/public/product/' . $imageName;

            // Create directory if it doesn't exist
            $directory = dirname($imagePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if (file_put_contents($imagePath, $imageContent)) {
                echo "Downloaded image: $imageName\n";
                return $imageName;
            }

        } catch (Exception $e) {
            echo "Warning: Could not download image $imageUrl: " . $e->getMessage() . "\n";
        }

        return null;
    }

    public function clearExistingData($confirm = false)
    {
        if (!$confirm) {
            echo "WARNING: This will delete all existing products and categories!\n";
            echo "Run with --clear-data flag to confirm.\n";
            return;
        }

        echo "Clearing existing data...\n";

        try {
            $this->capsule->table('products')->truncate();
            $this->capsule->table('categories')->truncate();
            echo "Existing data cleared successfully.\n";
        } catch (Exception $e) {
            echo "Error clearing data: " . $e->getMessage() . "\n";
        }
    }

    public function validateDatabase()
    {
        try {
            $this->capsule->table('products')->count();
            $this->capsule->table('categories')->count();
            echo "Database connection successful.\n";
            return true;
        } catch (Exception $e) {
            echo "Database connection failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function generateCategoryName($categoryId)
    {
        $categoryNames = [
            1 => 'Grocery',
            2 => 'Fresh Produce',
            7 => 'Vegetables',
            12 => 'Fresh Vegetables',
            1484 => 'Fruits',
            1505 => 'Citrus Fruits',
            1506 => 'Seasonal Fruits'
        ];
        
        return $categoryNames[$categoryId] ?? "Category $categoryId";
    }
}

// Helper function for current timestamp
function now()
{
    return date('Y-m-d H:i:s');
}

// Main execution
function showUsage()
{
    echo "GroFresh Sample Data Population Script\n";
    echo "=====================================\n\n";
    echo "Usage: php populate_sample_data.php <json_file_path> [options]\n\n";
    echo "Arguments:\n";
    echo "  json_file_path    Path to the JSON file containing sample data\n\n";
    echo "Options:\n";
    echo "  --clear-data      Clear existing products and categories before importing\n";
    echo "  --validate-only   Only validate database connection, don't import data\n";
    echo "  --help           Show this help message\n\n";
    echo "Examples:\n";
    echo "  php populate_sample_data.php /path/to/sample_data.json\n";
    echo "  php populate_sample_data.php /path/to/sample_data.json --clear-data\n";
    echo "  php populate_sample_data.php --validate-only\n\n";
}

if ($argc < 2 || in_array('--help', $argv)) {
    showUsage();
    exit(0);
}

$jsonFile = null;
$clearData = false;
$validateOnly = false;

// Parse command line arguments
for ($i = 1; $i < $argc; $i++) {
    switch ($argv[$i]) {
        case '--clear-data':
            $clearData = true;
            break;
        case '--validate-only':
            $validateOnly = true;
            break;
        default:
            if ($jsonFile === null && !str_starts_with($argv[$i], '--')) {
                $jsonFile = $argv[$i];
            }
            break;
    }
}

try {
    $populator = new GroFreshDataPopulator();

    // Validate database connection
    if (!$populator->validateDatabase()) {
        exit(1);
    }

    if ($validateOnly) {
        echo "Database validation completed successfully.\n";
        exit(0);
    }

    if ($jsonFile === null) {
        echo "Error: JSON file path is required.\n\n";
        showUsage();
        exit(1);
    }

    // Clear existing data if requested
    if ($clearData) {
        $populator->clearExistingData(true);
    }

    // Populate data
    $populator->populateFromJson($jsonFile);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
