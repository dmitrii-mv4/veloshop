<?php
/**
 * Валидация редактирования информационного блока.
 */
namespace App\Modules\IBlock\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IBlockEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Заголовок обязателен для заполнения.',
            'title.min' => 'Заголовок должен содержать не менее :min символов.',
            'title.max' => 'Заголовок не может быть длиннее :max символов.',
            'content.required' => 'Содержание обязательно для заполнения.',
        ];
    }
}