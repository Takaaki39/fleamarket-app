@extends('layout.app')
@extends('layout.header')

@section('css')
<link rel="stylesheet" href="{{asset('css/profile/edit.css')}}">
@endsection

@section('content')
<main class="profile-container">
    <h1>プロフィール設定</h1>

    <!-- フォーム -->
    <form class="profile-form" action="/mypage/profile" method="post" enctype="multipart/form-data" novalidate>
        @csrf

        <div class="profile-image">
            <div class="icon" id="preview">
                @if($user->icon_img)
                    <img src="{{ asset('storage/' . $user->icon_img) }}" alt="プロフィール画像">
                @endif
            </div>
            <label class="btn-select-img">
                画像を選択する
                <input type="file" name="icon_img" id="icon_img" accept="image/*" hidden>
            </label>
        </div>

        <label for="username">ユーザー名</label>
        <input type="text" id="username" name="username" placeholder="ユーザー名" value="{{$user->name}}">
        @error('username')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="zipcode">郵便番号</label>
        <input type="text" id="zipcode" name="zipcode" placeholder="郵便番号" value="{{$user->postal_code}}">
        @error('zipcode')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="address">住所</label>
        <input type="text" id="address" name="address" placeholder="住所" value="{{$user->address}}">
        @error('address')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="building">建物名</label>
        <input type="text" id="building" name="building" placeholder="建物名" value="{{$user->building}}">
        @error('building')
            <div class="error">{{ $message }}</div>
        @enderror

        <button type="submit" class="btn-update">更新する</button>
    </form>
</main>

<script>
    document.getElementById('icon_img').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview');
            preview.innerHTML = `<img src="${e.target.result}" alt="プレビュー">`;
        }
        reader.readAsDataURL(file);
    });
</script>
@endsection
