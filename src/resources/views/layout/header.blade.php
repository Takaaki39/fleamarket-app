
{{-- 検索フォーム（子ビュー） --}}
@section('header-center')
    <form action="{{ route('index') }}" method="GET" class="search-form">
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
@endsection

{{-- 右側コントロール（子ビュー） --}}
@section('header-right')
<div class="controls">
    <!-- PCで表示するリンク群（横並び） -->
    <div class="header-links">
        @if(Auth::check() && Auth::user()->hasVerifiedEmail())
            <form class="header-btn" action="/logout" method="post">
                @csrf
                <button class="header-auth" type="submit">ログアウト</button>
            </form>
        @else
            @if(!Auth::check())
                <a class="header-auth" href="{{ route('login') }}">ログイン</a>
            @else
                <a class="header-auth" href="{{ route('verification.notice') }}">ログイン</a>
            @endif
        @endif
        <a class="header-auth header-mypage" href="/mypage">マイページ</a>
        <a href="{{ route('sell.create') }}" class="sell-btn">出品</a>
    </div>

    <!-- ハンバーガー（タブレット・スマホ用） -->
    <button class="menu-toggle" id="menu-toggle" aria-expanded="false" aria-controls="header-menu">☰</button>

    <!-- ハンバーガーメニュー（開いたときに表示） -->
    <nav class="header-menu" id="header-menu" aria-hidden="true">
        @if(Auth::check() && Auth::user()->hasVerifiedEmail())
            <form class="header-btn" action="/logout" method="post">
                @csrf
                <button class="header-auth" type="submit">ログアウト</button>
            </form>
        @else
            @if(!Auth::check())
                <a class="header-auth" href="{{ route('login') }}">ログイン</a>
            @else
                <a class="header-auth" href="{{ route('verification.notice') }}">ログイン</a>
            @endif
        @endif
        <a class="header-auth header-mypage" href="/mypage">マイページ</a>
        <a href="{{ route('sell.create') }}" class="sell-btn">出品</a>
    </nav>
</div>
    
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('menu-toggle');
    const menu = document.getElementById('header-menu');

    if (!toggle || !menu) return;

    toggle.addEventListener('click', () => {
        menu.classList.toggle('active');
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!expanded));
        menu.setAttribute('aria-hidden', String(expanded));
    });

    // メニュー外クリックで閉じる
    document.addEventListener('click', (e) => {
        if (!menu.classList.contains('active')) return;
        if (menu.contains(e.target) || toggle.contains(e.target)) return;
        menu.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        menu.setAttribute('aria-hidden', 'true');
    });
});
</script>
@endsection

