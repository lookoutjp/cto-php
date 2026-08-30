<?php

/*
|--------------------------------------------------------------------------
| 料金プラン
|--------------------------------------------------------------------------
|
| 月額フラット（プランごとに固定額 + 会員数・容量の上限）。課金の単位はテナント（Room）。
| Stripe の Price は JPY・月次で作成し、Price ID を .env に設定する。
| stripe_price_id が空のプランは「契約できない」= 実質 free のみ有効な状態。
|
| limits の値: 整数 = 上限、null = 無制限。
|
*/

return [

    'free' => [
        'name' => 'フリー',
        'description' => '小さなチームで試すための無料プラン。閲覧は無制限、書き込みは上限あり。',
        'price' => 0,
        'stripe_price_id' => null,
        'limits' => [
            'members' => 10,      // member_room の総数（ninshou 問わず）
            'storage_mb' => 200,  // files の合計サイズ（S3/R2 化後に実効）
        ],
    ],

    'standard' => [
        'name' => 'スタンダード',
        'description' => '通常の社内利用向け。',
        'price' => 3000,
        'stripe_price_id' => env('STRIPE_PRICE_STANDARD'),
        'limits' => [
            'members' => 50,
            'storage_mb' => 5000,
        ],
    ],

    'pro' => [
        'name' => 'プロ',
        'description' => '大規模チーム・容量重視。',
        'price' => 10000,
        'stripe_price_id' => env('STRIPE_PRICE_PRO'),
        'limits' => [
            'members' => null,
            'storage_mb' => 50000,
        ],
    ],

];
