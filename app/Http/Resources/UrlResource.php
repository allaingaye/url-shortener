<?php
// app/Http/Resources/UrlResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a Url model for API output.
 *
 * @mixin \App\Models\Url
 */
class UrlResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'original_url'  => $this->original_url,
            'short_code'    => $this->short_code,       // always the system code
            'custom_alias'  => $this->custom_alias,     // null unless set
            'public_code'   => $this->public_code,      // what appears in the URL
            'short_url'     => $this->short_url,
            'clicks_count'  => (int) $this->clicks_count,
            'is_active'     => (bool) $this->is_active,
            'expires_at'    => $this->expires_at?->toIso8601String(),
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}