<?php

namespace App\Http\Requests\Admin\Tags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('content.manage');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => mb_strtolower(trim($this->input('name'))),
            ]);
        }
    }

    public function rules(): array
    {
        $tagId = $this->route('tag')->id;

        return [
            'name' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9\- ]+$/u', Rule::unique('tags', 'name')->ignore($tagId)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Tag mag alleen kleine letters, cijfers, spaties en streepjes bevatten.',
            'name.unique' => 'Deze tag bestaat al.',
        ];
    }
}
