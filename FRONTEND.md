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

## アクセス制御

| 対象 | 条件 | 実装 |
|---|---|---|
| `/admin`（Filament） | 現在サイトの**管理員**（`ninshou = -1`）or スーパー管理者 | `Member::canAccessPanel()` |
| `/tasks/*` `/wbs/*` `/surveys/*` | 現在サイトの**プロジェクト参加者**（`ninshou = 1` or `-1`）or スーパー管理者 | `EnsureProjectMember` ミドルウェア + `Member::isProjectMemberOf()`。Livewire の即時編集メソッドにも `guardWrite()` |
| `/mypage` | 誰でも（要ログイン）。非参加者には `mypage-lite`（機能が使えない旨の案内）を表示 | `MypageController` で分岐 |
| 公開フロント（`/` `/news` `/contents` `/faq` `/contact`） | 誰でも | — |

- `ninshou = 0` = コンテンツ閲覧のみの会員。PM機能は不可。旧ASP の各ページ冒頭 `<%ninshou=",1,"%>` + `chkusr.asp` に対応。
- **レコード単位のアクセス制御は無し**（旧ASP同様、参加者なら他人のタスクも編集・削除できる協働ツール）。
- nav は `isProjectMemberOf()` で業務系リンクを出し分け。

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
| `/tasks/{kind}`（todo/problem/risk/product/routinework） | `Livewire\Member\TaskList` + `Member\TaskController` | todo.asp / Problem.asp / Risk.asp / product.asp / RoutineWorkList.asp | 一覧（フィルタ・キーワード検索・列ソート・20件/頁、**一覧上でステータス／担当者を `<select>` で即時変更、✪ で「本日のタスク」(`dotoday`) トグル**）／詳細／新規起票・編集・論理削除（`delete_to=1`）。`{kind}function` が無いサイトは 404。`App\Support\TaskKind` の `features` で任意フィールド（期限/チーム/状況/完了基準/承認者/内容/ステージ/責任者/today）を出し分け。product は期限なし、routinework は `actiondate`（表示「実施日」）。Mypage の集計値からドリルダウン（todo/problem/risk） |
| `/wbs` `/wbs/{id}` | `Member\WbsController` | wbs.asp | 階層ツリー表示（`Wbs::tree()` で全件から親子組み立て、`father_id` null/0 がルート、`member.partials.wbs-node` 再帰）＋ 詳細（子タスク一覧付き）。**閲覧のみ**。`wbsfunction` 必須 |
| `/surveys` `/surveys/{id}` `/surveys/{id}/answer` | `Member\SurveyController` | SurveyList_My.asp / Survey.asp | 回答可能なサーベイ一覧（open かつ選択肢あり、回答済み/未回答/受付終了バッジ）／回答フォーム（`selectable_numbers` で radio/checkbox）／集計結果（棒グラフ）。回答は `survey_choice_results`（選択ごと1行）＋ `survey_reply_lists`（回答済みマーカー）をトランザクションで。`surveyfunction` 必須 |

## モデルのスコープ

- `NewsItem::scopePublished()` = `newsdate <= now`、`scopeListingOrder()` = istop→newsdate→id
- `Content::scopePublished()` = `ok = 1`、`scopeListingOrder()` = junban→adddatetime→id
- `ContentSort::scopePublicVisible()` = `ninshou is null or 0`

## 未実装（旧ASPの主要導線の残り）

- 会員登録・ログイン画面の日本語化（Breeze 雛形のまま英語）
- **change（変更管理）は保留**: `statuses`/`categories` に `change` kind が無く、`change_requests` は1件のみ。ステータス体系が未定義なので画面化を見送り
- routinework: `routine_works`（定例作業の定義／繰り返しルール）からの `routine_work_lists` 自動生成は未。一覧の閲覧・編集のみ
- **WBS の編集・並び替え・階層操作は未実装**（v1 は閲覧のみ）
- サーベイの作成・締切変更は管理側（未実装）。`specify_yn`（記名アンケート＝誰がどれに投票したか表示）も未対応
- タスクの担当変更・状況更新の簡易操作（一覧から直接。旧ASP の ✪「本日のタスク」トグルも）
- レコード単位のアクセス制御（旧ASP同様「参加者なら誰でも編集可」を踏襲。person_do/maker ベースの制限を入れるかは要検討）
- Mypage 集計 → 一覧のドリルダウンは todo/problem/risk のみ（wbs は一覧未実装、routineGrid のリンクも未）
- 一覧の即時編集は status / person_do / dotoday のみ。期限・チーム等は詳細の編集フォームから
- `checkfunction_F` 相当は `Room::hasFunction()`。お問い合わせ・タスク一覧の nav / 404 で使用。
  Mypage 本体のパネル出し分けはまだ（全パネル表示）
- お問い合わせの「入力内容確認」ステップ（旧 otoi2.asp）は省略し1画面に。必要なら後で追加
- 業務系（todo / Risk / Problem / Product / RoutineWork / Change / wbs / Survey）の会員向け画面
- コンテンツへのコメント（`content_comments`）、掲示板（`guestbooks`）
- カテゴリの階層表示（現状フラット。`content_sorts.father_id` の親子は未使用）
- サイトごとのテーマカラー（`rooms.sitecolor` = gold/spring/css-orange）
- 画像・添付（`files`）→ S3/R2 前提なので後回し
