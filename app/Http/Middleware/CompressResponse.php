<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CompressResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $minSize  Minimum response size to compress (in bytes)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $minSize = 1024)
    {
        $response = $next($request);

        // Only compress JSON responses
        if (!$response instanceof JsonResponse) {
            return $response;
        }

        // Check if client accepts gzip compression
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (!str_contains($acceptEncoding, 'gzip')) {
            return $response;
        }

        $content = $response->getContent();
        $contentLength = strlen($content);

        // Only compress if response is larger than minimum size
        if ($contentLength < $minSize) {
            return $response;
        }

        // Compress the content
        $compressedContent = gzencode($content, 6); // Compression level 6 (good balance)
        
        if ($compressedContent === false) {
            Log::warning('Failed to compress response', [
                'endpoint' => $request->path(),
                'original_size' => $contentLength
            ]);
            return $response;
        }

        $compressedLength = strlen($compressedContent);
        $compressionRatio = round((1 - $compressedLength / $contentLength) * 100, 2);

        // Set compressed content and headers
        $response->setContent($compressedContent);
        $response->header('Content-Encoding', 'gzip');
        $response->header('Content-Length', $compressedLength);
        $response->header('Vary', 'Accept-Encoding');
        $response->header('X-Compression-Ratio', $compressionRatio . '%');
        $response->header('X-Original-Size', $contentLength);
        $response->header('X-Compressed-Size', $compressedLength);

        Log::debug('Response compressed', [
            'endpoint' => $request->path(),
            'original_size' => $contentLength,
            'compressed_size' => $compressedLength,
            'compression_ratio' => $compressionRatio . '%'
        ]);

        return $response;
    }
}
