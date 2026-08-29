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

`App\Http\Middleware\ResolveCurrentSite` は「管理画面」と「公開フロント」で対象サイトの決め方を変える:

| コンテキスト | 対象サイト集合 | 解決順 | session キー |
|---|---|---|---|
| /admin + Member | `manageableSiteIds()`（管理員/スーパー管理者） | session → 先頭。無ければ `denyAll()` | `admin_site_id` |
| 公開フロント + Member | `accessibleSiteIds()`（所属サイト） | ホスト → session → 既定サイト → 先頭 | `site_id` |
| 公開フロント + ゲスト | 全 rooms | `Room::resolveSiteIdFromHost()` → 既定サイト | — |

- 管理画面コンテキストの判定 = リクエストパスが `admin/*`、または Referer が `/admin` 始まり
  （livewire/update は web ミドルウェア経由で管理画面からも飛んでくるため）
- どの経路でも必ず `CurrentSite::set()`（またはゲストは host 解決）するので、
  `BelongsToSite` のスコープが常に効く（他サイトのデータは見えない）

## 実装済みページ

| URL | 実装 | 旧ASP | 内容 |
|---|---|---|---|
| `/` | `Public\HomeController` + `public.home` | index.asp | サイト紹介（`rooms.siteintro`）+ 最新ニュース5件 |
| `/news` | `Livewire\Public\NewsIndex` | news.asp | 一覧・タイトル検索・32件/ページ。`newsdate <= now` のみ、`istop` 優先 |
| `/news/{id}` | `Public\NewsController@show` | newsdetail.asp | 本文HTML + 前後リンク。未公開/他サイトは404。clicks++ |
| `/contents` | `Public\ContentController@index` | contents.asp | 公開カテゴリ（`ninshou` null/0）ごとに公開コンテンツ（`ok=1`） |
| `/contents/{id}` | `Public\ContentController@show` | ContentDetail.asp | 本文HTML。非公開/非公開カテゴリ/他サイトは404。clicks++ |
| `/faq` | `Public\FaqController@index` | faq.asp | 全FAQ（`<details>` で開閉）+ キーワード検索 |
| `/mypage` | `MypageController`（route 名 `dashboard`） | Mypage.asp | ログイン後の入口。本日の計画作業 / 管理タスク対応状況（todo・課題・リスク・WBS × 新規/接近/遅延/期限未設定）/ 定例作業対応状況。集計は `App\Support\TaskDashboard` |
| `/contact` `/contact/thanks` | `Public\InquiryController` | otoi.asp / otoi2 / otoi3 | お問い合わせフォーム。会員はプロフィールから自動入力。保存（`inquiries`、`site_id` 自動）＋ 受付確認メール（本人）＋ 新着通知メール（`rooms.site_mail`）。番号は `T{id}`。`rooms.function_list` に `otoiawasefunction` が無いサイトは 404（nav リンクも非表示） |

## モデルのスコープ

- `NewsItem::scopePublished()` = `newsdate <= now`、`scopeListingOrder()` = istop→newsdate→id
- `Content::scopePublished()` = `ok = 1`、`scopeListingOrder()` = junban→adddatetime→id
- `ContentSort::scopePublicVisible()` = `ninshou is null or 0`

## 未実装（旧ASPの主要導線の残り）

- 会員登録・ログイン画面の日本語化（Breeze 雛形のまま英語）
- Mypage の数値からのドリルダウン（旧ASP は `todo.asp?view=mynew` 等へリンク。会員向け業務画面がまだ無いのでリンクは未実装）
- `checkfunction_F` 相当は `Room::hasFunction()` として実装済み（お問い合わせで使用）。
  Mypage・その他ページへの適用は未（現状 Mypage は全パネル表示）
- お問い合わせの「入力内容確認」ステップ（旧 otoi2.asp）は省略し1画面に。必要なら後で追加
- 業務系（todo / Risk / Problem / Product / RoutineWork / Change / wbs / Survey）の会員向け画面
- コンテンツへのコメント（`content_comments`）、掲示板（`guestbooks`）
- カテゴリの階層表示（現状フラット。`content_sorts.father_id` の親子は未使用）
- サイトごとのテーマカラー（`rooms.sitecolor` = gold/spring/css-orange）
- 画像・添付（`files`）→ S3/R2 前提なので後回し
