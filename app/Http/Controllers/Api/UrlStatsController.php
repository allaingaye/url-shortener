<?php

// app/Http/Controllers/Api/UrlStatsController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Url;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class UrlStatsController extends Controller
{
    #[OA\Get(
        path: '/api/v1/urls/{id}/stats',
        operationId: 'urls.stats',
        tags: ['Analytics'],
        summary: 'Get click analytics for a URL',
        description: 'Returns aggregated click data for the authenticated owner of the URL.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'URL ID',
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
            new OA\Parameter(
                name: 'days',
                in: 'query',
                required: false,
                description: 'Window for "by_day" aggregation (default 30)',
                schema: new OA\Schema(type: 'integer', example: 30),
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Stats returned'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'URL not found'),
        ],
    )]
    public function __invoke(Url $url): JsonResponse
    {
        // Only the owner can view stats
        $this->authorize('view', $url);

        return response()->json([
            'data' => [
                'url' => [
                    'id' => $url->id,
                    'short_code' => $url->public_code,
                    'short_url' => $url->short_url,
                    'original_url' => $url->original_url,
                ],

                'total_clicks' => $url->clicks()->count(),
                'unique_visitors' => $url->clicks()->distinct('ip_address')->count('ip_address'),

                'by_device' => $this->groupCount($url, 'device'),
                'by_browser' => $this->groupCount($url, 'browser'),
                'by_platform' => $this->groupCount($url, 'platform'),

                'top_referers' => $this->topReferers($url, 10),

                'by_day' => $this->byDay($url, 30),
            ],
        ]);
    }

    /**
     * Group clicks by a column and return [value => count] pairs.
     *
     * @return array<string, int>
     */
    private function groupCount(Url $url, string $column): array
    {
        return $url->clicks()
            ->select($column, DB::raw('count(*) as total'))
            ->whereNotNull($column)
            ->groupBy($column)
            ->orderByDesc('total')
            ->pluck('total', $column)
            ->all();
    }

    /**
     * Top referers (limited). Returns [referer => count] pairs.
     *
     * @return array<string, int>
     */
    private function topReferers(Url $url, int $limit): array
    {
        return $url->clicks()
            ->select('referer', DB::raw('count(*) as total'))
            ->whereNotNull('referer')
            ->where('referer', '!=', '')
            ->groupBy('referer')
            ->orderByDesc('total')
            ->limit($limit)
            ->pluck('total', 'referer')
            ->all();
    }

    /**
     * Daily click counts for the last N days.
     *
     * @return array<int, array{date: string, count: int}>
     */
    private function byDay(Url $url, int $days): array
    {
        $since = now()->subDays($days)->startOfDay();

        return $url->clicks()
            ->select(DB::raw('date(created_at) as date, count(*) as count'))
            ->where('created_at', '>=', $since)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }
}
