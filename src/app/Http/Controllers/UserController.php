<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Http\Requests\ProfileRequest;

class UserController extends Controller
{
    public function showNotice()
    {
        return view('auth.notice');
    }


    /**
     * マイページを表示する
     */
    public function showMypage(Request $request)
    {
        $userId = auth()->id();

        // 出品した商品を取得（seller_idが自分）
        $soldItems = Item::where('seller_id', $userId)->get();

        // 購入した商品を取得（purchaser_idが自分）
        $purchasedItems = Item::where('purchaser_id', $userId)->get();

        return view('mypage', compact('soldItems', 'purchasedItems'));
    }

    /**
     * プロフィール編集画面を表示する
     */
    public function showProfile()
    {
        return view('profile');
    }

    /**
     * プロフィール更新処理
     */
    public function updateProfile(ProfileRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $imagePath = $user->image_path; // 既存の画像パスを保持

        // プロフィール画像のアップロード処理
        if ($request->hasFile('profile_image')) {
            // 古い画像を削除
            if ($user->image_path) {
                Storage::disk('public')->delete($user->image_path);
            }

            // 新しい画像を保存
            $imagePath = $request->file('profile_image')->store('users', 'public');
        }

        // ユーザー情報を更新
        $user->update([
            'name' => $request->name,
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building' => $request->building,
            'image_path' => $imagePath,
        ]);

        return redirect('/mypage');
    }
}
