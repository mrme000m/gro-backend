<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class FeatureController extends Controller
{
    protected $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Display the feature management interface
     */
    public function index()
    {
        $features = $this->featureService->getAllFeatures();

        return view('admin-views.settings.features', compact('features'));
    }

    /**
     * Update feature configuration
     */
    public function update(Request $request)
    {
        try {
            $features = $request->input('features', []);

            // Get current features configuration
            $currentFeatures = $this->featureService->getAllFeatures();

            // Update the features based on form input
            $updatedFeatures = $this->updateFeatureArray($currentFeatures, $features);

            // Save the updated configuration
            if ($this->featureService->updateFeatures($updatedFeatures)) {
                // Force clear all caches to ensure changes take effect immediately
                \Artisan::call('cache:clear');
                \Artisan::call('config:clear');

                Toastr::success('Features updated successfully!');
            } else {
                Toastr::error('Failed to update features. Please try again.');
            }

        } catch (\Exception $e) {
            Toastr::error('An error occurred: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Update feature array recursively
     */
    private function updateFeatureArray($current, $updates, $path = '')
    {
        foreach ($current as $key => $value) {
            $currentPath = $path ? $path . '.' . $key : $key;

            if (is_array($value)) {
                $current[$key] = $this->updateFeatureArray($value, $updates, $currentPath);
            } else {
                // Check if this feature was submitted in the form
                if (isset($updates[$currentPath])) {
                    $current[$key] = (bool) $updates[$currentPath];
                } else {
                    // If not in form (unchecked checkbox), set to false
                    // But keep core features always enabled
                    if (strpos($currentPath, 'core.') !== 0) {
                        $current[$key] = false;
                    }
                }
            }
        }

        return $current;
    }

    /**
     * Reset features to default
     */
    public function reset()
    {
        try {
            // Get default features from the original config
            $defaultFeatures = config('features');

            if ($this->featureService->updateFeatures($defaultFeatures)) {
                Toastr::success('Features reset to default successfully!');
            } else {
                Toastr::error('Failed to reset features. Please try again.');
            }

        } catch (\Exception $e) {
            Toastr::error('An error occurred: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Get feature status for AJAX requests
     */
    public function status(Request $request)
    {
        $feature = $request->input('feature');

        if (!$feature) {
            return response()->json(['error' => 'Feature parameter required'], 400);
        }

        $isEnabled = $this->featureService->isEnabled($feature);

        return response()->json([
            'feature' => $feature,
            'enabled' => $isEnabled
        ]);
    }

    /**
     * Toggle a single feature
     */
    public function toggle(Request $request)
    {
        try {
            $feature = $request->input('feature');
            $enabled = $request->input('enabled', false);

            if (!$feature) {
                return response()->json(['error' => 'Feature parameter required'], 400);
            }

            // Get current features
            $features = $this->featureService->getAllFeatures();

            // Update the specific feature
            $this->setNestedValue($features, $feature, (bool) $enabled);

            // Save the updated configuration
            if ($this->featureService->updateFeatures($features)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature updated successfully',
                    'feature' => $feature,
                    'enabled' => (bool) $enabled
                ]);
            } else {
                return response()->json(['error' => 'Failed to update feature'], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Set nested array value using dot notation
     */
    private function setNestedValue(&$array, $key, $value)
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }
}
