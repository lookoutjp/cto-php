# 公開フロントエンド（旧ASP → Blade + Livewire 移植）

旧ASP（`C:\inetpub\wwwroot\cto-asp`、約187ページ）の会員・来訪者向け画面を
Laravel の Blade + Livewire + Tailwind で作り直す。管理画面は Filament（`/admin`）。

## スタック

- Blade（サーバーレンダリング）+ Livewire 3（検索・ページネーション等の動的部分）
- Tailwind v3 + `@tailwindcss/forms` + `@tailwindcss/typography`（`prose` で本文HTMLを整形）
- テーマカラー: `rooms.sitecolor`（旧ASP `css/inc_Stytle.asp` の gold/spring/blue…13種）を
  `App\Support\ThemePalette` が CSS変数 `--brand{,-dark,-light,-bg,-fg}` ＋ `--brand-name`
  （`rooms.sitename_color`）に変換。3レイアウト（public / app / guest）が `<head>` で
  `partials/theme-style` を出力し、Tailwind の `brand` カラー（`bg-brand` `text-brand-fg`
  `hover:bg-brand-dark` `ring-brand` 等）がそれを参照。ボタン・アクティブnav・フォーカスリング・
  ヘッダーのアクセントバー・サイト名に適用。旧Breeze の indigo アクセントは全廃
- ロケール: `APP_LOCALE=ja`（`config/app.php` の既定も `ja`、`fallback_locale=en`）。
  `lang/ja.json`（Breeze 認証・プロフィール画面の `__()` 文言）＋ `lang/ja/{validation,auth,passwords,pagination}.php`。
  業務系の画面は Blade に直接日本語を書いており `__()` は使っていない。日付は Carbon の
  `isoFormat('YYYY年M月D日')` が基本（ロケール非依存）、曜日等が要る箇所は `ddd` で ja 出力される
- レイアウト: `resources/views/components/layouts/public.blade.php`（匿名コンポーネント）
  - Livewire からは `->layout('components.layouts.public', ['title' => '...'])`
  - Blade からは `<x-layouts.public :title="...">`
  - `$site`（現在の `Room`）は `AppServiceProvider` の View Composer が
    `components.layouts.public` / `public.*` / `livewire.public.*` に注入

## アクセス制御

| 対象 | 条件 | 実装 |
|---|---|---|
| `/admin`（Filament） | 現在サイトの**管理員**（`ninshou = -1`）or スーパー管理者 | `Member::canAccessPanel()` |
| `/tasks/*` `/wbs/*` `/surveys/*` `/board/*` | 現在サイトの**プロジェクト参加者**（`ninshou = 1` or `-1`）or スーパー管理者 | `EnsureProjectMember` ミドルウェア + `Member::isProjectMemberOf()`。Livewire の即時編集メソッドにも `guardWrite()` |
| コンテンツのコメント投稿 | 現在サイトのプロジェクト参加者（閲覧は誰でも） | `Livewire\Public\ContentComments::submit()` の `abort_unless` |
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
| `/` | `Public\HomeController` + `public.home` | index.asp | サイト紹介（`rooms.siteintro`）+ 最新ニュース5件 ＋ おすすめコンテンツ（`contents.recommend=1`、`osusumecontentsfunction`）＋ 人気コンテンツ（`clicks` 降順、`ninkicontentsfunction`） |
| `/news` | `Livewire\Public\NewsIndex` | news.asp | 一覧・タイトル検索・32件/ページ。`newsdate <= now` のみ、`istop` 優先 |
| `/news/{id}` | `Public\NewsController@show` | newsdetail.asp | 本文HTML + 前後リンク。未公開/他サイトは404。clicks++ |
| `/contents` | `Public\ContentController@index` | contents.asp | 公開カテゴリ（`ninshou` null/0）ごとに公開コンテンツ（`ok=1`） |
| `/contents/{id}` | `Public\ContentController@show` | ContentDetail.asp | 本文HTML。非公開/非公開カテゴリ/他サイトは404。clicks++ |
| `/faq` | `Public\FaqController@index` | faq.asp | 全FAQ（`<details>` で開閉）+ キーワード検索 |
| `/manager` | `Public\SitePageController@managerWords` | managerwords.asp | `rooms.managerwords`（HTML）をそのまま表示。見出しは `rooms.manager_shouko`（無ければ「管理員」）。`managerwordsfunction` 必須 |
| `/links` | `Public\SitePageController@links` | friendlink 系 | 管理員が承認したリンク（`links.allow = 1`）の一覧。承認は Filament `LinkItemResource`。`friendlinkfunction` 必須 |
| `/members` | `Member\MemberListController` | memberlist.asp | サイト参加者（`member_room.ninshou` 1/-1）の一覧。名前・自己紹介・オンライン表示。`memberlistfunction` 必須 |
| `/messages` `/messages/sent` `/messages/create` `/messages/{id}` | `Member\MessageController` | Member_MessageSend.asp | 社内メッセージ（伝言）。受信箱／送信箱／作成／詳細（受信者は開くと既読）／削除（送信者=`delete_from`、受信者=`delete_to` の論理削除）。宛先はサイト参加者のみ。`dengonfunction` 必須 |
| `/mypage` | `MypageController`（route 名 `dashboard`） | Mypage.asp | ログイン後の入口。本日の計画作業 / 管理タスク対応状況（todo・課題・リスク・WBS × 新規/接近/遅延/期限未設定）/ 定例作業対応状況。集計は `App\Support\TaskDashboard` |
| `/contact` `/contact/thanks` | `Public\InquiryController` | otoi.asp / otoi2 / otoi3 | お問い合わせフォーム。会員はプロフィールから自動入力。保存（`inquiries`、`site_id` 自動）＋ 受付確認メール（本人）＋ 新着通知メール（`rooms.site_mail`）。番号は `T{id}`。`rooms.function_list` に `otoiawasefunction` が無いサイトは 404（nav リンクも非表示） |
| `/tasks/{kind}`（todo/problem/risk/product/routinework） | `Livewire\Member\TaskList` + `Member\TaskController` | todo.asp / Problem.asp / Risk.asp / product.asp / RoutineWorkList.asp | 一覧（フィルタ・キーワード検索・列ソート・20件/頁、**一覧上でステータス／担当者を `<select>` で即時変更、✪ で「本日のタスク」(`dotoday`) トグル**）／詳細／新規起票・編集・論理削除（`delete_to=1`）。`{kind}function` が無いサイトは 404。`App\Support\TaskKind` の `features` で任意フィールド（期限/チーム/状況/完了基準/承認者/内容/ステージ/責任者/today）を出し分け。product は期限なし、routinework は `actiondate`（表示「実施日」）。Mypage の集計値からドリルダウン（todo/problem/risk） |
| `/wbs` `/wbs/{id}` ほか | `Member\WbsController` | wbs.asp / WbsAdd.asp / WbsDetail.asp | 階層ツリー表示 ＋ 詳細 ＋ 追加・編集・論理削除 ＋ D&D並び替え（下記）＋ `/wbs/check` 計画チェック ＋ **`/wbs/schedule` スケジュール計算（CPM）**。`wbsfunction` 必須 |
| `/wbs/check` | 旧 WBS_CheckFromTo/CheckDays | サマリ項目の計画工数/開始/完了 vs 配下タスク集計。超過=赤・余裕=黄・未計画=灰 |
| `/wbs/schedule` `?root={id}` `?calendar=working\|calendar` | 新機能 | `App\Support\WbsScheduler`（CPM）。依存タイプ FS/SS/FF/SF ＋ リード/ラグ（`relations.dep_type` / `lag_days`）を考慮し `tododays` で ES/EF を前進計算 → 後退計算で LS/LF/フロート → フロート0以下 = クリティカルパス。日数の数え方は `App\Support\WorkCalendar` で「稼働日」（土日＋`holidays` を除外, 既定）/「暦日」を切替。非wbs先行はその `duedate` を固定制約に。循環は検出してエラー。「計算結果を反映」で `godate`/`duedate` を書き戻す（`?root=` があればその配下のみ、サマリ項目は任意でロールアップ更新） |
| `/wbs/holidays` | 新機能 | `holidays` テーブル（`site_id` 自動）の追加・削除。スケジュール計算の「稼働日」モードで除外される休日。土日は自動で非稼働日 |
| `/wbs/load` `?capacity=N` | 新機能 | `App\Support\WbsLoadAnalyzer`。各リーフ WBS の `tododays` を期間（着手予定〜期限）の稼働日に均等配分し、担当者 × ISO週 で合計。週あたり稼働可能日数（既定 5、3〜6 で切替）超の週を過負荷として色分け＋内訳表示。簡易リソース平準化の第一歩（自動再配置はしない） |
| 関連タスクパネル（`<livewire:member.relations-panel>`） | WBS詳細・タスク詳細に埋め込み | WbsDetail の relation 部分 | 先行/後続/関連（`relations` テーブル、`rtype` = `fromto`/`relation`）の一覧・追加・論理削除。先行/後続は依存タイプ（FS/SS/FF/SF）とラグ日数も指定可（一覧に「SS +2d」等を表示）。kind をまたいで（wbs↔todo等）リンク可。先行タスクの完了予定 > このタスクの開始予定 なら ⚠ 警告。`App\Support\Relations` + `App\Support\TaskRef` |
| `/surveys` `/surveys/{id}` `/surveys/{id}/answer` | `Member\SurveyController` | SurveyList_My.asp / Survey.asp | 回答可能なサーベイ一覧（open かつ選択肢あり、回答済み/未回答/受付終了バッジ）／回答フォーム（`selectable_numbers` で radio/checkbox）／集計結果（棒グラフ）。回答は `survey_choice_results`（選択ごと1行）＋ `survey_reply_lists`（回答済みマーカー）をトランザクションで。`surveyfunction` 必須 |
| `/surveys/manage` `/surveys/create` `/surveys/{id}/edit` ほか | `Member\SurveyController@manage/create/store/edit/update/destroy/toggleOpen` | SurveyList_Mytask.asp / Survey_new.asp / Surveyedit_son.asp | サーベイの作成・編集・締切／再開（`open_yn`）・論理削除（`delete_to=1`）。一覧は「自分が作成したもの」＋管理員は全件。選択肢は Alpine の可変行（タイトル＋説明）。**回答が付いた後は選択肢を編集不可**（メタ情報は可）。回答期限は `endOfDay` で保存 |
| `/board` `/board/categories/{id}` `/board/threads/{id}` ほか | `Member\BoardController` | meetlist.asp / meet.asp / meet_disp.asp / meetadd.asp / meet_re.asp | 掲示板。`/board`=コミュニティ一覧（`guestbook_categories`。id=1 は「サイト掲示板」既定カテゴリで一覧では別枠表示）／`categories/{id}`=スレッド一覧（`guestbooks` の `parent='0'`、返信数・管理員返信バッジ、10件/頁）／`threads/{id}`=スレッド詳細（本文＋`revert` 管理員返信＋`parent`/`top`/`space_num` の自己参照ツリーで返信をインデント表示、各ノードに Alpine 開閉式の返信フォーム）／`categories/{id}/new` 新規スレッド。返信は `top`=スレッド先頭ID・`space_num`=親+1 を自動セット。`create_date` に投稿時刻。旧Access由来の空行は `Guestbook::scopeReal()` で除外。管理員返信の編集は Filament（`GuestbookResource`）。`freeguestbookfunction` 必須 |
| コンテンツのコメント（`<livewire:public.content-comments>`） | 公開コンテンツ詳細に埋め込み | ContentCommentSon.asp / ContentComment_Write.asp / ContentCommentList.asp | `commentfunction` かつ `contents.commentok=1` のとき表示。`content_comments` を新しい順・10件/頁。閲覧は誰でも、投稿はプロジェクト参加者のみ（未ログインは「ログインすると…」、`ninshou=0` は不可の旨）。`time` は旧データにあわせ `Y/m/d H:i:s` 文字列で保存 |

## モデルのスコープ

- `NewsItem::scopePublished()` = `newsdate <= now`、`scopeListingOrder()` = istop→newsdate→id
- `Content::scopePublished()` = `ok = 1`、`scopeListingOrder()` = junban→adddatetime→id
- `ContentSort::scopePublicVisible()` = `ninshou is null or 0`

## 未実装（旧ASPの主要導線の残り）

- change（変更管理）: `statuses` に `kind='change'`（起票→調査中→判定待ち→承認待ち→対応中→完了／却下／保留）をマイグレーションで投入し、`TaskKind` に `change` を追加（`features` に `changedetail` = 発生日・工数見積・判定結果・完了日・影響範囲・対応内容・却下理由）。ステージは既存の `categories.kind='stage'` を流用。一覧/CRUD は汎用の TaskList / TaskController。関連タスクパネルは未対応（`TaskRef::KINDS` に未登録）
- routinework: `App\Support\RoutineWorkGenerator` が `routine_works`（繰り返しルール: circle = day/week/month/year、`circle_number`）から `routine_work_lists` を生成。会員は `/routinework/generate`（旧 RoutineWorkMake.asp）で期間指定、cron 用に `php artisan routinework:generate --days=N [--site=]`。同一マスター×同一 actiondate は重複作成しない
- スケジューリング: FS/SS/FF/SF ＋ リード/ラグ ＋ 稼働日カレンダー（休日 `holidays`）対応済み。リソース平準化は `/wbs/load` の負荷分析（過負荷週の検出）まで。自動でのタスク再配置は未
- `relations` の既存データはテスト混じりで重複・削除済み参照あり（パネルは「(削除済み #N)」と表示してグレースフルに処理）
- スケジュール計算はプレビュー→明示的な「反映」でのみ DB を書き換える（自動再計算はしない）
- WBS D&D は SortableJS の `forceFallback: true`（ポインタイベント）。タッチ端末での操作性は要確認
- サーベイの作成・編集・締切は `/surveys/manage` 系で対応済み。`specify_yn`（記名アンケート＝誰がどれに投票したか集計で表示）はフラグ保存のみで、集計画面での本人表示は未対応
- 掲示板: コミュニティの参加者制限は `GuestbookCategory::allowsMember()`（旧 `guestbook_categories.member` の `||id||` リスト、空 or サイト掲示板 id=1 は無制限、管理員/スーパー管理者は常に可）で適用。一覧は許可カテゴリのみ表示、直リンクは 403。管理員返信（`revert`）の入力・投稿削除・コミュニティ CRUD は Filament 側。旧 CKEditor リッチ入力は素の textarea に
- コンテンツのコメント: 削除・管理は Filament（`ContentCommentResource`）。旧 `Contentcomment_about.asp`（プライバシーポリシー）リンクは省略
- タスクの担当変更・状況更新の簡易操作（一覧から直接。旧ASP の ✪「本日のタスク」トグルも）
- レコード単位のアクセス制御（旧ASP同様「参加者なら誰でも編集可」を踏襲。person_do/maker ベースの制限を入れるかは要検討）
- Mypage 集計 → 一覧のドリルダウンは todo/problem/risk のみ（wbs は一覧未実装、routineGrid のリンクも未）
- 一覧の即時編集は status / person_do / dotoday のみ。期限・チーム等は詳細の編集フォームから
- `checkfunction_F` 相当は `Room::hasFunction()`。nav / 404 のほか、**Mypage 本体も
  `TaskDashboard::enabledKinds()`（todo/problem/risk/wbs/routinework の各 `*function`）で
  パネル・行を出し分け**。全機能オフのサイトでは案内文のみ
- お問い合わせの「入力内容確認」ステップ（旧 otoi2.asp）は省略し1画面に。必要なら後で追加
- カテゴリの階層表示（現状フラット。`content_sorts.father_id` の親子は未使用）
- 画像・添付（`files`）→ S3/R2 前提なので後回し
- オンラインメンバー（`onlinemembersfunction`）、セミナー（`seminarfunction`：テーブル無し）、
  作品公開（`sakuhinkoukaifunction`：`homework_sorts` 空）、バージョン履歴（`sys_versions` 空）は未実装
- 会員個人ページ（旧 memberpage.asp）は未実装（メンバー一覧から個別ページへのリンクなし）
