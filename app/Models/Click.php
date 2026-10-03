<?php

// app/Models/Click.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Click extends Model
{
    use HasFactory;

    protected $fillable = [
        'url_id',
        'ip_address',
        'user_agent',
        'referer',
        'device',
        'browser',
        'platform',
        'country',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The URL that was clicked.
     */
    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }
}
