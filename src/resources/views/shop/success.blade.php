@extends('layout.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/shop/message.css')}}">
@endsection

@section('content')
<div class="content">
    <div class="message">
        <h2>支払いが完了しました！</h2>
        <p>ご購入ありがとうございます。</p>
        <a href="/">トップに戻る</a>
    </div>
</div>
@endsection