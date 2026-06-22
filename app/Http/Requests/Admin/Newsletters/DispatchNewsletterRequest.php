<?php

namespace App\Http\Requests\Admin\Newsletters;

use App\Models\Subscriber;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DispatchNewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('dispatch', $this->route('newsletter'));
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (Subscriber::active()->count() === 0) {
                $validator->errors()->add(
                    'recipients',
                    'Er zijn geen actieve abonnees om deze nieuwsbrief naar te versturen.'
                );
            }
        });
    }
}
