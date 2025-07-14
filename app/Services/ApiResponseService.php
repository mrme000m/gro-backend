<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ApiResponseService
{
    /**
     * Create a standardized success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @param array $meta
     * @return JsonResponse
     */
    public function success($data = null, string $message = 'Success', int $statusCode = 200, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode)
            ->header('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * Create a standardized error response
     *
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @param mixed $data
     * @return JsonResponse
     */
    public function error(string $message = 'Error', int $statusCode = 400, array $errors = [], $data = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode)
            ->header('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * Create a paginated response
     *
     * @param LengthAwarePaginator $paginator
     * @param string $message
     * @param array $additionalData
     * @return JsonResponse
     */
    public function paginated(LengthAwarePaginator $paginator, string $message = 'Success', array $additionalData = []): JsonResponse
    {
        $data = array_merge([
            'items' => $paginator->items(),
        ], $additionalData);

        $meta = [
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ]
        ];

        return $this->success($data, $message, 200, $meta);
    }

    /**
     * Create a collection response with custom pagination
     *
     * @param Collection $collection
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @param string $message
     * @param array $additionalData
     * @return JsonResponse
     */
    public function collection(Collection $collection, int $total, int $perPage, int $currentPage, string $message = 'Success', array $additionalData = []): JsonResponse
    {
        $lastPage = ceil($total / $perPage);
        $from = ($currentPage - 1) * $perPage + 1;
        $to = min($currentPage * $perPage, $total);

        $data = array_merge([
            'items' => $collection->values(),
        ], $additionalData);

        $meta = [
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'from' => $from,
                'to' => $to,
                'has_more_pages' => $currentPage < $lastPage,
            ]
        ];

        return $this->success($data, $message, 200, $meta);
    }

    /**
     * Transform legacy pagination format to new format
     *
     * @param array $legacyData
     * @param string $message
     * @return JsonResponse
     */
    public function transformLegacyPagination(array $legacyData, string $message = 'Success'): JsonResponse
    {
        $items = $legacyData['products'] ?? $legacyData['items'] ?? $legacyData;
        $total = $legacyData['total_size'] ?? count($items);
        $perPage = $legacyData['limit'] ?? 10;
        $currentPage = $legacyData['offset'] ?? 1;

        if (is_array($items)) {
            $items = collect($items);
        }

        return $this->collection($items, $total, $perPage, $currentPage, $message);
    }

    /**
     * Add compression headers for large responses
     *
     * @param JsonResponse $response
     * @param bool $enableCompression
     * @return JsonResponse
     */
    public function withCompression(JsonResponse $response, bool $enableCompression = true): JsonResponse
    {
        if ($enableCompression) {
            $response->header('Content-Encoding', 'gzip');
            $response->header('Vary', 'Accept-Encoding');
        }

        return $response;
    }

    /**
     * Add caching headers
     *
     * @param JsonResponse $response
     * @param int $maxAge
     * @param bool $public
     * @return JsonResponse
     */
    public function withCacheHeaders(JsonResponse $response, int $maxAge = 300, bool $public = true): JsonResponse
    {
        $cacheControl = $public ? 'public' : 'private';
        $cacheControl .= ", max-age={$maxAge}";

        return $response->header('Cache-Control', $cacheControl)
                       ->header('Expires', gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
    }

    /**
     * Add performance headers
     *
     * @param JsonResponse $response
     * @param float $executionTime
     * @param int $queryCount
     * @return JsonResponse
     */
    public function withPerformanceHeaders(JsonResponse $response, float $executionTime = null, int $queryCount = null): JsonResponse
    {
        if ($executionTime !== null) {
            $response->header('X-Response-Time', round($executionTime * 1000, 2) . 'ms');
        }

        if ($queryCount !== null) {
            $response->header('X-Query-Count', $queryCount);
        }

        return $response;
    }

    /**
     * Optimize response data by removing null values and empty arrays
     *
     * @param mixed $data
     * @return mixed
     */
    public function optimizeData($data)
    {
        if (is_array($data)) {
            $optimized = [];
            foreach ($data as $key => $value) {
                $optimizedValue = $this->optimizeData($value);
                if ($optimizedValue !== null && $optimizedValue !== [] && $optimizedValue !== '') {
                    $optimized[$key] = $optimizedValue;
                }
            }
            return $optimized;
        }

        if (is_object($data)) {
            $array = json_decode(json_encode($data), true);
            return $this->optimizeData($array);
        }

        return $data;
    }

    /**
     * Create a minimal response for mobile apps
     *
     * @param mixed $data
     * @param array $fields
     * @param string $message
     * @return JsonResponse
     */
    public function minimal($data, array $fields = [], string $message = 'Success'): JsonResponse
    {
        if (!empty($fields) && is_array($data)) {
            $data = $this->selectFields($data, $fields);
        }

        $optimizedData = $this->optimizeData($data);

        return $this->success($optimizedData, $message);
    }

    /**
     * Select specific fields from data
     *
     * @param array $data
     * @param array $fields
     * @return array
     */
    private function selectFields(array $data, array $fields): array
    {
        if (isset($data[0]) && is_array($data[0])) {
            // Array of items
            return array_map(function ($item) use ($fields) {
                return array_intersect_key($item, array_flip($fields));
            }, $data);
        } else {
            // Single item
            return array_intersect_key($data, array_flip($fields));
        }
    }

    /**
     * Log API response for monitoring
     *
     * @param Request $request
     * @param JsonResponse $response
     * @param float $executionTime
     * @return void
     */
    public function logResponse(Request $request, JsonResponse $response, float $executionTime): void
    {
        $responseSize = strlen($response->getContent());
        
        Log::info('API Response', [
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'response_size' => $responseSize,
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);
    }

    /**
     * Create a response with ETag for caching
     *
     * @param mixed $data
     * @param string $message
     * @param Request $request
     * @return JsonResponse
     */
    public function withETag($data, string $message, Request $request): JsonResponse
    {
        $etag = md5(json_encode($data));
        
        if ($request->header('If-None-Match') === $etag) {
            return response()->json(null, 304);
        }

        return $this->success($data, $message)
                   ->header('ETag', $etag);
    }
}
