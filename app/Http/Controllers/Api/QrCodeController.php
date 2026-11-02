<?php

// app/Http/Controllers/Api/QrCodeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Url;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class QrCodeController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodeService,
    ) {}

    #[OA\Get(
        path: '/api/v1/urls/{id}/qr',
        operationId: 'urls.qr',
        tags: ['URLs'],
        summary: 'Download a QR code for a URL',
        description: 'Returns a PNG or SVG QR code that encodes the short URL. Owner-only.',
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
                name: 'format',
                in: 'query',
                required: false,
                description: 'Output format: png or svg (default: png)',
                schema: new OA\Schema(type: 'string', enum: ['png', 'svg'], example: 'png'),
            ),
            new OA\Parameter(
                name: 'size',
                in: 'query',
                required: false,
                description: 'Size in pixels (64-1024, PNG only; default: 300)',
                schema: new OA\Schema(type: 'integer', minimum: 64, maximum: 1024, example: 300),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'QR code image',
                content: new OA\MediaType(
                    mediaType: 'image/png',
                    schema: new OA\Schema(type: 'string', format: 'binary'),
                ),
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'URL not found'),
        ],
    )]
    public function __invoke(Request $request, Url $url): Response
    {
        // Reuse the existing policy for ownership
        Gate::forUser(auth('sanctum')->user())->authorize('view', $url);

        $format = $request->string('format', 'png')->toString();
        $size = (int) $request->input('size', 300);

        $image = $this->qrCodeService->generate($url, $format, $size);

        $mimeType = $format === 'svg' ? 'image/svg+xml' : 'image/png';
        $filename = "qr-{$url->public_code}.{$format}";

        return response($image, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
