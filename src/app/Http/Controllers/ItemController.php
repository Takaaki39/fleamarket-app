<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemStar;
use App\Models\ItemComment;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sell;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CommentRequest;

class ItemController extends Controller
{

    public function index(Request $request)
    {
        // 売れた商品の item_id 一覧を取得
        $soldItemIds = Purchase::pluck('item_id')->toArray();

        // 自分が出品した商品の item_id 一覧を取得
        $mySellItemIds = [];
        if (auth()->check()) 
        {
            $mySellItemIds = Sell::where('user_id', auth()->id())->pluck('item_id')->toArray();
        }

        // 検索ワード
        $search = $request->input('search');

        if ($request->tab === 'mylist') 
        {
            if (!auth()->check()) 
            {
                return redirect()->route('login');
            }
            // マイリスト（お気に入り）
            $items = Item::whereIn('id', function ($query) {
                    $query->select('item_id')
                        ->from('item_stars')
                        ->where('user_id', auth()->id());
                })
                ->whereNotIn('id', $mySellItemIds) // ← 自分の出品物を除外
                ->when($search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->get();
        } 
        else 
        {
            // おすすめ（全商品）
            $items = Item::whereNotIn('id', $mySellItemIds)
                ->when($search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->get();
        }

        // sold フラグを設定（item.id が purchases.item_id に含まれていたら true）
        foreach ($items as $item) 
        {
            $item->sold = in_array($item->id, $soldItemIds);
        }

        return view('index', compact('items'));
    }

    public function show($item_id)
    {
        $item = Item::with(['categories', 'comments.user'])->withCount('stars')->findOrFail($item_id);
        return view('shop.item', compact('item'));
    }

    public function star($item_id)
    {
        $star = ItemStar::where('user_id', Auth::id())
                        ->where('item_id', $item_id)
                        ->first();

        if ($star)
        {
            // すでにスターしていたら解除
            $star->delete();
        } 
        else 
        {
            // スターしていなければ追加
            ItemStar::create([
                'user_id' => Auth::id(),
                'item_id' => $item_id,
            ]);
        }
        return back(); // 元のページに戻る
    }

    public function comment(CommentRequest $request, $item_id)
    {
        ItemComment::create([
                'user_id' => Auth::id(),
                'item_id' => $item_id,
                'content' => $request->input('content'),
            ]);

        return redirect()->route('item.show', $item_id)->with('success', 'コメントを投稿しました。');
    }

}
