<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    protected $errorBag = 'transactionChat';
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
            // 本文
            'message' => [
                'required',
                'string',
                'max:400',
            ],

            // 画像（任意・png / jpeg のみ）
            'image' => [
                'nullable',
                'file',
                'mimes:jpeg,png',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // 本文
            'message.required' => '本文を入力してください',
            'message.max'      => '本文は400文字以内で入力してください',

            // 画像
            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}
