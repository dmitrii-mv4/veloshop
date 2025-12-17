<?php

namespace App\Modules\Page\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'content' => 'sometimes|string',
            'meta_slug' => 'sometimes|string',
            'meta_title' => 'sometimes|string',
            'meta_description' => 'sometimes|string',
            'meta_keys' => 'sometimes|string',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Это поле обязательно для заполнения!',
            'title.min' => 'Имя должно быть не менее :min символов',
            'title.max' => 'Имя должно быть не более :max символов',
        ];
    }
}