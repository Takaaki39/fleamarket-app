<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;

class PurchaseController extends Controller
{
    public function index($item_id)
    {
        $item = Item::findOrFail($item_id);

        // ログイン中のユーザー取得
        $user = Auth::user();

        // 配送先データ（最初はユーザー情報を使用）
        $delivery = [
            'postal_code' => $user->postal_code,
            'address' => $user->address,
            'building' => $user->building,
        ];

        if (session()->has('delivery')) 
        {
            $delivery = session('delivery');
        }
        session(['delivery' => $delivery]);

        return view('shop.purchase', compact('item', 'delivery'));
    }

    public function address($item_id)
    {
        $item = Item::find($item_id);
        $delivery = session('delivery');
        return view('shop.address_edit', compact('item', 'delivery'));
    }

    public function updateAddress(AddressRequest $request, $item_id)
    {
        $item = Item::find($item_id);
        $delivery = [
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building' => $request->building,
        ];
        session(['delivery' => $delivery]);
        
        return redirect()->route('purchase.index', ['item_id' => $item_id]);
    }

    public function store(PurchaseRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // 購入レコード作成
        Purchase::create([
            'item_id' => $item->id,
            'user_id' => $user->id,
            'payment' => $request->payment,
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building' => $request->building,
        ]);

        return redirect()->route('index')->with('success', '購入が完了しました！');
    }
}
