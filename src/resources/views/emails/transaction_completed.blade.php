<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>取引完了のお知らせ</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333;">

    <p>{{ $transaction->user->name }} 様</p>

    <p>
        以下の取引が完了しました。
    </p>

    <table style="border-collapse: collapse; margin: 20px 0;">
        <tr>
            <th style="text-align: left; padding: 6px 10px; background: #f5f5f5;">
                商品名
            </th>
            <td style="padding: 6px 10px;">
                {{ $transaction->item->name }}
            </td>
        </tr>
        <tr>
            <th style="text-align: left; padding: 6px 10px; background: #f5f5f5;">
                購入者
            </th>
            <td style="padding: 6px 10px;">
                {{ $buyer->name }}
            </td>
        </tr>
    </table>

    <p>
        取引画面より内容をご確認ください。
    </p>

    <hr style="margin: 30px 0;">

    <p>
        {{ config('app.name') }}
    </p>

</body>

</html>