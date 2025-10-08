@extends('layout.app')
@extends('layout.header')

@section('css')
<link rel="stylesheet" href="{{asset('css/shop/purchase.css')}}">
@endsection

@section('content')
<main class="container">
    <div class="left-column">
        <section class="product-info">
            <div class="product-image">
                <img src="{{ $item->img_url }}" alt="商品画像" class="product-image">
            </div>
            <div class="product-details">
                <h2 class="product-name">{{$item->name}}</h2>
                <p class="product-price">¥{{$item->price_label}}</p>
            </div>
        </section>

        <section class="payment-shipping">
            <div class="payment">
                <h3>支払い方法</h3>
                <select id="payment-select" name="payment">
                    <option value="">選択してください</option>
                    <option value="1">クレジットカード</option>
                    <option value="2">コンビニ払い</option>
                </select>
            </div>

            <div class="shipping">
                <h3>配送先</h3>
                <p>〒 {{$delivery['postal_code']}}<br>{{ $delivery['address'] .' '. $delivery['building'] }}</p>
                <a href="{{ route('purchase.address.edit', $item->id) }}" class="change-link">変更する</a>
            </div>
        </section>
    </div>

    <aside class="summary">
        <form id="purchase-form" action="{{ route('purchase.pay', $item->id) }}" method="post">
            @csrf
            <input type="hidden" id="payment-method-hidden" name="payment">
            <input type="hidden" name="postal_code" value="{{$delivery['postal_code']}}">
            <input type="hidden" name="address" value="{{$delivery['address']}}">
            <input type="hidden" name="building" value="{{$delivery['building']}}">

            <table>
                <tr>
                    <td>商品代金</td>
                    <td>¥{{$item->price_label}}</td>
                </tr>
                <tr>
                    <td>支払い方法</td>
                    <td id="summary-payment">未選択</td>
                </tr>
            </table>
            @error('payment')
                <p class="error">{{ $message }}</p>
            @enderror
            @error('address')
                <p class="error">{{ $message }}</p>
            @enderror
            <button id="buy-btn" class="buy-btn" type="submit">購入する</button>
        </form>
    </aside>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const paymentSelect = document.getElementById('payment-select');
        const summaryPayment = document.getElementById('summary-payment');
        const hiddenInput = document.getElementById('payment-method-hidden');
        //const buyBtn = document.getElementById('buy-btn');

        paymentSelect.addEventListener('change', () => {
            const value = paymentSelect.value;
            let label = '';

            if (value === '1') label = 'クレジットカード';
            else if (value === '2') label = 'コンビニ払い';
            else label = '未選択';

            // summaryに反映
            summaryPayment.textContent = label;

            // hidden inputを更新
            hiddenInput.value = value;

            // 選択がされている場合のみ購入ボタンを有効化
            //buyBtn.disabled = (value === '');
        });
    });
</script>
@endsection
