<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    // Scope validatiefouten naar een eigen bag zodat ze bij de juiste form landen.
    protected $errorBag = 'comment';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        // Lege hidden parent_id -> null, zodat 'nullable' klopt en exists niet draait.
        if ($this->input('parent_id') === '') {
            $this->merge(['parent_id' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:2', 'max:2000'],
            // parent_id moet bij DEZELFDE post horen en zelf top-level zijn (1 niveau, F5-86).
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')->where(function ($query) {
                    $query->where('post_id', $this->route('post')->id)
                        ->whereNull('parent_id');
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Schrijf eerst een reactie.',
            'body.min' => 'Je reactie is wel erg kort.',
            'body.max' => 'Een reactie mag maximaal 2000 tekens bevatten.',
            'parent_id.exists' => 'Je kunt alleen reageren op een bestaande hoofdreactie bij dit verhaal.',
        ];
    }
}
