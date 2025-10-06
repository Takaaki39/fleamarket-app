@extends('layout.app')
@extends('layout.header')

@section('css')
<link rel="stylesheet" href="{{asset('css/index.css')}}">
@endsection

@section('content')
<main>
     <!-- タブメニュー -->
    <nav class="tab-menu">
        <div class="tab-inner">
            <a 
                href="{{ route('index', array_filter(['search' => request('search')])) }}" 
                class="tab {{ request('tab') !== 'mylist' ? 'active' : '' }}"
            >おすすめ</a>
            <a 
                href="{{ route('index', array_filter(['tab' => 'mylist', 'search' => request('search')])) }}" 
                class="tab {{ request('tab') === 'mylist' ? 'active' : '' }}"
            >マイリスト</a>
        </div>
    </nav>

    <!-- 商品リスト -->
    <div class="product-list">
        @if($items->isEmpty())
            <p class="no-result">該当する商品はありません。</p>
        @endif
        @foreach($items as $item)
            @if(!$item->sold)
                <a href="{{ route('item.show', $item->id) }}" class="product-card">
                    <div class="image-wrapper">
                        <img src="{{ $item->img_url }}" alt="商品画像" class="product-image">
                    </div>
                    <p class="product-name">{{ $item->name }}</p>
                </a>
            @else
                <div class="product-card sold">
                    <div class="image-wrapper">
                        <img src="{{ $item->img_url }}" alt="商品画像" class="product-image">
                        <div class="sold-overlay">
                            <span class="sold-text">SOLD</span>
                        </div>
                    </div>
                    <p class="product-name">{{ $item->name }}</p>
                </div>
            @endif
        @endforeach
    </div>
</main>

@endsection