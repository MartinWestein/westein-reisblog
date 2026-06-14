<?php

namespace App\Http\Requests\Admin\Posts;

class UpdatePostRequest extends PostRequest
{
    public function authorize(): bool
    {
        // Own/any-logica leeft in PostPolicy::update — één bron van waarheid
        return $this->user()->can('update', $this->route('post'));
    }

    /**
     * Slug is read-only bij update (beslissing #10): weggelaten uit rules(),
     * dus validated() retourneert 'm nooit → ook tamper-proof bij POST.
     *
     * @return array<string, mixed>
     */
    protected function slugRules(): array
    {
        return [];
    }
}
