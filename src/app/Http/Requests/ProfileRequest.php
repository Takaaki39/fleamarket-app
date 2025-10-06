<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'icon_img' => ['nullable', 'image', 'mimes:jpeg,png'],  // jpeg/pngのみ
            'username' => ['required', 'string', 'max:20'],         // 必須・20文字以内
            'zipcode' => ['required', 'regex:/^\d{3}-\d{4}$/'],     // ハイフンありの8文字
            'address' => ['required', 'string'],                    // 必須
        ];
    }

    public function messages(): array
    {
        return [
            'icon_img.image' => 'プロフィール画像は画像ファイルを選択してください。',
            'icon_img.mimes' => 'プロフィール画像はjpegまたはpng形式でアップロードしてください。',

            'username.required' => 'お名前を入力してください。',
            'username.max' => 'お名前は20文字以内で入力してください。',

            'zipcode.required' => '郵便番号を入力してください。',
            'zipcode.regex' => '郵便番号はハイフンあり（例: 123-4567）で入力してください。',

            'address.required' => '住所を入力してください。',
        ];
    }
}
