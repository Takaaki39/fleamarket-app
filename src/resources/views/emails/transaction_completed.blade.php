{{ $progress->user->name }} 様

以下の取引が完了しました。

商品名：{{ $progress->item->name }}
購入者：{{ $buyer->name }}

取引画面より内容をご確認ください。

--
{{ config('app.name') }}