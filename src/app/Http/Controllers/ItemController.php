<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
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

    public function store(ExhibitionRequest $request)
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
}
