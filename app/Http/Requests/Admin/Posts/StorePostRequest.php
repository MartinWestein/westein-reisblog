<?php

namespace App\Http\Requests\Admin\Posts;

use App\Models\Post;
use Illuminate\Validation\Rule;

class StorePostRequest extends PostRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function slugRules(): array
    {
        return [
            'slug' => [
                'nullable',
                'string',
                'max:200',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('posts', 'slug'),
            ],
        ];
    }
}
