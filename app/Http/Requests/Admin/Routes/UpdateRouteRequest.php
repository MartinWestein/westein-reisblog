<?php

namespace App\Http\Requests\Admin\Routes;

class UpdateRouteRequest extends RouteRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('route')) ?? false;
    }

    /**
     * Slug is locked bij update (Pages-patroon). Door 'm uit rules() te halen
     * komt 'ie niet in validated() — en wordt 'ie dus nooit gesaved, ook bij
     * POST-tampering. Tamper-proof zonder extra werk.
     */
    protected function slugRules(): array
    {
        return [];
    }
}
