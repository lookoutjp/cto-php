# 公開フロントエンド（旧ASP → Blade + Livewire 移植）

旧ASP（`C:\inetpub\wwwroot\cto-asp`、約187ページ）の会員・来訪者向け画面を
Laravel の Blade + Livewire + Tailwind で作り直す。管理画面は Filament（`/admin`）。

## スタック

- Blade（サーバーレンダリング）+ Livewire 3（検索・ページネーション等の動的部分）
- Tailwind v3 + `@tailwindcss/forms` + `@tailwindcss/typography`（`prose` で本文HTMLを整形）
- レイアウト: `resources/views/components/layouts/public.blade.php`（匿名コンポーネント）
  - Livewire からは `->layout('components.layouts.public', ['title' => '...'])`
  - Blade からは `<x-layouts.public :title="...">`
  - `$site`（現在の `Room`）は `AppServiceProvider` の View Composer が
    `components.layouts.public` / `public.*` / `livewire.public.*` に注入

## サイト（テナント）解決

`App\Http\Middleware\ResolveCurrentSite`:
- ログイン中 Member → `manageableSiteIds()`（管理画面向け）
- 未ログイン（公開フロント）→ `Room::resolveSiteIdFromHost(HTTP host)`。
  `rooms.sitedomain` のホスト部分と一致するサイト。該当なしは `config('app.default_site')`。
- どの経路でも必ず `CurrentSite::set()` するので `BelongsToSite` のスコープが常に効く
  （＝来訪者が他サイトのデータを見ることはない）

## 実装済みページ

| URL | 実装 | 旧ASP | 内容 |
|---|---|---|---|
| `/` | `Public\HomeController` + `public.home` | index.asp | サイト紹介（`rooms.siteintro`）+ 最新ニュース5件 |
| `/news` | `Livewire\Public\NewsIndex` | news.asp | 一覧・タイトル検索・32件/ページ。`newsdate <= now` のみ、`istop` 優先 |
| `/news/{id}` | `Public\NewsController@show` | newsdetail.asp | 本文HTML + 前後リンク。未公開/他サイトは404。clicks++ |
| `/contents` | `Public\ContentController@index` | contents.asp | 公開カテゴリ（`ninshou` null/0）ごとに公開コンテンツ（`ok=1`） |
| `/contents/{id}` | `Public\ContentController@show` | ContentDetail.asp | 本文HTML。非公開/非公開カテゴリ/他サイトは404。clicks++ |
| `/faq` | `Public\FaqController@index` | faq.asp | 全FAQ（`<details>` で開閉）+ キーワード検索 |

## モデルのスコープ

- `NewsItem::scopePublished()` = `newsdate <= now`、`scopeListingOrder()` = istop→newsdate→id
- `Content::scopePublished()` = `ok = 1`、`scopeListingOrder()` = junban→adddatetime→id
- `ContentSort::scopePublicVisible()` = `ninshou is null or 0`

## 未実装（旧ASPの主要導線の残り）

- 会員登録・ログイン後の導線（Breeze の雛形はあるが日本語化・旧UX合わせは未着手）
- Mypage（`Mypage.asp`）: 自分のタスク・進捗サマリ
- お問い合わせ（`otoi.asp` → `inquiries`）: フォーム送信
- 業務系（todo / Risk / Problem / Product / RoutineWork / Change / wbs / Survey）の会員向け画面
- コンテンツへのコメント（`content_comments`）、掲示板（`guestbooks`）
- カテゴリの階層表示（現状フラット。`content_sorts.father_id` の親子は未使用）
- サイトごとのテーマカラー（`rooms.sitecolor` = gold/spring/css-orange）
- 画像・添付（`files`）→ S3/R2 前提なので後回し
