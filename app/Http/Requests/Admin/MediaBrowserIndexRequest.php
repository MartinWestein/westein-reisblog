<?php

namespace App\Http\Requests\Admin;

use App\Services\Media\MediaQueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaBrowserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('media.browse') ?? false;
    }

    public function rules(): array
    {
        return [
            'collection' => ['nullable', 'string', Rule::in(MediaQueryBuilder::ALLOWED_COLLECTIONS)],
            'owner_type' => ['nullable', 'string', Rule::in(array_keys(config('westein.browsable_media_owners')))],
            'q' => 'nullable|string|max:100',
            'sort' => ['nullable', 'string', Rule::in(MediaQueryBuilder::ALLOWED_SORT_COLUMNS)],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function sortColumn(): string
    {
        return $this->input('sort', 'created_at');
    }

    public function sortDirection(): string
    {
        return $this->input('direction', 'desc');
    }
}
