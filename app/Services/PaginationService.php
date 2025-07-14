<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PaginationService
{
    /**
     * Default pagination settings
     */
    const DEFAULT_PER_PAGE = 15;
    const MAX_PER_PAGE = 100;
    const MIN_PER_PAGE = 5;

    /**
     * Create optimized pagination for API responses
     *
     * @param Builder $query
     * @param Request $request
     * @param array $options
     * @return LengthAwarePaginator
     */
    public function paginate(Builder $query, Request $request, array $options = []): LengthAwarePaginator
    {
        $perPage = $this->getPerPage($request, $options);
        $page = $this->getCurrentPage($request);
        
        // Optimize query for counting
        $total = $this->getOptimizedCount($query);
        
        // Apply pagination to query
        $items = $query->offset(($page - 1) * $perPage)
                      ->limit($perPage)
                      ->get();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Create cursor-based pagination for better performance on large datasets
     *
     * @param Builder $query
     * @param Request $request
     * @param string $cursorColumn
     * @param array $options
     * @return array
     */
    public function cursorPaginate(Builder $query, Request $request, string $cursorColumn = 'id', array $options = []): array
    {
        $perPage = $this->getPerPage($request, $options);
        $cursor = $request->get('cursor');
        $direction = $request->get('direction', 'next'); // 'next' or 'prev'

        if ($cursor) {
            if ($direction === 'next') {
                $query->where($cursorColumn, '>', $cursor);
            } else {
                $query->where($cursorColumn, '<', $cursor);
            }
        }

        // Get one extra item to determine if there are more pages
        $items = $query->orderBy($cursorColumn, $direction === 'next' ? 'asc' : 'desc')
                      ->limit($perPage + 1)
                      ->get();

        $hasMore = $items->count() > $perPage;
        
        if ($hasMore) {
            $items = $items->slice(0, $perPage);
        }

        $nextCursor = null;
        $prevCursor = null;

        if ($hasMore && $items->isNotEmpty()) {
            $nextCursor = $items->last()->{$cursorColumn};
        }

        if ($cursor && $items->isNotEmpty()) {
            $prevCursor = $items->first()->{$cursorColumn};
        }

        return [
            'data' => $items->values(),
            'meta' => [
                'per_page' => $perPage,
                'has_more' => $hasMore,
                'next_cursor' => $nextCursor,
                'prev_cursor' => $prevCursor,
            ]
        ];
    }

    /**
     * Transform legacy pagination format to modern format
     *
     * @param array $legacyData
     * @param Request $request
     * @return array
     */
    public function transformLegacyPagination(array $legacyData, Request $request): array
    {
        $items = $legacyData['products'] ?? $legacyData['items'] ?? [];
        $total = $legacyData['total_size'] ?? count($items);
        $perPage = $legacyData['limit'] ?? self::DEFAULT_PER_PAGE;
        $currentPage = $legacyData['offset'] ?? 1;

        $lastPage = ceil($total / $perPage);
        $from = ($currentPage - 1) * $perPage + 1;
        $to = min($currentPage * $perPage, $total);

        return [
            'data' => $items,
            'meta' => [
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                    'has_more_pages' => $currentPage < $lastPage,
                    'path' => $request->url(),
                ]
            ]
        ];
    }

    /**
     * Get optimized count for large tables
     *
     * @param Builder $query
     * @return int
     */
    protected function getOptimizedCount(Builder $query): int
    {
        try {
            // Clone query to avoid affecting the original
            $countQuery = clone $query;
            
            // Remove unnecessary clauses for counting
            $countQuery->getQuery()->orders = null;
            $countQuery->getQuery()->limit = null;
            $countQuery->getQuery()->offset = null;
            
            return $countQuery->count();
        } catch (\Exception $e) {
            Log::warning('Failed to get optimized count, falling back to regular count', [
                'error' => $e->getMessage()
            ]);
            
            return $query->count();
        }
    }

    /**
     * Get per page value from request with validation
     *
     * @param Request $request
     * @param array $options
     * @return int
     */
    protected function getPerPage(Request $request, array $options = []): int
    {
        $defaultPerPage = $options['default_per_page'] ?? self::DEFAULT_PER_PAGE;
        $maxPerPage = $options['max_per_page'] ?? self::MAX_PER_PAGE;
        $minPerPage = $options['min_per_page'] ?? self::MIN_PER_PAGE;

        $perPage = (int) $request->get('limit', $request->get('per_page', $defaultPerPage));

        // Validate per page value
        if ($perPage < $minPerPage) {
            $perPage = $minPerPage;
        } elseif ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        return $perPage;
    }

    /**
     * Get current page from request
     *
     * @param Request $request
     * @return int
     */
    protected function getCurrentPage(Request $request): int
    {
        $page = (int) $request->get('page', $request->get('offset', 1));
        
        return max(1, $page);
    }

    /**
     * Create simple pagination for collections
     *
     * @param Collection $collection
     * @param int $perPage
     * @param int $currentPage
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function paginateCollection(Collection $collection, int $perPage, int $currentPage, Request $request): LengthAwarePaginator
    {
        $total = $collection->count();
        $offset = ($currentPage - 1) * $perPage;
        $items = $collection->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Get pagination metadata
     *
     * @param LengthAwarePaginator $paginator
     * @return array
     */
    public function getPaginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more_pages' => $paginator->hasMorePages(),
            'path' => $paginator->path(),
            'first_page_url' => $paginator->url(1),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ];
    }

    /**
     * Create infinite scroll pagination
     *
     * @param Builder $query
     * @param Request $request
     * @param string $cursorColumn
     * @param array $options
     * @return array
     */
    public function infiniteScroll(Builder $query, Request $request, string $cursorColumn = 'id', array $options = []): array
    {
        $perPage = $this->getPerPage($request, $options);
        $lastId = $request->get('last_id');

        if ($lastId) {
            $query->where($cursorColumn, '<', $lastId);
        }

        $items = $query->orderBy($cursorColumn, 'desc')
                      ->limit($perPage + 1)
                      ->get();

        $hasMore = $items->count() > $perPage;
        
        if ($hasMore) {
            $items = $items->slice(0, $perPage);
        }

        $lastId = $items->isNotEmpty() ? $items->last()->{$cursorColumn} : null;

        return [
            'data' => $items->values(),
            'meta' => [
                'per_page' => $perPage,
                'has_more' => $hasMore,
                'last_id' => $lastId,
            ]
        ];
    }
}
