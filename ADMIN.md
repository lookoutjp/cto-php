# 管理画面（Filament）

旧ASPは各ページに管理員モード（ページ上に編集/非表示/並び替えボタンが浮かぶ）があったが、
新サイトでは意図的に採用していない。管理は **Filament（`/admin`）に一本化**し、
公開フロント・会員画面からは `managesSite()` を満たすサイト管理員にだけ「管理画面」への
リンクを表示する（`FRONTEND.md` 参照）。

## ブランディング（`AdminPanelProvider`）

- ロゴ: `resources/views/filament/partials/brand-logo.blade.php` が現在サイトの
  `rooms.logo`（`<x-site-logo>` 経由、未設定なら既定のSVGマーク）を表示。クリックすると
  `->homeUrl(route('home'))` で公開フロントのトップへ戻る（Filament自身のダッシュボードには戻らない）
- `Widgets\FilamentInfoWidget` は削除済み（バージョン番号・ドキュメント/GitHubへのリンクカード）
- ユーザーメニュー（右上のアバター）に `->userMenuItems()` で「マイページへ戻る」
  （`route('dashboard')` = `/mypage`）を追加

## 日本語ラベル

### ナビゲーション（サイドバーの項目名）

各 Resource に `protected static ?string $navigationLabel`（および `$modelLabel` /
`$pluralModelLabel`）を設定済み。34 Resource すべてに対応。新しい Resource を作る際は
同様に日本語ラベルを設定すること（`php artisan make:filament-resource` の既定は英語のクラス名）。

### フォーム・テーブルの各フィールド

`App\Support\FieldLabels::ja('列名')` が旧Access由来のカラム名（例: `junban` `father_id`
`site_joutai`）を日本語ラベルに変換する辞書。各 Resource の `::make('列名')` 直後に
`->label(FieldLabels::ja('列名'))` を付けている（718件、スクリプトで一括生成）。

- 辞書に無い列名はそのまま返す（表示は崩れないが英語のまま）。新しいカラムを使うときは
  `FieldLabels::MAP` に追記する
- 同じカラム名でもテーブルによって意味が違う場合がある（例: `introduce` は「紹介文」で統一。
  会員の自己紹介にもカテゴリの説明文にも使われるため、どちらでも通じる訳語を選んでいる）
- 一部のフィールドは辞書適用後に手動で `->label(...)` を追加しているため2重になっていた
  （`id`→'ID'、`FileItemResource` のいくつかのカラム等）。重複は除去済みで、後から追加した
  手動ラベルが優先される

### 新しい Resource を作るとき

`php artisan make:filament-resource Xxx` で生成した直後に:

```php
use App\Support\FieldLabels;

// クラス冒頭
protected static ?string $navigationLabel = '日本語名';
protected static ?string $modelLabel = '日本語名';
protected static ?string $pluralModelLabel = '日本語名';

// 各フィールド
Forms\Components\TextInput::make('column')->label(FieldLabels::ja('column')),
```

## 管理場所の早見表（`FRONTEND.md` にも同じ表あり）

| 何を管理するか | 場所 |
|---|---|
| トップページのボタン列 | `/admin/top-menus` |
| 左サイドバーのカテゴリ（順序=`junban`、公開範囲=`ninshou`、外部リンク=`link`） | `/admin/content-sorts` |
| ニュース | `/admin/news-items` |
| サイト設定（ロゴ・トップ画像・サイト名・SMTP等） | `/admin/rooms` |
