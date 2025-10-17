<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Event;

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

    public function pay(PurchaseRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // 購入レコード作成(購入ボタンを押したら作成)
        Purchase::create([
            'item_id' => $item->id,
            'user_id' => $user->id,
            'paid' => false,
            'payment' => $request->payment,
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building' => $request->building,
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentMethod = $request->payment;

        // Stripe Checkout セッション作成
        $session = Session::create([
            'payment_method_types' => 
                $paymentMethod === '1' ? ['card'] : ['konbini'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    'unit_amount' => $item->price, // 円単位
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success'),
            'cancel_url' => route('purchase.cancel'),
            
            // Webhookで特定できるようmetadataにpurchase_idを渡す
            'metadata' => [
                'purchase_id' => $item_id,
            ],

        ]);

        // Stripeの決済画面にリダイレクト
        return redirect($session->url);
    }

    public function success()
    {
        return view('shop.success');
    }
    
    public function cancel()
    {
        return view('shop.cancel');
    }

    public function handle(Request $request)
    {
        // Stripeの秘密鍵をセット
        Stripe::setApiKey(config('services.stripe.secret'));

        // Stripeからのイベントデータを取得
        $payload = @file_get_contents('php://input');
        $event = null;

        try {
            $event = json_decode($payload, true);
        } catch (\UnexpectedValueException $e) {
            // JSONが不正な場合
            return response('Invalid payload', 400);
        }

        // イベントタイプに応じた処理
        switch ($event['type']) {

            // 💰 カード・コンビニいずれも支払い完了時に発火
            case 'checkout.session.completed':
                $session = $event['data']['object'];

                // 商品ID（＝Purchaseのid）をmetadataに入れておく想定
                $purchaseId = $session['metadata']['purchase_id'] ?? null;

                if ($purchaseId) {
                    $purchase = Purchase::find($purchaseId);
                    if ($purchase) {
                        $purchase->paid = true; // ←ここスペル修正済み
                        $purchase->save();
                    }
                }

                break;
        }

        return response('Webhook handled', 200);
    }
}
