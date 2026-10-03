<?php
// app/Http/Requests/UpdateUrlRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $urlId = $this->route('url')?->id;

        return [
            'original_url' => ['sometimes', 'required', 'string', 'url', 'max:2048'],

            'custom_alias' => [
                'sometimes',
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('urls', 'custom_alias')->ignore($urlId),
                Rule::notIn(['api', 'admin', 'login', 'register', 'dashboard', 'docs']),
            ],

            'expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],

            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'original_url' => 'URL',
            'custom_alias' => 'custom alias',
            'expires_at'   => 'expiration date',
            'is_active'    => 'active flag',
        ];
    }

    public function messages(): array
    {
        return [
            'original_url.url'    => 'The :attribute must be a valid URL (e.g. https://example.com).',
            'custom_alias.regex'  => 'The :attribute may only contain letters, numbers, hyphens, and underscores.',
            'custom_alias.not_in' => 'The :attribute is reserved and cannot be used.',
            'expires_at.after'    => 'The :attribute must be in the future.',
        ];
    }
}