<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use App\Models\Evaluation;

class MypageController extends Controller
{
    //
    public function index(Request $request)
    {
        // 現在ログイン中のユーザー情報を取得
        $user = Auth::user();

        $items = [];
        $transactions = [];
        if ($request->page === null || $request->page === 'sell') {
            $items = $user->selledItems()->with('sells')->get();
        } else if ($request->page === 'buy') {
            $items = $user->purchasedItems()->with('purchases')->get();
        } else {
            $transactions = $user->allTransactions()->get();
        }

        // 平均評価（小数）
        $averageRating = Evaluation::where('user_id', $user->id)
            ->avg('rating');

        // 表示用（切り捨て）
        $displayRating = $averageRating
            ? floor($averageRating)
            : 0;

        return view('profile/mypage', compact('user', 'items', 'transactions', 'displayRating'));
    }

    public function edit()
    {
        // 現在ログイン中のユーザー情報を取得
        $user = Auth::user();
        return view('profile/edit', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();

        $user->name        = $request->input('username');
        $user->postal_code = $request->input('zipcode');
        $user->address     = $request->input('address');
        $user->building    = $request->input('building');

        // 画像アップロード処理
        if ($request->hasFile('icon_img')) {
            $path = $request->file('icon_img')->store('images/icons', 'public');

            $user->icon_img = $path;
        }

        // userの情報を更新する
        $user->save();

        return redirect()->route('mypage')->with('success', 'プロフィールを更新しました。');
    }
}
