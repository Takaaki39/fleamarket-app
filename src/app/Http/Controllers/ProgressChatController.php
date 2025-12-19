<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRequest;
use App\Http\Requests\EditChatRequest;
use App\Mail\TransactionCompletedMail;
use App\Models\Evaluation;
use App\Models\Item;
use App\Models\Progress;
use App\Models\ProgressChat;
use App\Models\Sell;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ProgressChatController extends Controller
{
    public function start($item_id)
    {
        $user = Auth::user();

        $item = Item::findOrFail($item_id);

        // すでに取引中かチェック（重複防止）
        $progress = Progress::where('item_id', $item->id)
            ->where('customer_id', $user->id)
            ->first();

        $seller = Sell::where('item_id', $item->id)->first();

        if (!$progress) {
            $progress = Progress::create([
                'item_id'     => $item->id,
                'user_id'     => $seller->user_id, // 出品者
                'customer_id' => $user->id,       // 購入者
                'status'      => 0,               // 取引中
            ]);
        }

        // 作成後、そのままチャット画面へ
        return redirect()->route('progress', [
            'progress_id' => $progress->id
        ]);
    }

    //
    public function progress($progress_id)
    {
        $user = auth()->user();
        $progress = Progress::find($progress_id);
        $item = Item::find($progress->item_id);

        // sellsテーブルのitem_idが$item_idと一致するデータを取得
        $sells = Sell::where('item_id', $item->id)->first();
        if ($sells->user_id === $user->id) {
            // 出品者
            $progresses = Progress::where('user_id', $user->id)->get();
            $is_customer = false;
        } else {
            // 購入者
            $progresses = Progress::where('customer_id', $user->id)->get();
            $is_customer = true;
        }

        // progressesの$item_idと$item_idが一致するProgressデータのcustomer_idからUserを取得
        $other_id = $is_customer ? $progress->user_id : $progress->customer_id;
        $other = User::find($other_id);

        // $progress_idに対応するチャットデータを取得
        $chats = ProgressChat::where('progress_id', $progress_id)->get();

        // ここから既読処理 
        ProgressChat::where('progress_id', $progress_id)
            ->where('user_id', '!=', $user->id) // 自分以外の投稿
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return view('profile.progress_chat', ['item' => $item, 'progress' => $progress, 'progresses' => $progresses, 'other' => $other, 'chats' => $chats, 'is_customer' => $is_customer]);
    }

    public function chat(ChatRequest $request)
    {
        $progress = Progress::find($request->progress_id);

        // $user->idが$progress->customer_idと一致したら客用表示、user_idと一致したら売主用表示
        $chat = ProgressChat::create([
            'progress_id' => $progress->id,
            'user_id'     => auth()->id(),
            'message'     => $request->message,
            'is_customer' => auth()->id() === $progress->customer_id,
        ]);
        // 画像アップロード処理
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/chats', 'public');
            $chat->update([
                'image' => $path,
            ]);
        }

        //profile.progress_chatにリダイレクト
        return redirect()->route('progress', ['progress_id' => $progress->id]);
    }

    public function editChat(EditChatRequest $request)
    {
        // $request->chat_idで指定されたチャットデータを取得
        $chat = ProgressChat::find($request->chat_id);
        // チャットデータを更新
        $chat->update([
            'message' => $request->message,
        ]);
        // profile.progress_chatにリダイレクト
        return redirect()->route('progress', ['progress_id' => $chat->progress->id]);
    }

    public function deleteChat(Request $request)
    {
        // $request->chat_idで指定されたチャットデータを取得
        $chat = ProgressChat::find($request->chat_id);
        $item_id = $chat->progress->item_id;
        // チャットデータを削除
        $chat->delete();
        // profile.progress_chatにリダイレクト
        return redirect()->route('progress', ['progress_id' => $chat->progress->id]);
    }

    public function complete(Request $request, $progress_id)
    {
        $progress = Progress::findOrFail($progress_id);
        // 取引ステータスを「完了」に更新
        $progress->status = $progress->status === 0 ? 1 : 2;
        $progress->save();

        // 評価されるユーザー（取引相手）
        $evaluatedUserId =
            auth()->id() === $progress->customer_id
            ? $progress->user_id      // 出品者を評価
            : $progress->customer_id; // 購入者を評価

        Evaluation::create([
            'user_id'      => $evaluatedUserId,
            'evaluator_id' => auth()->id(),
            'rating'       => $request->rating,
        ]);

        // 購入者が評価した場合のみメール送信
        if (auth()->id() === $progress->customer_id) {
            $seller = $progress->user;     // 出品者
            $buyer  = auth()->user();      // 購入者

            Mail::to($seller->email)
                ->send(new TransactionCompletedMail($progress, $buyer));
        }

        // トップページへリダイレクト
        return redirect()
            ->route('index')
            ->with('success', '評価を送信しました');
    }
}
