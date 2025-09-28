<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Http\Requests\ExhibitionRequest;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();
        $search = $request->query('search');

        if ($request->query('tab') === 'mylist') {
            // マイリスト: favoriteテーブルで自分がお気に入りした商品
            $query->whereHas('favorites', function ($q) {
                $q->where('user_id', auth()->id());
            });
        } else {
            // おすすめ: 他のユーザーが出品した商品
            $query->where('seller_id', '!=', auth()->id());
        }

        // 検索機能
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        $items = $query->get();

        return view('index', compact('items'));
    }

    public function sell()
    {
        $categories = Category::all();

        return view('sell', compact('categories'));
    }

    public function storeItem(ExhibitionRequest $request)
    {
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('items', 'public');
        }

        $item = Item::create([
            'name' => $request->name,
            'brand' => $request->brand,
            'description' => $request->description,
            'image_path' => $imagePath,
            'price' => $request->price,
            'condition' => $request->condition,
            'seller_id' => auth()->id()
        ]);

        // カテゴリーの関連付け
        $item->categories()->sync($request->categories);

        return redirect('/');
    }

    /**
     * 商品詳細ページを表示
     *
     * @param int $item_id
     * @return \Illuminate\View\View
     */
    public function detail($item_id)
    {
        // 商品情報を取得（関連データも含む）
        $item = Item::with(['seller', 'categories', 'comments.user', 'favorites'])
            ->withCount(['comments', 'favorites'])
            ->findOrFail($item_id);

        return view('detail', compact('item'));
    }

    /**
     * お気に入りトグル処理
     *
     * @param int $item_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function favorite($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = auth()->user();

        // 既にお気に入りしているかチェック
        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('item_id', $item->id)
            ->first();

        if ($existingFavorite) {
            // 既にお気に入りしている場合は削除（お気に入り解除）
            $existingFavorite->delete();
        } else {
            // お気に入りしていない場合は追加
            Favorite::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
            ]);
        }

        // 商品詳細ページにリダイレクト
        return redirect()->back();
    }

    /**
     * 商品へのコメント投稿処理
     *
     * @param CommentRequest $request
     * @param int $item_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function comment(CommentRequest $request, $item_id)
    {

        $item = Item::findOrFail($item_id);
        $user = auth()->user();

        // コメントを作成
        Comment::create([
            'content' => $request->comment,
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // 商品詳細ページにリダイレクト
        return redirect()->back();
    }

    /**
     * 購入ページを表示
     *
     * @param int $item_id
     * @return \Illuminate\View\View
     */
    public function purchase($item_id)
    {
        $item = Item::findOrFail($item_id);

        // 既に購入済みの商品かチェック
        if ($item->purchaser_id) {
            return redirect('/item/' . $item_id);
        }

        // Sessionから配送先を取得、なければユーザー情報を使用
        $shippingAddress = session('shipping_address.' . $item_id, [
            'postal_code' => auth()->user()->postal_code,
            'address' => auth()->user()->address,
            'building' => auth()->user()->building,
        ]);

        return view('purchase', compact('item', 'shippingAddress'));
    }

    /**
     * 住所変更ページを表示
     *
     * @param int $item_id
     * @return \Illuminate\View\View
     */
    public function editAddress($item_id)
    {
        // Sessionから現在の配送先を取得、なければユーザー情報を使用
        $currentAddress = session('shipping_address.' . $item_id, [
            'postal_code' => auth()->user()->postal_code,
            'address' => auth()->user()->address,
            'building' => auth()->user()->building,
        ]);

        return view('address', compact('item_id', 'currentAddress'));
    }

    /**
     * 住所更新処理
     *
     * @param AddressRequest $request
     * @param int $item_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAddress(AddressRequest $request, $item_id)
    {
        // 配送先をSessionに保存（商品IDごとに管理）
        session(['shipping_address.' . $item_id => [
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building' => $request->building,
            'updated_at' => now(),
        ]]);

        // 購入ページにリダイレクト
        return redirect('/purchase/' . $item_id);
    }

    /**
     * 購入処理
     *
     * @param Request $request
     * @param int $item_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkout(PurchaseRequest $request, $item_id)
    {

        $item = Item::findOrFail($item_id);
        $user = auth()->user();

        // 既に購入済みの商品かチェック
        if ($item->purchaser_id) {
            return redirect('/item/' . $item_id);
        }

        // 自分の出品した商品は購入できない
        if ($item->seller_id === $user->id) {
            return redirect('/item/' . $item_id);
        }

        // POSTから支払い方法を取得
        $paymentMethod = $request->payment_method;

        // 支払い方法に応じてStripeの決済方法を設定
        $paymentMethodTypes = in_array($paymentMethod, ['konbini', 'card'])
            ? [$paymentMethod]
            : ['card', 'konbini'];

        // Sessionから配送先を取得
        $shippingAddress = session('shipping_address.' . $item_id, [
            'postal_code' => $user->postal_code,
            'address' => $user->address,
            'building' => $user->building,
        ]);

        // Stripeキーの確認
        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret) || $stripeSecret === 'your_stripe_secret_key_here') {
            return redirect('/purchase/' . $item_id);
        }

        // Stripeの設定
        Stripe::setApiKey($stripeSecret);

        try {
            $session = Session::create([
                'payment_method_types' => $paymentMethodTypes,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => $item->name,
                            'description' => $item->description,
                        ],
                        'unit_amount' => $item->price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/purchase/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/purchase/cancel'),
                'metadata' => [
                    'item_id' => $item_id,
                    'user_id' => $user->id,
                    'postal_code' => $shippingAddress['postal_code'] ?? '',
                    'address' => $shippingAddress['address'] ?? '',
                    'building' => $shippingAddress['building'] ?? '',
                ],
            ]);
            // Dusk環境では決済が完了した前提で、決済成功ルートへ
            if (app()->environment('dusk')) {
                return redirect('/purchase/success?session_id=' . $session->id);
            }
            return redirect($session->url);
        } catch (\Exception $e) {
            return redirect('/purchase/' . $item_id);
        }
    }

    /**
     * Stripe決済成功時の処理
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stripeSuccess(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect('/')->with('error', '不正なアクセスです');
        }

        // Stripeキーの確認
        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return redirect('/')->with('error', 'Stripe決済の設定が完了していません');
        }
        Stripe::setApiKey($stripeSecret);

        try {
            $session = Session::retrieve($sessionId);

            // Dusk環境ではStripe側の決済処理ができないため、決済完了フラグを設定
            if (app()->environment('dusk')) {
                $session->payment_status = 'paid';
            }

            if ($session->payment_status === 'paid') {
                $metadata = $session->metadata;
                $item = Item::findOrFail($metadata['item_id']);

                // 購入処理
                $item->update([
                    'purchaser_id' => $metadata['user_id'],
                    'postal_code' => $metadata['postal_code'],
                    'address' => $metadata['address'],
                    'building' => $metadata['building'],
                ]);

                // Sessionクリア
                session()->forget('shipping_address.' . $metadata['item_id']);

                return redirect('/')->with('success', '商品を購入しました');
            }
        } catch (\Exception $e) {
            return redirect('/')->with('error', '決済の確認中にエラーが発生しました');
        }

        return redirect('/')->with('error', '決済が完了していません');
    }

    /**
     * Stripe決済キャンセル時の処理
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stripeCancel()
    {
        return redirect()->back();
    }
}
