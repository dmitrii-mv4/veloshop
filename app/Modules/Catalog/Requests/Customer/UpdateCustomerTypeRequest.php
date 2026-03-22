<?php

namespace App\Modules\Catalog\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $typeId = $this->route('id');

        return [
            'title' => 'required|string|max:255|unique:catalog_customers_type,title,' . $typeId,
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название типа обязательно',
            'title.string' => 'Название должно быть строкой',
            'title.max' => 'Название не может превышать 255 символов',
            'title.unique' => 'Тип с таким названием уже существует',
            'is_active.boolean' => 'Флаг активности должен быть true или false',
        ];
    }
}