<?php

/*
|--------------------------------------------------------------------------
| 事業者情報（特定商取引法に基づく表記 / 利用規約 / プライバシーポリシー用）
|--------------------------------------------------------------------------
|
| 有料サービス（サブスクリプション）を提供する「運営者」の情報。
| テナント(Room)ごとではなくプラットフォーム全体で1つ。すべて .env で設定する。
| 未設定の項目は各ページで「—（未設定）」と表示され、スーパー管理者には警告が出る。
|
*/

return [

    // サービスの名称（画面表示・規約本文で使う）
    'service_name' => env('LEGAL_SERVICE_NAME', 'CtoS'),

    // 事業者名（法人名または個人事業主の氏名／屋号）
    'operator' => env('LEGAL_OPERATOR_NAME', ''),

    // 運営統括責任者（代表者氏名）
    'representative' => env('LEGAL_REPRESENTATIVE', ''),

    // 所在地（登記上の住所。個人事業主で非公開にしたい場合は「請求があれば遅滞なく開示します」運用も可）
    'address' => env('LEGAL_ADDRESS', ''),

    // 電話番号（同上。表示しない場合は問い合わせ窓口を明記）
    'phone' => env('LEGAL_PHONE', ''),

    // 電話受付時間
    'phone_hours' => env('LEGAL_PHONE_HOURS', '平日 10:00〜17:00（土日祝を除く）'),

    // 問い合わせ先メールアドレス
    'email' => env('LEGAL_EMAIL', env('MAIL_FROM_ADDRESS', '')),

    // 追加手数料等（サーバー利用に必要な通信料は利用者負担、など）
    'extra_fees' => env('LEGAL_EXTRA_FEES', 'インターネット接続に必要な通信料はお客様のご負担となります。'),

    // 各種ポリシーの最終改定日（YYYY-MM-DD）
    'terms_updated' => env('LEGAL_TERMS_UPDATED', '2026-09-02'),
    'privacy_updated' => env('LEGAL_PRIVACY_UPDATED', '2026-09-02'),
    'tokushoho_updated' => env('LEGAL_TOKUSHOHO_UPDATED', '2026-09-02'),

    // 準拠法・合意管轄裁判所
    'jurisdiction' => env('LEGAL_JURISDICTION', '東京地方裁判所'),
];
