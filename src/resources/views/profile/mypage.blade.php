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
        <h2 class="username">{{$user->name}}</h2>
        <a class="edit-btn" href="{{ route('mypage.edit') }}">
            プロフィールを編集
        </a>
    </section>

    <nav class="tabs">
        <a 
            href="{{ route('mypage', array_filter(['page' => 'sell'])) }}" 
            class="tab {{ request('page') !== 'buy' ? 'active' : '' }}"
        >出品した商品</a>
        <a 
            href="{{ route('mypage', array_filter(['page' => 'buy'])) }}" 
            class="tab {{ request('page') === 'buy' ? 'active' : '' }}"
        >購入した商品</a>
    </nav>

    <section class="items">
        @foreach($items as $item)
            <div class="product-card">
                <img src="{{ $item->img_url }}" alt="商品画像" class="product-image">
                <p class="product-name">{{$item->name}}</p>
            </div>
        @endforeach
    </section>
</main>
@endsection
