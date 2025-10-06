@extends('layout.app')
@extends('layout.header')

@section('css')
<link rel="stylesheet" href="{{asset('css/shop/address_edit.css')}}">
@endsection

@section('content')
<main class="container">
    <h1 class="title">住所の変更</h1>
    <form class="address-form" action="{{ route('purchase.address.update', $item->id) }}" method="post">
        @csrf
        <div class="form-group">
            <label for="postal">郵便番号</label>
            <input type="text" id="postal" name="postal_code" value="{{$delivery['postal_code']}}">
            @error('postal_code')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group">
            <label for="address">住所</label>
            <input type="text" id="address" name="address" value="{{$delivery['address']}}">
            @error('address')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group">
            <label for="building">建物名</label>
            <input type="text" id="building" name="building" value="{{$delivery['building']}}">
        </div>
        <button type="submit" class="update-btn">更新する</button>
    </form>
</main>
@endsection
