<?php

namespace App\Http\Requests\Admin\Newsletters;

use Illuminate\Foundation\Http\FormRequest;

class SendTestNewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sendTest', $this->route('newsletter'));
    }

    public function rules(): array
    {
        return [];
    }
}
