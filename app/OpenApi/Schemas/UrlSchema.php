<?php
// app/OpenApi/Schemas/UrlSchema.php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Url',
    type: 'object',
    title: 'Shortened URL',
    required: ['id', 'original_url', 'short_code', 'short_url'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'original_url', type: 'string', format: 'uri', example: 'https://laravel.com/docs'),
        new OA\Property(property: 'short_code', type: 'string', example: 'aB3xK9m'),
        new OA\Property(property: 'short_url', type: 'string', format: 'uri', example: 'http://localhost:8080/aB3xK9m'),
        new OA\Property(property: 'clicks_count', type: 'integer', example: 0),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
)]
class UrlSchema
{
    // Annotation-only class — no runtime logic.
}