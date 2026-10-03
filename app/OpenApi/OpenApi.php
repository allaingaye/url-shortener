<?php
// app/OpenApi/OpenApi.php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'URL Shortener API',
    description: 'REST API for shortening URLs. Built with Laravel 12, SQLite, and Redis.',
    contact: new OA\Contact(name: 'Your Name', email: 'you@example.com'),
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: 'Local development',
)]
#[OA\Tag(
    name: 'Auth',
    description: 'Registration, login, and current user endpoints',
)]
#[OA\Tag(
    name: 'URLs',
    description: 'Create, list, view, and delete shortened URLs',
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Personal Access Token',
    description: 'Enter your Sanctum token (e.g. "1|abc..."). Swagger sends it as: Authorization: Bearer <token>',
)]
class OpenApi
{
    // Annotation-only class — no runtime logic.
}