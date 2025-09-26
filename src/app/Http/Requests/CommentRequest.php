<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
            'comment' => ['required', 'max:255'],
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
            'comment.required' => 'コメントを入力してください',
            'comment.max' => 'コメントは255文字以内で入力してください',
        ];
    }
}
