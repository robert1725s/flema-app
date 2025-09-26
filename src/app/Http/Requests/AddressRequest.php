<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * リクエストの認可を決定
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * バリデーションルールを取得
     *
     * @return array
     */
    public function rules()
    {
        return [
            'postal_code' => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'address' => ['required']
        ];
    }

    /**
     * バリデーションエラーメッセージをカスタマイズ
     *
     * @return array
     */
    public function messages()
    {
        return [
            'postal_code.required' => '郵便番号は必須です',
            'postal_code.regex' => '郵便番号はハイフンを含む8文字で入力してください',
            'address.required' => '住所は必須です',
        ];
    }
}
