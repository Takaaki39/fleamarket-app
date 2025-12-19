@extends('layout.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/profile/transaction_chat.css')}}">
@endsection

@section('content')
<div class="container">

    <!-- サイドバー -->
    <aside class="sidebar">
        <h1 class="sidebar-title">その他の取引</h1>
        @if($is_customer === false)
        <ul class="product-list">
            @foreach($transactions as $element)
            <li>
                <a
                    href="{{ route('transaction', ['transaction_id' => $element->id]) }}"
                    class="transaction-link
                        {{ request()->route('transaction_id') == $element->id ? 'active' : '' }}">
                    {{ $element->item->name }}
                </a>
            </li>
            @endforeach
        </ul>
        @endif
    </aside>

    <!-- メイン -->
    <main class="main">

        <!-- 取引相手 -->
        <div class="transaction-header">
            <div class="transaction-left">
                <div class="avatar">
                    @if($other->icon_img)
                    <img src="{{ asset('storage/' . $other->icon_img) }}" alt="プロフィール画像">
                    @endif
                </div>
                <h1>「{{ $other?->name }}」さんとの取引画面</h1>
            </div>

            @if($is_customer)
            <button
                type="button"
                class="complete-btn"
                onclick="openCompleteModal()">
                取引完了
            </button>
            @endif
        </div>

        <!-- 商品情報 -->
        <div class="product-info">
            <div class="product-image">
                <img src="{{ $item->img_url }}" alt="商品画像" class="product-image">
            </div>

            <div class="product-detail">
                <h2>{{ $item->name }}</h2>
                <p class="price">¥{{$item->price_label}} <span>(税込)</span></p>
            </div>
        </div>

        <!-- メッセージエリア -->
        <div class="chat-area" id="chatArea">
            @foreach($chats as $chat)
            @if($chat->user_id === auth()->id())
            <!-- 自分メッセージ -->
            <div class="chat-row right">
                <div class="message-wrapper">
                    <div class="user-name right">{{ auth()->user()->name }}</div>
                    {{-- 表示用 --}}
                    <div id="view-message-{{ $chat->id }}">
                        <div
                            class="message-box my-message">
                            {{ $chat->message }}
                        </div>
                        {{-- 編集・削除 --}}
                        <div class="message-actions">
                            @if(
                            session('edit_chat_id') == $chat->id &&
                            $errors->editChat->has('message')
                            )
                            <div class="error"> {{ $errors->editChat->first('message') }}</div>
                            @endif
                            <form method="POST"
                                action="{{ route('transaction_chat.edit', ['transaction_id' => $transaction->id]) }}"
                                class="inline-form" novalidate>
                                @csrf
                                <input type="hidden" name="chat_id" value="{{ $chat->id }}">
                                <button type="button" class="action-link" onclick="startEdit({{ $chat->id }})">編集</button>
                            </form>

                            <form method="POST"
                                action="{{ route('transaction_chat.delete', ['transaction_id' => $transaction->id]) }}"
                                class="inline-form"
                                onsubmit="return confirm('削除しますか？');" novalidate>
                                @csrf
                                <input type="hidden" name="chat_id" value="{{ $chat->id }}">
                                <button type="submit" class="action-link">削除</button>
                            </form>
                        </div>
                    </div>

                    {{-- 編集用 --}}
                    <form
                        method="POST"
                        action="{{ route('transaction_chat.edit', ['transaction_id' => $transaction->id]) }}"
                        class="edit-form"
                        id="edit-form-{{ $chat->id }}"
                        style="display:none;">
                        @csrf
                        <input type="hidden" name="chat_id" value="{{ $chat->id }}">
                        <textarea
                            name="message"
                            class="message-box my-message edit-textarea"
                            rows="2">{{ $chat->message }}</textarea>

                        <div class="edit-actions">
                            <button type="submit" class="action-link">保存</button>
                            <button
                                type="button"
                                class="action-link"
                                onclick="cancelEdit({{ $chat->id }})">
                                キャンセル
                            </button>
                        </div>
                    </form>
                </div>
                <div class="message-image">
                    @if($chat->image)
                    <img src="{{ asset('storage/' . $chat->image) }}" class="chat-image" alt="画像">
                    @endif
                </div>
                <div class="avatar small">
                    @if(auth()->user()->icon_img)
                    <img src="{{ asset('storage/' . auth()->user()->icon_img) }}" alt="プロフィール画像">
                    @endif
                </div>
            </div>
            @else
            <!-- 相手メッセージ -->
            <div class="chat-row left">
                <div class="avatar small">
                    @if($chat->user->icon_img)
                    <img src="{{ asset('storage/' . $chat->user->icon_img) }}" alt="プロフィール画像">
                    @endif
                </div>
                <div>
                    <div class="user-name">{{ $chat->user->name }}</div>
                    <div class="message-box">
                        {{ $chat->message }}
                    </div>
                </div>
                <div class="message-image">
                    @if($chat->image)
                    <img src="{{ asset('storage/' . $chat->image) }}" class="chat-image" alt="画像">
                    @endif
                </div>
            </div>
            @endif
            @endforeach
        </div>

        <!-- 入力エリア -->
        <form
            class="chat-input"
            method="POST"
            action="{{ route('transaction_chat', ['transaction_id' => $transaction->id]) }}"
            enctype="multipart/form-data"
            novalidate>
            @csrf

            <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
            <input
                type="text"
                id="chatMessageInput"
                name="message"
                placeholder="取引メッセージを記入してください"
                value="{{ old('message') }}"
                required>

            <label class="image-btn">
                <input type="file" name="image" id="image" accept="image/*" hidden>
                画像を追加
            </label>

            <button type="submit" class="send-btn">
                <img src="{{ asset('storage/images/inputbutton.png') }}" alt="">
            </button>
        </form>
        @error('message', 'transactionChat')
        <div class="error">{{ $message }}</div>
        @enderror
        @error('image')
        <div class="error">{{ $message }}</div>
        @enderror

        <!-- 画面全体を覆うオーバーレイ -->
        <div class="modal-overlay" id="completeModal">
            <div class="complete-modal">
                <h1>取引が完了しました。</h1>

                <hr>

                <p class="modal-text">今回の取引相手はどうでしたか？</p>

                <!-- ★評価 -->
                <div class="stars">
                    <span data-value="1">★</span>
                    <span data-value="2">★</span>
                    <span data-value="3">★</span>
                    <span data-value="4">★</span>
                    <span data-value="5">★</span>
                </div>

                <hr>

                <div class="modal-actions">
                    <form method="POST" action="{{ route('transaction.complete', ['transaction_id' => $transaction->id]) }}">
                        @csrf
                        <input type="hidden" name="rating" id="ratingInput">
                        <button type="submit" class="modal-send">
                            送信する
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @if($transaction->status === 1)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                openCompleteModal();
            });
        </script>
        @endif
    </main>
</div>

<script>
    const transactionId = {{ $transaction->id }};
    const draftKey = `chat_draft_${transactionId}`;
    const messageInput = document.getElementById('chatMessageInput');

    /* ページ表示時：下書きを復元 */
    document.addEventListener('DOMContentLoaded', () => {
        const savedDraft = localStorage.getItem(draftKey);
        if (savedDraft !== null) {
            messageInput.value = savedDraft;
        }
    });

    /* 入力中：transaction_idごとに保存 */
    messageInput.addEventListener('input', () => {
        localStorage.setItem(draftKey, messageInput.value);
    });

    /* 送信時：下書きを削除 */
    document.querySelector('.chat-input').addEventListener('submit', () => {
        localStorage.removeItem(draftKey);
    });
    window.addEventListener('load', () => {
        const chatArea = document.getElementById('chatArea');
        chatArea.scrollTop = chatArea.scrollHeight;
    });

    function startEdit(chatId) {
        document.getElementById(`view-message-${chatId}`).style.display = 'none';
        document.getElementById(`edit-form-${chatId}`).style.display = 'block';
    }

    function cancelEdit(chatId) {
        document.getElementById(`edit-form-${chatId}`).style.display = 'none';
        document.getElementById(`view-message-${chatId}`).style.display = 'block';
    }

    function openCompleteModal() {
        document.getElementById('completeModal').style.display = 'flex';
    }

    /* 星評価 */
    let selectedRating = 0;

    document.querySelectorAll('.stars span').forEach(star => {
        star.addEventListener('click', () => {
            selectedRating = star.dataset.value;

            document.getElementById('ratingInput').value = selectedRating;

            document.querySelectorAll('.stars span').forEach(s => {
                s.classList.toggle('active', s.dataset.value <= selectedRating);
            });
        });
    });
</script>
@endsection