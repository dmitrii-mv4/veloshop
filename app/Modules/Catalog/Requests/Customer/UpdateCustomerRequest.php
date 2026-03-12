<?php

namespace App\Modules\Catalog\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('id'); // получаем ID из маршрута

        return [
            'user_id' => 'required|exists:users,id|unique:catalog_customers,user_id,' . $customerId,
            'type_id' => 'required|exists:catalog_customers_type,id',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Выберите пользователя',
            'user_id.exists' => 'Выбранный пользователь не существует',
            'user_id.unique' => 'Для этого пользователя уже создан профиль покупателя',
            'type_id.required' => 'Выберите тип покупателя',
            'type_id.exists' => 'Выбранный тип не существует',
        ];
    }
}