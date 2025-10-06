@section('header')
<!-- 中央検索フォーム -->
<div class="header-section header-center">
    <form action="{{ route('index') }}" method="GET">
        <input 
            type="text" 
            name="search" 
            placeholder="なにをお探しですか？" 
            value="{{ request('search') }}"
        >
        @if(request('tab') === 'mylist')
            <input type="hidden" name="tab" value="mylist">
        @endif
    </form>
</div>

<!-- 右側（PCではリンク群 / SPではハンバーガー） -->
<div class="header-section header-right">
    <!-- ハンバーガーアイコン（スマホ用） -->
    <button class="menu-toggle" id="menu-toggle">☰</button>

    <!-- メニュー本体 -->
    <div class="header-menu" id="header-menu">
        @if(Auth::check() && Auth::user()->hasVerifiedEmail())
            <form class="header-btn" action="/logout" method="post">
                @csrf
                <button class="header-auth" type="submit">ログアウト</button>
            </form>
        @else
            @if(!Auth::check())
                <a class = "header-auth" href="{{ route('login') }}">ログイン</a>
            @else
                <a class = "header-auth" href="{{ route('verification.notice') }}">ログイン</a>
            @endif
        @endif
        <a class="header-auth header-mypage" href="/mypage">マイページ</a>
        <a href="{{ route('sell.create') }}" class="sell-btn">出品</a>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const toggle = document.getElementById("menu-toggle");
        const menu = document.getElementById("header-menu");

        toggle.addEventListener("click", () => {
            menu.classList.toggle("active");
        });
    });
</script>
@endsection