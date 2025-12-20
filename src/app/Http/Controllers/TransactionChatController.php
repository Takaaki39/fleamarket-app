<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRequest;
use App\Http\Requests\EditChatRequest;
use App\Mail\TransactionCompletedMail;
use App\Models\Evaluation;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionChat;
use App\Models\Sell;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TransactionChatController extends Controller
{
    public function start($item_id)
    {
        $user = Auth::user();

        $item = Item::findOrFail($item_id);

        // すでに取引中かチェック（重複防止）
        $transaction = Transaction::where('item_id', $item->id)
            ->where('customer_id', $user->id)
            ->first();
        if ($transaction && $transaction->status === 2) {
            $transaction->status = 0;
            $transaction->save();
        }

        $seller = Sell::where('item_id', $item->id)->first();

        if (!$transaction) {
            $transaction = Transaction::create([
                'item_id'     => $item->id,
                'user_id'     => $seller->user_id, // 出品者
                'customer_id' => $user->id,       // 購入者
                'status'      => 0,               // 取引中
            ]);
        }

        // 作成後、そのままチャット画面へ
        return redirect()->route('transaction', [
            'transaction_id' => $transaction->id
        ]);
    }

    //
    public function transaction($transaction_id)
    {
        $user = auth()->user();
        $transaction = Transaction::find($transaction_id);
        $item = Item::find($transaction->item_id);

        // sellsテーブルのitem_idが$item_idと一致するデータを取得
        $sells = Sell::where('item_id', $item->id)->first();
        $transactions = $user->allTransactions();

        if ($sells->user_id === $user->id) {
            // 出品者
            $is_customer = false;
        } else {
            // 購入者
            $is_customer = true;
        }

        // transactionsの$item_idと$item_idが一致するTransactionデータのcustomer_idからUserを取得
        $other_id = $is_customer ? $transaction->user_id : $transaction->customer_id;
        $other = User::find($other_id);

        // $transaction_idに対応するチャットデータを取得
        $chats = TransactionChat::where('transaction_id', $transaction->id)->get();
        // ここから既読処理 
        TransactionChat::where('transaction_id', $transaction->id)
            ->where('user_id', '!=', $user->id) // 自分以外の投稿
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return view('profile.transaction_chat', ['item' => $item, 'transaction' => $transaction, 'transactions' => $transactions, 'other' => $other, 'chats' => $chats, 'is_customer' => $is_customer]);
    }

    public function chat(ChatRequest $request)
    {
        $transaction = Transaction::find($request->transaction_id);

        // $user->idが$transaction->customer_idと一致したら客用表示、user_idと一致したら売主用表示
        $chat = TransactionChat::create([
            'transaction_id' => $transaction->id,
            'user_id'     => auth()->id(),
            'message'     => $request->message,
            'is_customer' => auth()->id() === $transaction->customer_id,
        ]);
        // 画像アップロード処理
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/chats', 'public');
            $chat->update([
                'image' => $path,
            ]);
        }

        //profile.transaction_chatにリダイレクト
        return redirect()->route('transaction', ['transaction_id' => $transaction->id]);
    }

    public function editChat(EditChatRequest $request)
    {
        // $request->chat_idで指定されたチャットデータを取得
        $chat = TransactionChat::find($request->chat_id);
        // チャットデータを更新
        $chat->update([
            'message' => $request->message,
        ]);
        // profile.transaction_chatにリダイレクト
        return redirect()->route('transaction', ['transaction_id' => $chat->transaction->id]);
    }

    public function deleteChat(Request $request)
    {
        // $request->chat_idで指定されたチャットデータを取得
        $chat = TransactionChat::find($request->chat_id);
        $item_id = $chat->transaction->item_id;
        // チャットデータを削除
        $chat->delete();
        // profile.transaction_chatにリダイレクト
        return redirect()->route('transaction', ['transaction_id' => $chat->transaction->id]);
    }

    public function complete(Request $request, $transaction_id)
    {
        $transaction = Transaction::findOrFail($transaction_id);
        // 取引ステータスを「完了」に更新
        $transaction->status = $transaction->status === 0 ? 1 : 2;
        $transaction->save();

        // 評価されるユーザー（取引相手）
        $evaluatedUserId =
            auth()->id() === $transaction->customer_id
            ? $transaction->user_id      // 出品者を評価
            : $transaction->customer_id; // 購入者を評価

        Evaluation::create([
            'user_id'      => $evaluatedUserId,
            'evaluator_id' => auth()->id(),
            'rating'       => $request->rating,
        ]);

        // 購入者が評価した場合のみメール送信
        if (auth()->id() === $transaction->customer_id) {
            $seller = $transaction->user;     // 出品者
            $buyer  = auth()->user();      // 購入者

            Mail::to($seller->email)
                ->send(new TransactionCompletedMail($transaction, $buyer));
        }

        // トップページへリダイレクト
        return redirect()
            ->route('index')
            ->with('success', '評価を送信しました');
    }
}
