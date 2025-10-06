@extends('layout.app')
@extends('layout.header')

@section('css')
<link rel="stylesheet" href="{{asset('css/item.css')}}">
@endsection

@section('content')
<main class="container">
    <!-- 左側 商品画像 -->
    <div class="image-area">
        <div class="product-image">
            <img src="{{ $item->img_url }}" alt="商品画像" class="product-image">
        </div>
    </div>

    <!-- 右側 詳細 -->
    <div class="detail-area">
        <h2 class="product-title">{{$item->name}}</h2>
        <p class="brand">{{$item->brand_name}}</p>
        <p class="price">¥{{$item->price_label}} <span>(税込)</span></p>

        <div class="actions">
            <div class="action-item">
                <form action="{{ route('items.star', $item->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="star-btn">
                        @if($item->stars->contains('user_id', Auth::id()))
                            <img src="{{ asset('storage/images/star_filled.png') }}" alt="star">
                        @else
                            <img src="{{ asset('storage/images/star.png') }}" alt="star">
                        @endif
                    </button>
                </form>
                <span class="count">{{$item->star_count}}</span>
            </div>
            <div class="action-item">
                <img src="{{ asset('storage/images/balloon.png') }}" alt="comment">
                <span class="count">{{$item->comment_count}}</span>
            </div>
        </div>

        <a href="{{ route('purchase.index', ['item_id' => $item->id]) }}" class="buy-btn">
            購入手続きへ
        </a>

        <section class="description">
            <h3>商品説明</h3>
            <p>{{$item->description}}</p>
        </section>

        <section class="info">
            <h3>商品の情報</h3>
            <p>カテゴリー：
                @foreach($item->categories as $category)
                    <span class="tag">{{$category->category_name}}</span>    
                @endforeach
            </p>
            <p>商品の状態：{{$item->condition_label}}</p>
        </section>

        <section class="comments">
            <h3>コメント({{$item->comment_count}})</h3>
            @foreach($item->comments as $comment) 
                <div class="comment">
                    <div class="comment-header">
                        <div class="icon">
                            @if($comment->user->icon_img)
                                <img src="{{ asset('storage/' . $comment->user->icon_img) }}" alt="プロフィール画像">
                            @endif
                        </div>
                        <p class="user">{{$comment->user->name}}</p>
                    </div>
                    <div class="comment-body">
                        <p class="text">{{$comment->content}}</p>
                    </div>
                </div>
            @endforeach

            <h4>商品へのコメント</h4>
            <form action="{{ route('items.comment', $item->id) }}" method="POST" novalidate>
                @csrf
                <textarea name="content"></textarea>
                @error('content')
                    <div class="error">{{ $message }}</div>
                @enderror
                <button class="comment-btn">コメントを送信する</button>
            </form>
        </section>
    </div>
</main>
@endsection
