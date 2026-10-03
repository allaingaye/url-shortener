<?php
// app/Http/Controllers/Api/UrlController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUrlRequest;
use App\Http\Requests\UpdateUrlRequest;
use App\Http\Resources\UrlResource;
use App\Models\Url;
use App\Services\UrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class UrlController extends Controller
{
    public function __construct(
        private readonly UrlService $urlService,
    ) {}

    #[OA\Get(
        path: '/api/v1/urls',
        operationId: 'urls.index',
        tags: ['URLs'],
        summary: 'List the authenticated user\'s URLs',
        description: 'Returns a paginated, searchable, filterable, sortable list of URLs owned by the authenticated user.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search across original_url, short_code, and custom_alias',
                schema: new OA\Schema(type: 'string', example: 'github'),
            ),
            new OA\Parameter(
                name: 'active',
                in: 'query',
                required: false,
                description: 'Filter by is_active flag',
                schema: new OA\Schema(type: 'boolean', example: true),
            ),
            new OA\Parameter(
                name: 'expired',
                in: 'query',
                required: false,
                description: 'Filter by expiration: true = only expired, false = only unexpired',
                schema: new OA\Schema(type: 'boolean', example: false),
            ),
            new OA\Parameter(
                name: 'sort_by',
                in: 'query',
                required: false,
                description: 'Column to sort by',
                schema: new OA\Schema(type: 'string', enum: ['created_at', 'clicks_count', 'original_url'], example: 'created_at'),
            ),
            new OA\Parameter(
                name: 'sort_dir',
                in: 'query',
                required: false,
                description: 'Sort direction',
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], example: 'desc'),
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Items per page (1–100)',
                schema: new OA\Schema(type: 'integer', example: 15),
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number',
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of URLs',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Url'),
                        ),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Url::query()
            ->where('user_id', auth('sanctum')->id());

        // ── Search: matches original_url OR short_code OR custom_alias ──
        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('original_url',  'like', "%{$search}%")
                  ->orWhere('short_code',   'like', "%{$search}%")
                  ->orWhere('custom_alias', 'like', "%{$search}%");
            });
        }

        // ── Filter: is_active ──
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // ── Filter: expiration state ──
        if ($request->has('expired')) {
            if ($request->boolean('expired')) {
                // Only expired URLs
                $query->whereNotNull('expires_at')
                      ->where('expires_at', '<', now());
            } else {
                // Only unexpired (including no-expiry)
                $query->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                });
            }
        }

        // ── Sorting: whitelist columns to prevent SQL injection ──
        $allowedSorts = ['created_at', 'clicks_count', 'original_url'];
        $sortBy  = $request->string('sort_by')->toString();
        $sortBy  = in_array($sortBy, $allowedSorts, true) ? $sortBy : 'created_at';

        $sortDir = strtolower($request->string('sort_dir')->toString());
        $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

        $query->orderBy($sortBy, $sortDir);

        // ── Pagination: safe per_page (1–100) ──
        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        return UrlResource::collection($query->paginate($perPage));
    }

    #[OA\Post(
        path: '/api/v1/urls',
        operationId: 'urls.store',
        tags: ['URLs'],
        summary: 'Shorten a URL',
        description: 'Creates a new short URL. Guests allowed. Authenticated users get ownership.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['original_url'],
                properties: [
                    new OA\Property(
                        property: 'original_url',
                        type: 'string',
                        format: 'uri',
                        example: 'https://laravel.com/docs',
                        description: 'The URL to shorten',
                    ),
                    new OA\Property(
                        property: 'custom_alias',
                        type: 'string',
                        nullable: true,
                        example: 'laravel-docs',
                        description: 'Optional custom alias (3–50 chars)',
                    ),
                    new OA\Property(
                        property: 'expires_at',
                        type: 'string',
                        format: 'date-time',
                        nullable: true,
                        example: '2026-12-31T23:59:59Z',
                        description: 'Optional expiration (must be in the future)',
                    ),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'URL created successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Url'),
                    ],
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ],
                ),
            ),
        ],
    )]
    public function store(StoreUrlRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $url = $this->urlService->create(
            $request->validated(),
            $user?->id,
        );

        return (new UrlResource($url))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/v1/urls/{id}',
        operationId: 'urls.show',
        tags: ['URLs'],
        summary: 'Get a single URL',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'URL ID',
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'URL details',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Url'),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'URL not found'),
        ],
    )]
    public function show(Url $url): UrlResource
    {
        Gate::forUser(auth('sanctum')->user())->authorize('view', $url);

        return new UrlResource($url);
    }

    #[OA\Patch(
        path: '/api/v1/urls/{id}',
        operationId: 'urls.update',
        tags: ['URLs'],
        summary: 'Update a URL',
        description: 'Partial update. Only fields sent are changed.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'original_url', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'custom_alias', type: 'string', nullable: true),
                    new OA\Property(property: 'expires_at',   type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'is_active',    type: 'boolean', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'URL updated',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Url'),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function update(UpdateUrlRequest $request, Url $url): UrlResource
    {
        Gate::forUser(auth('sanctum')->user())->authorize('update', $url);

        // Remember the old public code so we can bust its cache
        // if the alias is being changed.
        $oldCode = $url->public_code;

        $url->update($request->validated());

        // Bust cache for the old code and the new one (if different).
        $this->urlService->forget($oldCode);
        if ($url->public_code !== $oldCode) {
            $this->urlService->forget($url->public_code);
        }

        return new UrlResource($url->fresh());
    }

    #[OA\Delete(
        path: '/api/v1/urls/{id}',
        operationId: 'urls.destroy',
        tags: ['URLs'],
        summary: 'Delete a URL',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'URL deleted',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'URL deleted successfully.'),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'URL not found'),
        ],
    )]
    public function destroy(Url $url): JsonResponse
    {
        Gate::forUser(auth('sanctum')->user())->authorize('delete', $url);

        $this->urlService->forget($url->public_code);
        $url->delete();

        return response()->json([
            'message' => 'URL deleted successfully.',
        ]);
    }
}