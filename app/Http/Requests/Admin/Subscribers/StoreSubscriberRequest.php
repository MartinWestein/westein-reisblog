<?php

namespace App\Http\Requests\Admin\Subscribers;

use App\Models\Subscriber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Subscriber::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc',     // bewust geen 'dns' — bevestigingsmail-bounce vangt domeinfouten af
                'max:255',
                Rule::unique('subscribers', 'email'),
            ],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'name' => $this->filled('name') ? trim((string) $this->input('name')) : null,
        ]);
    }
}
