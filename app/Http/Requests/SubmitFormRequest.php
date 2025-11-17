<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'       => ['sometimes', 'string', 'max:255'],
            'email'      => ['sometimes', 'email', 'max:255'],
            'phone'      => ['sometimes', 'string', 'max:50'],
            'profession' => ['sometimes', 'nullable', 'string', 'max:255'],
            'region'     => ['sometimes', 'string', 'max:255'],
            'product'    => ['sometimes', 'string', 'max:50'],

            'address' => ['sometimes', 'email', 'max:255'],
        ];
    }

    public function authorize(): true
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'name.string'       => 'Поле "name" должно быть строкой.',
            'email.email'       => 'Поле "email" должно быть корректным email-адресом.',
            'phone.string'      => 'Поле "phone" должно быть строкой.',
            'profession.string' => 'Поле "profession" должно быть строкой.',
            'region.string'     => 'Поле "region" должно быть строкой.',
            'product.string'    => 'Поле "product" должно быть строкой.',
            'address.email'     => 'Поле "address" должно содержать корректный email.',
        ];
    }
}
