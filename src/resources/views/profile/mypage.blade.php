@extends('layout.app')
@extends('layout.header')

@section('css')
<link rel="stylesheet" href="{{asset('css/profile/mypage.css')}}">
@endsection

@section('content')
<main class="mypage">
    <section class="profile">
        <div class="icon">
            @if($user->icon_img)
            <img src="{{ asset('storage/' . $user->icon_img) }}" alt="プロフィール画像">
            @endif
        </div>
        <div class="user-info">
            <h1 class="username">{{ $user->name }}</h1>

            <div class="rating">
                @for ($i = 1; $i <= 5; $i++)
                    <span class="{{ $i <= $displayRating ? 'active' : '' }}">★</span>
                    @endfor
            </div>
        </div>
        <a class="edit-btn" href="{{ route('mypage.edit') }}">
            プロフィールを編集
        </a>
    </section>

    <nav class="tabs">
        <a
            href="{{ route('mypage', array_filter(['page' => 'sell'])) }}"
            class="tab {{ request('page') === null ||request('page') === 'sell' ? 'active' : '' }}">
            出品した商品
        </a>
        <a
            href="{{ route('mypage', array_filter(['page' => 'buy'])) }}"
            class="tab {{ request('page') === 'buy' ? 'active' : '' }}">
            購入した商品
        </a>
        <a
            href="{{ route('mypage', array_filter(['page' => 'transaction'])) }}"
            class="tab {{ request('page') === 'transaction' ? 'active' : '' }}">
            取引中の商品

            @if(auth()->user()->unread_chats > 0)
            <div class="all-unread-badge">
                {{ auth()->user()->unread_chats > 99 ? '99+' : auth()->user()->unread_chats }}
            </div>
            @endif
        </a>
    </nav>

    <section class="items">
        @if(request('page') === 'transaction')
        @foreach($transactions as $transaction)
        <a href="{{ route('transaction', $transaction->id) }}" class="product-card-link">
            <div class="product-card">
                {{-- 未読バッジ --}}
                @if($transaction->unreadCount > 0)
                <div class="unread-badge">
                    {{ $transaction->unreadCount > 99 ? '99+' : $transaction->unreadCount }}
                </div>
                @endif
                <img src="{{ $transaction->item->img_url }}" alt="商品画像" class="product-image">
                <p class="product-name">{{$transaction->item->name}}</p>
            </div>
        </a>
        @endforeach
        @else
        @foreach($items as $item)
        <div class="product-card">
            <img src="{{ $item->img_url }}" alt="商品画像" class="product-image">
            <p class="product-name">{{$item->name}}</p>
        </div>
        @endforeach
        @endif
    </section>
</main>
@endsection