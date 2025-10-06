<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payment' => ['required'], // 支払い方法の選択必須
            'address' => ['required'],     // 配送先の選択必須
        ];
    }

    public function messages(): array
    {
        return [
            'payment.required' => '支払い方法を選択してください。',
            'address.required' => '配送先を選択してください。',
        ];
    }
}
