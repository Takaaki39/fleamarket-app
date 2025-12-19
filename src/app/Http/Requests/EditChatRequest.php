<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class EditChatRequest extends FormRequest
{
    protected $errorBag = 'editChat';
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

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            redirect()->back()
                ->withErrors($validator, 'editChat') // ← editChat用
                ->withInput()
                ->with('edit_chat_id', request('chat_id')) // ← どのチャットか
        );
    }
}
