<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'payment_method' => ['required'],
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
            'payment_method.required' => '支払い方法を選択してください。',
        ];
    }

    /**
     * バリデーション後の処理
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $item_id = $this->route('item_id');
            $user = auth()->user();

            // Sessionから配送先を取得
            $shippingAddress = session('shipping_address.' . $item_id, [
                'postal_code' => $user->postal_code,
                'address' => $user->address,
                'building' => $user->building,
            ]);

            // 配送先の必須チェック
            if (empty($shippingAddress['postal_code']) || empty($shippingAddress['address'])) {
                $validator->errors()->add('shipping_address', '配送先を設定してください。');
            }
        });
    }
}