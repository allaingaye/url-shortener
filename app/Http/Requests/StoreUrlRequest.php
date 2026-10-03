<?php

// app/Http/Requests/StoreUrlRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation rules for creating a shortened URL.
 *
 * Extracted from the controller so rules can be tested in isolation,
 * reused by future endpoints, and documented in one place.
 */
class StoreUrlRequest extends FormRequest
{
    /**
     * Anyone (guest or authenticated) can shorten a URL.
     * Ownership rules are enforced by policies in Phase 2.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare input before validation.
     * Ensures `original_url` is trimmed so `" https://x.com "` passes.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('original_url')) {
            $this->merge([
                'original_url' => trim((string) $this->input('original_url')),
            ]);
        }

        if ($this->has('custom_alias')) {
            $this->merge([
                'custom_alias' => trim((string) $this->input('custom_alias')) ?: null,
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'original_url' => [
                'required',
                'string',
                'url',          // must be a valid URL
                'max:2048',     // cap length (fits our DB column)
            ],

            'custom_alias' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/',   // URL-safe chars only
                Rule::unique('urls', 'custom_alias'),
                // Reserve words that clash with app routes
                Rule::notIn(['api', 'admin', 'login', 'register', 'dashboard', 'docs']),
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after:now',
            ],
        ];
    }

    /**
     * Friendly field names in error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'original_url' => 'URL',
            'custom_alias' => 'custom alias',
            'expires_at' => 'expiration date',
        ];
    }

    /**
     * Custom messages for specific rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'original_url.url' => 'The :attribute must be a valid URL (e.g. https://example.com).',
            'custom_alias.regex' => 'The :attribute may only contain letters, numbers, hyphens, and underscores.',
            'custom_alias.not_in' => 'The :attribute is reserved and cannot be used.',
            'expires_at.after' => 'The :attribute must be in the future.',
        ];
    }
}
