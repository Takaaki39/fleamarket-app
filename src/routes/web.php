<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// トップページ
Route::get('/', [ItemController::class, 'index'])->name('index');

Route::prefix('purchase')->group(function () {
    // 購入関連
    Route::get('/address/{item_id}', [PurchaseController::class, 'address'])->name('purchase.address.edit');
    Route::post('/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');
    Route::post('/{item_id}', [PurchaseController::class, 'pay'])->name('purchase.pay');

    // 支払い完了後の画面表示(Route::get('/purchase/{item_id}',と衝突して404になるので前に持ってくる)
    Route::get('/success', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/cancel', [PurchaseController::class, 'cancel'])->name('purchase.cancel');

});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage');
    Route::get('/sell', [SellController::class, 'create'])->name('sell.create');
    Route::post('/item/{item_id}/star', [ItemController::class, 'star'])->name('items.star');
    Route::post('/item/{item_id}/comment', [ItemController::class, 'comment'])->name('items.comment');
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'index'])->name('purchase.index');
});

// メール内のURLでの認証（Fortify標準）
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // メールアドレス認証完了
    return redirect('/mypage/profile')->with('success', 'メール認証が完了しました！');
})->middleware(['auth', 'signed'])->name('verification.verify');

// メール認証待ち画面
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました！');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 手動コード入力ページ
Route::get('/email/verify/manual', [AuthController::class, 'show'])
    ->middleware('auth')
    ->name('verification.manual');

// コード入力フォーム送信処理
Route::post('/email/verify/manual', [AuthController::class, 'verify'])
    ->middleware('auth')
    ->name('verification.manual.verify');

// 商品詳細ページ
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show');

// マイページ編集
Route::get('/mypage/profile', [MypageController::class, 'edit'])->name('mypage.edit');
Route::post('/mypage/profile', [MypageController::class, 'update']);

// 出品
Route::post('/sell', [SellController::class, 'store'])->name('sell.store');

// 支払い完了通知
Route::post('/stripe/webhook', [PurchaseController::class, 'handle']);