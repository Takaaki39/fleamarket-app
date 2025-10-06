<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'name' => ['required', 'string'],                       // 商品名：必須
            'description' => ['required', 'string', 'max:255'],     // 商品説明：必須・255文字以内
            'img_url' => ['required', 'image', 'mimes:jpeg,png'],   // 商品画像：必須・jpeg/png
            'categories' => ['required'],                          // カテゴリー：必須
            'condition' => ['required'],                            // 状態：必須
            'price' => ['required', 'numeric', 'min:0'],            // 価格：必須・数値・0円以上
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '商品名を入力してください。',

            'description.required' => '商品説明を入力してください。',
            'description.max' => '商品説明は255文字以内で入力してください。',

            'img_url.required' => '商品画像をアップロードしてください。',
            'img_url.image' => '商品画像は画像ファイルを選択してください。',
            'img_url.mimes' => '商品画像はjpegまたはpng形式でアップロードしてください。',

            'categories.required' => '商品のカテゴリーを選択してください。',

            'condition.required' => '商品の状態を選択してください。',

            'price.required' => '商品価格を入力してください。',
            'price.numeric' => '商品価格は数値で入力してください。',
            'price.min' => '商品価格は0円以上で入力してください。',
        ];
    }
}
