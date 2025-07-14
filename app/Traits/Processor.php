<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Application;
use App\Models\AddonSetting;
use Illuminate\Support\Facades\Storage;
use App\Models\PaymentRequest;
use App\Services\ImageOptimizationService;

trait  Processor
{
    public function response_formatter($constant, $content = null, $errors = []): array
    {
        $constant = (array)$constant;
        $constant['content'] = $content;
        $constant['errors'] = $errors;
        return $constant;
    }

    public function error_processor($validator): array
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $errors[] = ['error_code' => $index, 'message' => self::translate($error[0])];
        }
        return $errors;
    }

    public function translate($key)
    {
        try {
            App::setLocale('en');
            $lang_array = include(base_path('resources/lang/' . 'en' . '/lang.php'));
            $processed_key = ucfirst(str_replace('_', ' ', str_ireplace(['\'', '"', ',', ';', '<', '>', '?'], ' ', $key)));
            if (!array_key_exists($key, $lang_array)) {
                $lang_array[$key] = $processed_key;
                $str = "<?php return " . var_export($lang_array, true) . ";";
                file_put_contents(base_path('resources/lang/' . 'en' . '/lang.php'), $str);
                $result = $processed_key;
            } else {
                $result = __('lang.' . $key);
            }
            return $result;
        } catch (\Exception $exception) {
            return $key;
        }
    }

    public function payment_config($key, $settings_type): object|null
    {
        try {
            $config = DB::table('addon_settings')->where('key_name', $key)
                ->where('settings_type', $settings_type)->first();
        } catch (Exception $exception) {
            return new AddonSetting();
        }

        return (isset($config)) ? $config : null;
    }

    public function file_uploader(string $dir, string $format, $image = null, $old_image = null)
    {
        if ($image == null) return $old_image ?? 'def.png';

        if (isset($old_image)) Storage::disk('public')->delete($dir . $old_image);

        $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        Storage::disk('public')->put($dir . $imageName, file_get_contents($image));

        return $imageName;
    }

    /**
     * Upload and optimize image with multiple sizes and WebP support
     *
     * @param string $dir
     * @param \Illuminate\Http\UploadedFile $image
     * @param array $old_images
     * @param array $options
     * @return array
     */
    public function optimized_file_uploader(string $dir, $image = null, array $old_images = [], array $options = []): array
    {
        if ($image == null) {
            return $old_images ?: ['medium' => 'def.png'];
        }

        try {
            $imageOptimizationService = app(ImageOptimizationService::class);

            // Delete old images if updating
            if (!empty($old_images)) {
                $imageOptimizationService->deleteFiles($dir, array_values($old_images));
            }

            // Upload and optimize new image
            $results = $imageOptimizationService->uploadAndOptimize($image, $dir, $options);

            return $results;

        } catch (\Exception $e) {
            // Fallback to original method if optimization fails
            \Log::warning('Image optimization failed, falling back to original upload', [
                'error' => $e->getMessage(),
                'directory' => $dir
            ]);

            $imageName = $this->file_uploader($dir, $image->getClientOriginalExtension(), $image, $old_images['medium'] ?? null);
            return ['medium' => $imageName];
        }
    }

    public function payment_response($payment_info, $payment_flag): Application|JsonResponse|Redirector|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $payment_info = PaymentRequest::find($payment_info->id);
        $token_string = 'payment_method=' . $payment_info->payment_method . '&&attribute_id=' . $payment_info->attribute_id . '&&transaction_reference=' . $payment_info->transaction_id;
        if (in_array($payment_info->payment_platform, ['web', 'app']) && $payment_info['external_redirect_link'] != null) {
            return redirect($payment_info['external_redirect_link'] . '?flag=' . $payment_flag . '&&token=' . base64_encode($token_string));
        }
        return redirect()->route('payment-' . $payment_flag, ['token' => base64_encode($token_string)]);
    }
}
