<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Sell;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ExhibitionRequest;

class SellController extends Controller
{
    //
    public function create()
    {
        $categories = Category::all();
        return view('shop/sell', compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        $path = null;
        if ($request->hasFile('img_url')) 
        {
            $path = $request->file('img_url')->store('images/items', 'public');
        }

        $item = Item::create([
            'name' => $request->name,
            'price' => $request->price,
            'brand_name' => $request->brand_name,
            'description' => $request->description,
            'condition' => $request->condition,
            'img_url' => $path,
        ]);
    
        $categories = json_decode($request->input('categories'), true) ?? [];
        foreach ($categories as $categoryId) 
        {
            ItemCategory::create([
                'item_id'     => $item->id,
                'category_id' => $categoryId,
            ]);
        }

        $user = Auth::user();
        // sellモデルに出品履歴を追加
        Sell::create([
            'user_id' => $user->id,
            'item_id' => $item->id
        ]);
        
        // ItemControllerのindex()を呼び出す
        return redirect()->route('index')->with('success', '商品を出品しました！');
    }

}
