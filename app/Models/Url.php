<?php

// app/Models/Url.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Url extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'user_id',
        'original_url',
        'short_code',
        'custom_alias',
        'expires_at',
        'is_active',
        'clicks_count',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'clicks_count' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Query scopes
    // ─────────────────────────────────────────────────────────────

    /** Only active URLs. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Only URLs that haven't expired yet. */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /** Look up by either short_code or custom_alias. */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where(function (Builder $q) use ($code) {
            $q->where('short_code', $code)
                ->orWhere('custom_alias', $code);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────────────────────

    /**
     * The code exposed in the public short URL.
     * Prefers the custom alias when present.
     */
    public function getPublicCodeAttribute(): string
    {
        return $this->custom_alias ?? $this->short_code;
    }

    /**
     * Fully-qualified short URL (e.g. http://localhost:8080/abc123).
     */
    public function getShortUrlAttribute(): string
    {
        return url($this->public_code);
    }
}
