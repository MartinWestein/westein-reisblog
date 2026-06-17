<?php

namespace App\Http\Requests\Admin\Routes;

use App\Models\Route;

class StoreRouteRequest extends RouteRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Route::class) ?? false;
    }

    protected function slugRules(): array
    {
        return [
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:255', 'unique:routes,slug'],
        ];
    }
}
