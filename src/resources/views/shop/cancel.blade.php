@extends('layout.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/shop/message.css')}}">
@endsection

@section('content')
<div class="content">
    <div class="message">
        <h1>支払いがキャンセルされました。</h1>
        <a href="/">トップに戻る</a>
    </div>
</div>
@endsection