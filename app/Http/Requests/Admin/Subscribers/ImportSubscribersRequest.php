<?php

namespace App\Http\Requests\Admin\Subscribers;

use App\Models\Subscriber;
use Illuminate\Foundation\Http\FormRequest;

class ImportSubscribersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('import', Subscriber::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:2048', // 2 MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => __('Kies een CSV-bestand om te importeren.'),
            'file.mimes' => __('Het bestand moet een CSV zijn.'),
            'file.max' => __('Het bestand mag maximaal 2 MB zijn.'),
        ];
    }
}
