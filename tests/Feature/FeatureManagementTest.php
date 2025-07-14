<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\FeatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeatureManagementTest extends TestCase
{
    protected $featureService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->featureService = app(FeatureService::class);
    }

    /** @test */
    public function it_can_check_if_a_feature_is_enabled()
    {
        // Test core features (should always be enabled)
        $this->assertTrue($this->featureService->isEnabled('core.dashboard'));
        $this->assertTrue($this->featureService->isEnabled('core.authentication'));
        $this->assertTrue($this->featureService->isEnabled('core.settings'));
    }

    /** @test */
    public function it_can_check_if_a_feature_category_is_enabled()
    {
        // Test that core category is enabled
        $this->assertTrue($this->featureService->isCategoryEnabled('core'));
        
        // Test other categories based on current config
        $features = $this->featureService->getAllFeatures();
        
        if (isset($features['orders']['enabled'])) {
            $this->assertEquals(
                $features['orders']['enabled'], 
                $this->featureService->isCategoryEnabled('orders')
            );
        }
    }

    /** @test */
    public function it_can_get_enabled_features_for_a_category()
    {
        $enabledFeatures = $this->featureService->getEnabledFeatures('core');
        
        // Core features should include dashboard, authentication, settings
        $this->assertContains('dashboard', $enabledFeatures);
        $this->assertContains('authentication', $enabledFeatures);
        $this->assertContains('settings', $enabledFeatures);
    }

    /** @test */
    public function helper_functions_work_correctly()
    {
        // Test feature_enabled helper
        $this->assertTrue(feature_enabled('core.dashboard'));
        
        // Test feature_category_enabled helper
        $this->assertTrue(feature_category_enabled('core'));
        
        // Test get_enabled_features helper
        $enabledFeatures = get_enabled_features('core');
        $this->assertIsArray($enabledFeatures);
        $this->assertNotEmpty($enabledFeatures);
    }

    /** @test */
    public function it_returns_false_for_non_existent_features()
    {
        $this->assertFalse($this->featureService->isEnabled('non.existent.feature'));
        $this->assertFalse($this->featureService->isCategoryEnabled('non_existent_category'));
    }

    /** @test */
    public function it_can_get_navigation_items()
    {
        $navigation = $this->featureService->getNavigationItems();
        
        $this->assertIsArray($navigation);
        $this->assertNotEmpty($navigation);
        
        // Dashboard should always be present
        $dashboardFound = false;
        foreach ($navigation as $item) {
            if ($item['name'] === 'Dashboard') {
                $dashboardFound = true;
                $this->assertTrue($item['enabled']);
                break;
            }
        }
        $this->assertTrue($dashboardFound, 'Dashboard navigation item should be present');
    }

    /** @test */
    public function feature_validation_works_correctly()
    {
        // Test with valid features structure
        $validFeatures = [
            'core' => [
                'dashboard' => true,
                'authentication' => true,
                'settings' => true,
            ],
            'orders' => [
                'enabled' => true,
                'view_orders' => true,
            ]
        ];
        
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->featureService);
        $method = $reflection->getMethod('validateFeaturesStructure');
        $method->setAccessible(true);
        
        $this->assertTrue($method->invoke($this->featureService, $validFeatures));
        
        // Test with invalid features structure (missing core)
        $invalidFeatures = [
            'orders' => [
                'enabled' => true,
            ]
        ];
        
        $this->assertFalse($method->invoke($this->featureService, $invalidFeatures));
    }
}
