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

- `member_room.ninshou` = `-1` 管理員 / `1` 参加者 / `0` コンテンツ閲覧のみ / `NULL` 加入申請中（未承認）。
  旧ASP の各ページ冒頭 `<%ninshou=",1,"%>` + `chkusr.asp` に対応。
- **サイト加入フロー**（`App\Http\Controllers\Member\SiteJoinController`、`/join`）: 既にログイン中の会員が
  別サイト（テナント）へ「加入申請」する。`member_room` に `applied_at` 付き・`ninshou = NULL` の行が作られ、
  `MemberRoom` の `confirmed` グローバルスコープで通常クエリからは除外される（＝メンバー扱いされない）。
  管理員が Filament「会員権限」(`MemberRoomResource`) の **承認**アクション（権限レベルを選択）で `ninshou` +
  `approved_at` を付与、または**却下**で行を削除。承認待ちでも公開コンテンツは閲覧できる（`accessibleSiteIds()` に含む）。
  承認待ち件数はナビにバッジ表示。新規ユーザーの初回登録は従来どおり `/register`。
- 旧ASPの「管理員メニュー」（ページ上に浮かぶ編集/非表示/並び替えボタン群）相当を
  「管理者モード」として復活させている。
  - `App\Support\AdminMode`: サイトごとのON/OFF（session）。`isEnabled()` は判定のみ（権限チェックは呼び出し側）、
    `activeFor(?siteId)` は「ログイン中の会員がそのサイトの管理員 かつ ON」を一括判定する唯一の判定点。
  - `AdminModeController@toggle`（`POST /admin-mode/toggle`）: サイト管理員のみ切替可（他は403）。
  - `$adminMode` は `AppServiceProvider` の View::composer が `components.layouts.public` /
    `public.*` / `livewire.public.*` に注入。個々の公開ページビューでもそのまま使える。
  - ONの間、以下に編集アイコン（→ Filament の該当編集ページへ `?back=` 付き直リンク。保存/削除後は
    元のページへ戻る＝`RedirectsEditBackToOrigin` / `RedirectsCreateBackToOrigin`）と「＋追加」ボタンが出る:
    - フェーズ1: ヘッダーのトップメニュー（`top_menus`）、左サイドバーのカテゴリ（`content_sorts`、D&D並び替えも）
    - フェーズ2: コンテンツ一覧/カテゴリ詳細/記事詳細（記事＝`contents`、カテゴリ＝`content_sorts`、
      「このカテゴリに記事を追加」は `?content_sort=`、「サブカテゴリを追加」は `?father_id=`＝現在カテゴリ
      で事前選択）、コンテンツのコメント（`content_comments`）、
      ニュース一覧/詳細（`news_items`）、FAQ（`faqs`）、リンク集（`link_items`。管理者モードでは未承認 `allow=0` も薄く表示）、
      管理員の言葉 / サイト概要（`rooms` の該当フィールド）
  - 一覧・削除UIそのものはFilament任せで、独自のインライン編集フォームは持たない（工数を抑えるための意図的な設計判断）。
  - 導線用の共通コンポーネント: `<x-admin-edit :href :label :show-label :icon>` / `<x-admin-add :href>`（`back=` を自動付与）。
  - 対象を増やす場合はこのパターン（`$adminMode` フラグ + `<x-admin-edit>` / `<x-admin-add>` +
    対象 Edit/Create ページに `RedirectsEditBackToOrigin` / `RedirectsCreateBackToOrigin`）を横展開する。
  管理そのものは引き続き Filament（`/admin`）に一本化: `top_menus`→`TopMenuResource`、`content_sorts`（並び順=`junban`、
  公開可否=`ninshou`、外部リンク=`link`）→`ContentSortResource`、ニュース→`NewsItemResource`、
  サイト設定（ロゴ・トップ画像・サイト名等）→`RoomResource`。フロント側の導線として、
  サイト管理員（`managesSite()`）には公開ヘッダー・会員ヘッダー・MyMenu に「管理画面」リンクを表示する。
  公開ヘッダー右上（旧 inc_top.asp の会員メニュー相当）は、ログイン中は「名前（会員ID）」の
  ドロップダウン（マイページ / プロフィール / ログアウト）＋ `dengonfunction` 有効時はメッセージ
  アイコン、ゲストは「ログイン」リンク。
  MyMenu 最下部の「管理者メニュー」ブロック（管理員のみ表示）にも、トップメニュー管理・カテゴリ管理への
  ショートカットを置いている
- **レコード単位のアクセス制御は無し**（旧ASP同様、参加者なら他人のタスクも編集・削除できる協働ツール）。
- nav は `isProjectMemberOf()` で業務系リンクを出し分け。

## サイト（テナント）解決

`App\Http\Middleware\ResolveCurrentSite` は「管理画面」と「公開フロント」で対象サイトの決め方を変える:

| コンテキスト | 対象サイト集合 | 解決順 | session キー |
|---|---|---|---|
| /admin + Member | `manageableSiteIds()`（管理員/スーパー管理者） | session → 先頭。無ければ `denyAll()` | `admin_site_id` |
| 公開フロント + Member | `accessibleSiteIds()`（所属サイト）＋ 明示選択サイト | **明示選択(`site_view`)** → ホスト → session → 既定サイト → 先頭 | `site_id` / `site_view` |
| 公開フロント + ゲスト | 全 rooms | **明示選択(`site_view`)** → `Room::resolveSiteIdFromHost()` → 既定サイト | `site_view` |

- **共有ドメインでのテナント公開フロント**: `/{site}/` `/{site}/{path}`（`SitePageController@enter`、ルート名 `site.enter`、
  全ルート定義の最後）で `session('site_view')` に `{site}` を保存し、プレフィックス無しの URL へリダイレクトする。
  以降 `site_view` が「表示中サイト」を最優先で決める（独自ドメイン `rooms.sitedomain` を持たないテナントでも
  `https://cto.jp/miraipmo/` のように開ける）。`site_view` は所属外でも尊重する＝公開コンテンツは誰でも閲覧可、
  管理・PM機能は `managesSite()` / `isProjectMemberOf()` で別途 gate されるので安全。`{site}` 正規表現は
  `admin` / `livewire` 等のインフラ系プレフィックスを除外。
- 管理画面コンテキストの判定 = リクエストパスが `admin/*`、または Referer が `/admin` 始まり
  （livewire/update は web ミドルウェア経由で管理画面からも飛んでくるため）
- どの経路でも必ず `CurrentSite::set()`（またはゲストは host 解決）するので、
  `BelongsToSite` のスコープが常に効く（他サイトのデータは見えない）

## 実装済みページ

| URL | 実装 | 旧ASP | 内容 |
|---|---|---|---|
| `/admin/org-chart` | `App\Filament\Pages\OrgChart` | orgchart.asp | 現在サイトの組織図（体制図）。`levels`（旧 lebel）の `fatherlevel`→`level` 自己参照ツリーを `Level::tree()`（循環は visited で打ち切り）で組み立て、インデント表示。編集は `LevelResource`（`/admin/levels`）。管理員のみ。0 件時は作成導線 |
| `/` | `Public\HomeController` + `public.home` | index.asp | 旧トップページの3カラム構成を再現。ヒーロー（サイト名＋`siteintro`先頭文の一言＋`homepagemainimage`）／左: カテゴリサイドバー（`content_sorts` のトップレベル・`publicVisible`、`link` があれば `LegacyLinkResolver` 経由でリンク、無ければ `/contents`）／中央: サイト紹介本文＋おすすめコンテンツ（`osusumecontentsfunction`）＋人気コンテンツ（`ninkicontentsfunction`）＋最新ニュース5件／右: `rooms.logo` ＋ `manager_shouko`（ラベル）+`webmanager`（名前）。カテゴリ・ロゴ等のデータが無いテナントではその区画ごと非表示になり単純な1カラムに戻る |
| 全ページ共通ヘッダー・左サイドバー | `components.layouts.public` | inc_top.asp / inc_left.asp | `top_menus` をブランド色のボタン列としてヘッダーに表示。左サイドバー「カテゴリ」（`content_sorts` のトップレベル・`publicVisible`）は全公開ページ共通（ホーム・ニュース・コンテンツ・FAQ・法務ページ等）で表示。どちらもデータが無いテナントでは区画ごと非表示。カテゴリのリンク先は `link` があれば `LegacyLinkResolver`、無ければ `/contents?category={id}`。ホームページのみ右カラムに `<x-slot name="aside">` でロゴ／オーナー欄を追加（`x-layouts.public` は `aside` スロットの有無と左サイドバーの有無で 1〜3カラムのグリッドを自動選択） |
| `/contents` の2カラム表示 | `Public\ContentController@index` | contents.asp?Contentsort=N | `?category={id}` で特定カテゴリの詳細を旧レイアウトのまま再現: パンくず「現在位置：{カテゴリ名}」＋キーワード検索ボックス＋そのカテゴリ直下の記事（見出し無し）＋直下の子カテゴリごとに見出しバー＋配下（孫以降を含む）の記事一覧。`?q=` はサイト内キーワード検索（`name`/`keyword` の部分一致）。どちらも無指定なら従来の階層ツリー表示（`ContentSort::publicTree()`）のまま、各見出しが `?category=` へのリンクになった |
| `/news` | `Livewire\Public\NewsIndex` | news.asp | 一覧・タイトル検索・32件/ページ。`newsdate <= now` のみ、`istop` 優先 |
| `/news/{id}` | `Public\NewsController@show` | newsdetail.asp | 本文HTML + 前後リンク。未公開/他サイトは404。clicks++ |
| `/contents` | `Public\ContentController@index` | contents.asp / inc_kataroguson.asp | `ContentSort::publicTree()` でカテゴリを `father_id` 階層表示。publicVisible（`ninshou` null/0）のみ、各カテゴリに公開コンテンツ（`ok=1`）。自身にも子孫にも公開コンテンツが無い枝は除外。循環は per-path visited で打ち切り。深さで見出しサイズ＋左ボーダーのインデント（`public.partials.category-node` 再帰） |
| `/contents/{id}` | `Public\ContentController@show` | ContentDetail.asp | 本文HTML。非公開/非公開カテゴリ/他サイトは404。clicks++ |
| `/faq` | `Public\FaqController@index` | faq.asp | 全FAQ（`<details>` で開閉）+ キーワード検索 |
| `/signup` | `Auth\TenantSignupController` | （新規） | **セルフサーブのテナント作成**（新機能）。会社名・サイトID（半角英小文字/数字/ハイフン、予約語・重複不可）・お名前・メール・パスワードを入力すると `rooms`＋`members`＋`member_room`(ninshou=-1) を一括作成し、作成者をそのテナントの管理員としてログインさせ `/admin` へ。新テナントは free プラン・内部共同作業機能一式（todo/problem/risk/wbs/product/routinework/change/members/messages/files/survey/board）を有効化した状態で始まる。**制約**: カスタムドメイン/サブドメインの自動払い出しは無いため、新テナントの公開フロント（`/`・`/news`・`/contents` 等、ホスト名で解決）にはまだ独自URLで到達できない。ログイン後の会員向け機能（`/admin`・`/dashboard`・`/tasks` 等はセッションで解決）はすぐ使える。`/login` からもリンク |
| `/register` | `Auth\RegisteredUserController` | reguser_*.asp | 会員登録。お名前・ふりがな・メール・電話（任意）・パスワード。現在サイトに `member_room`（`ninshou = 0` = コンテンツ閲覧のみ）を作成し自動ログイン。プロジェクト機能の利用には管理員が `ninshou` を 1 以上に引き上げる（＝旧「本承認」）。`newmemberregfunction` が無いサイトは 404 |
| `/manager` | `Public\SitePageController@managerWords` | managerwords.asp | `rooms.managerwords`（HTML）をそのまま表示。見出しは `rooms.manager_shouko`（無ければ「管理員」）。`managerwordsfunction` 必須 |
| `/links` | `Public\SitePageController@links` | friendlink 系 | 管理員が承認したリンク（`links.allow = 1`）の一覧。承認は Filament `LinkItemResource`。`friendlinkfunction` 必須 |
| `/members` | `Member\MemberListController@index` | memberlist.asp | サイト参加者（`member_room.ninshou` 1/-1）の一覧。名前・自己紹介・オンライン表示。名前は個人ページへリンク。`memberlistfunction` 必須 |
| `/files` `/files/{id}/download` | `Member\FileController` | filelist.asp / fileadd.asp / download.asp | 会員ファイルライブラリ。一覧（タグ絞り込み・20件/頁）／アップロード（許可拡張子・25MB上限・プラン容量チェック）／ダウンロード（アプリ経由でストリーム）／削除（本人 or 管理員）。実体は S3/R2（`STORAGE.md`）。旧Access www 50件のうち 31件は `migrate_legacy_files.php` で移行済み、残19件は旧サーバに実体無し（「実体未移行」表示）。管理画面は Filament `FileItemResource`（`/admin/file-items`）。`filemanagefunction` 必須 |
| `/members/online` | `Member\MemberListController@online` | onlinelist.asp / onlinechk.asp | オンライン中の参加者一覧＋メッセージ送信リンク。`TrackMemberPresence` ミドルウェアが会員ごと 60 秒スロットルで `members.timerenew` を更新し、`Member::isOnline()`（直近 15 分）で判定。`onlinemembersfunction` 必須。`/members` 上部からリンク |
| `/members/{member}` | `Member\MemberListController@show` | memberpage.asp | 会員個人ページ。表示名・ふりがな・ニックネーム（`appeal`）・性別・ホームページ（`hp`、`Member::homepageUrl()` で正規化）・自己紹介（`introduce`、`strip_tags`+エスケープで安全化）・現在サイトでの役割（管理員/参加者）・メッセージ送信リンク（`?to=`）。現在サイトの参加者以外／`memberlistfunction` 無しは 404 |
| `/profile` | `ProfileController`（Breeze拡張） | membermod.asp | Breeze 既定の name/email に加え、`nameread`（ふりがな）/`appeal`（ニックネーム）/`phone`/`hp`/`sex`（1=男性 0=女性 空=未回答）/`introduce`（自己紹介、公開）を編集可能に（`ProfileUpdateRequest`）。`introduce` は保存前に旧HTMLを `strip_tags` 表示に寄せる |
| `/messages` `/messages/sent` `/messages/create` `/messages/{id}` | `Member\MessageController` | Member_MessageSend.asp | 社内メッセージ（伝言）。受信箱／送信箱／作成／詳細（受信者は開くと既読）／削除（送信者=`delete_from`、受信者=`delete_to` の論理削除）。宛先はサイト参加者のみ。`dengonfunction` 必須 |
| `/mypage` | `MypageController`（route 名 `dashboard`） | Mypage.asp | ログイン後の入口。本日の計画作業 / 管理タスク対応状況（todo・課題・リスク・WBS × 新規/接近/遅延/期限未設定）/ 定例作業対応状況。集計は `App\Support\TaskDashboard`。パンくず＋右サイドバー「MyMenu」（`layouts/partials/my-menu.blade.php`、旧ASP相当）の2カラム。会員トップナビ（`layouts/navigation.blade.php`）は機能一覧を出さず最小限（ホーム/ニュース/掲示板/MyPage/アップロード）にし、業務機能は MyMenu 側（プロジェクト参加者のみ、`TaskKind` 各種の起票/私の担当/全員のタスク、WBS/サーベイ/コミュニティ/メンバー状況/メッセージ/ファイル管理、有効な機能のみ表示）に集約。各セクションは Alpine の `x-collapse` でクリック開閉（旧ASP同様）、現在地に対応するセクションは強調表示＋初期状態で開く。現状 MyMenu は mypage 専用（他の会員ページはまだ旧来の全幅レイアウトのまま） |
| `/legal/tokushoho` `/legal/terms` `/legal/privacy` | `LegalController` | （新規） | サービス共通の法務ページ（テナント非依存）。特商法表記は `config/plans.php` の価格表を表示。事業者情報は `config/legal.php`（`LEGAL_*` env）。フッター＋`/admin/billing` にリンク。詳細は `LEGAL.md` |
| `/contact` `/contact/thanks` | `Public\InquiryController` | otoi.asp / otoi2 / otoi3 | お問い合わせフォーム。会員はプロフィールから自動入力。保存（`inquiries`、`site_id` 自動）＋ 受付確認メール（本人）＋ 新着通知メール（`rooms.site_mail`）。番号は `T{id}`。`rooms.function_list` に `otoiawasefunction` が無いサイトは 404（nav リンクも非表示） |
| `/tasks/{kind}`（todo/problem/risk/product/routinework） | `Livewire\Member\TaskList` + `Member\TaskController` | todo.asp / Problem.asp / Risk.asp / product.asp / RoutineWorkList.asp | 一覧（フィルタ・キーワード検索・列ソート・20件/頁、**一覧上でステータス／担当者を `<select>` で即時変更、✪ で「本日のタスク」(`dotoday`) トグル**）／詳細／新規起票・編集・論理削除（`delete_to=1`）。`{kind}function` が無いサイトは 404。`App\Support\TaskKind` の `features` で任意フィールド（期限/チーム/状況/完了基準/承認者/内容/ステージ/責任者/today）を出し分け。product は期限なし、routinework は `actiondate`（表示「実施日」）。Mypage の集計値からドリルダウン（todo/problem/risk） |
| `/wbs` `/wbs/{id}` ほか | `Member\WbsController` | wbs.asp / WbsAdd.asp / WbsDetail.asp | 階層ツリー表示 ＋ 詳細 ＋ 追加・編集・論理削除 ＋ D&D並び替え（下記）＋ `/wbs/check` 計画チェック ＋ **`/wbs/schedule` スケジュール計算（CPM）**。`wbsfunction` 必須 |
| `/wbs/check` | 旧 WBS_CheckFromTo/CheckDays | サマリ項目の計画工数/開始/完了 vs 配下タスク集計。超過=赤・余裕=黄・未計画=灰 |
| `/wbs/schedule` `?root={id}` `?calendar=working\|calendar` | 新機能 | `App\Support\WbsScheduler`（CPM）。依存タイプ FS/SS/FF/SF ＋ リード/ラグ（`relations.dep_type` / `lag_days`）を考慮し `tododays` で ES/EF を前進計算 → 後退計算で LS/LF/フロート → フロート0以下 = クリティカルパス。日数の数え方は `App\Support\WorkCalendar` で「稼働日」（土日＋`holidays` を除外, 既定）/「暦日」を切替。非wbs先行はその `duedate` を固定制約に。循環は検出してエラー。「計算結果を反映」で `godate`/`duedate` を書き戻す（`?root=` があればその配下のみ、サマリ項目は任意でロールアップ更新） |
| `/wbs/holidays` | 新機能 | `holidays` テーブル（`site_id` 自動）の追加・削除。スケジュール計算の「稼働日」モードで除外される休日。土日は自動で非稼働日 |
| `/wbs/load` `?capacity=N` | 新機能 | `App\Support\WbsLoadAnalyzer`。各リーフ WBS の `tododays` を期間（着手予定〜期限）の稼働日に均等配分し、担当者 × ISO週 で合計。週あたり稼働可能日数（既定 5、3〜6 で切替）超の週を過負荷として色分け＋内訳表示。簡易リソース平準化の第一歩（自動再配置はしない） |
| 関連タスクパネル（`<livewire:member.relations-panel>`） | WBS詳細・タスク詳細に埋め込み | WbsDetail の relation 部分 | 先行/後続/関連（`relations` テーブル、`rtype` = `fromto`/`relation`）の一覧・追加・論理削除。先行/後続は依存タイプ（FS/SS/FF/SF）とラグ日数も指定可（一覧に「SS +2d」等を表示）。kind をまたいで（wbs↔todo等）リンク可。先行タスクの完了予定 > このタスクの開始予定 なら ⚠ 警告。`App\Support\Relations` + `App\Support\TaskRef` |
| `/surveys` `/surveys/{id}` `/surveys/{id}/answer` | `Member\SurveyController` | SurveyList_My.asp / Survey.asp / Survey_ChoiceResult.asp | 回答可能なサーベイ一覧（open かつ選択肢あり、回答済み/未回答/受付終了バッジ）／回答フォーム（`selectable_numbers` で radio/checkbox）／集計結果（棒グラフ）。**締切済み・受付終了のサーベイも `show` で集計を閲覧可**（回答は open のときだけ）。**`specify_yn`（記名式）のサーベイは各選択肢に投票者名（`Survey::tallyWithVoters()`）をチップ表示**、「記名式」バッジも。回答は `survey_choice_results`（選択ごと1行）＋ `survey_reply_lists`（回答済みマーカー）をトランザクションで。`surveyfunction` 必須 |
| `/surveys/manage` `/surveys/create` `/surveys/{id}/edit` ほか | `Member\SurveyController@manage/create/store/edit/update/destroy/toggleOpen` | SurveyList_Mytask.asp / Survey_new.asp / Surveyedit_son.asp | サーベイの作成・編集・締切／再開（`open_yn`）・論理削除（`delete_to=1`）。一覧は「自分が作成したもの」＋管理員は全件。選択肢は Alpine の可変行（タイトル＋説明）。**回答が付いた後は選択肢を編集不可**（メタ情報は可）。回答期限は `endOfDay` で保存 |
| `/board` `/board/categories/{id}` `/board/threads/{id}` ほか | `Member\BoardController` | meetlist.asp / meet.asp / meet_disp.asp / meetadd.asp / meet_re.asp | 掲示板。`/board`=コミュニティ一覧（`guestbook_categories`。id=1 は「サイト掲示板」既定カテゴリで一覧では別枠表示）／`categories/{id}`=スレッド一覧（`guestbooks` の `parent='0'`、返信数・管理員返信バッジ、10件/頁）／`threads/{id}`=スレッド詳細（本文＋`revert` 管理員返信＋`parent`/`top`/`space_num` の自己参照ツリーで返信をインデント表示、各ノードに Alpine 開閉式の返信フォーム）／`categories/{id}/new` 新規スレッド。返信は `top`=スレッド先頭ID・`space_num`=親+1 を自動セット。`create_date` に投稿時刻。旧Access由来の空行は `Guestbook::scopeReal()` で除外。管理員返信の編集は Filament（`GuestbookResource`）。`freeguestbookfunction` 必須 |
| コンテンツのコメント（`<livewire:public.content-comments>`） | 公開コンテンツ詳細に埋め込み | ContentCommentSon.asp / ContentComment_Write.asp / ContentCommentList.asp | `commentfunction` かつ `contents.commentok=1` のとき表示。`content_comments` を新しい順・10件/頁。閲覧は誰でも、投稿はプロジェクト参加者のみ（未ログインは「ログインすると…」、`ninshou=0` は不可の旨）。`time` は旧データにあわせ `Y/m/d H:i:s` 文字列で保存 |

## サイトロゴ・favicon（`x-site-logo` / `partials.favicon`）

`rooms.logo`（例 `img/logoCTO.png`。旧ASPの実ファイルを `public/img/` にそのまま配置済み）を
ヘッダー（公開フロント・会員画面）とログイン/登録画面で共通コンポーネント `<x-site-logo :site="$site" />`
経由で表示する。`logo` 未設定のテナントは既定の SVG マークにフォールバック。
ロゴ画像がある場合、隣に重複してサイト名テキストは出さない（ロゴ自体に文字が入っているため）。
favicon も同様に `rooms.favicon` があれば優先、無ければ `public/img/favicon.png`（`partials/favicon.blade.php`）。

Blade の匿名コンポーネントは呼び出し元のスコープを自動継承しない（`@include` と違う）ため、
`$site` は明示的に `:site="$site"` で渡す必要がある点に注意。

## 旧ASPページ名のリンク解決（`App\Support\LegacyLinkResolver`）

`top_menus.linkaddress` や `content_sorts.link` には旧ASPの相対パス（`index.asp`・
`otoi.asp`・`contents.asp?Contentsort=30` 等）や外部URLがそのまま残っている。
`LegacyLinkResolver::resolve($raw, $site, $fallback)` が:

- `http(s)://` 始まりはそのまま外部リンクとして返す
- `index.asp`→ホーム、`otoi*.asp`→お問い合わせ、`faq.asp`→FAQ、`news.asp`→ニュース、
  `contents.asp`→コンテンツ一覧、`meetlist.asp`/`meet.asp`→掲示板（`freeguestbookfunction` 必須）、
  `managerwords.asp`→オーナーの言葉（`managerwordsfunction` 必須）、`friendlink.asp`→リンク集
  （`friendlinkfunction` 必須）に対応付ける
- 対応表に無い相対パスは `$fallback`（省略時 null）を返す。呼び出し側で
  `/contents` や `/`（サイトトップ）を渡している。旧データは残したまま、
  Filament（`TopMenuResource`・`ContentSortResource`）側で `linkaddress`/`link` を
  新サイトのパスに更新していけば、順次リンクが直っていく設計

## モデルのスコープ

- `NewsItem::scopePublished()` = `newsdate <= now`、`scopeListingOrder()` = istop→newsdate→id
- `Content::scopePublished()` = `ok = 1`、`scopeListingOrder()` = junban→adddatetime→id
- `ContentSort::scopePublicVisible()` = `ninshou is null or 0`

## 未実装（旧ASPの主要導線の残り）

- change（変更管理）: **実装済み**。`change_requests` テーブル ＋ `2026_09_01 seed_change_statuses`（各サイトに `kind='change'` の 8 ステータス: 起票→調査中→判定待ち→承認待ち→対応中→完了／却下／保留）＋ `TaskKind` の `change`（`features` に `changedetail` = 発生日・工数見積・判定結果・影響範囲）。一覧/CRUD は汎用 TaskList / TaskController、nav は `changefunction` で自動表示、Filament は `ChangeRequestResource`。関連タスクパネル（`TaskRef::KINDS`）には未登録
- routinework: `App\Support\RoutineWorkGenerator` が `routine_works`（繰り返しルール: circle = day/week/month/year、`circle_number`）から `routine_work_lists` を生成。会員は `/routinework/generate`（旧 RoutineWorkMake.asp）で期間指定、cron 用に `php artisan routinework:generate --days=N [--site=]`。同一マスター×同一 actiondate は重複作成しない
- スケジューリング: FS/SS/FF/SF ＋ リード/ラグ ＋ 稼働日カレンダー（休日 `holidays`）対応済み。リソース平準化は `/wbs/load` の負荷分析（過負荷週の検出）まで。自動でのタスク再配置は未
- `relations` の既存データはテスト混じりで重複・削除済み参照あり（パネルは「(削除済み #N)」と表示してグレースフルに処理）
- スケジュール計算はプレビュー→明示的な「反映」でのみ DB を書き換える（自動再計算はしない）
- WBS D&D は SortableJS の `forceFallback: true`（ポインタイベント）。タッチ端末での操作性は要確認
- サーベイの作成・編集・締切は `/surveys/manage` 系で対応済み。`specify_yn`（記名式）は集計画面で選択肢ごとの投票者名を表示（`show` は締切済みサーベイも閲覧可に修正）
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
- **新機能: コンテンツ / WBS / タスクへの添付**（`<livewire:attachments-panel>` を contents-show / wbs-show / task-show に埋め込み。`attachments` テーブル + `HasAttachments` トレイト。画像はサムネイル表示。認可は公開コンテンツならゲスト可・それ以外は参加者。詳細は `STORAGE.md`）
- オンラインメンバー（`onlinemembersfunction`）は **実装済み**（`/members/online`、上記表）
- セミナー（`seminarfunction`：`seminars` テーブル無し）、作品公開（`sakuhinkoukaifunction`：`homeworks` テーブル無し・`homework_sorts` 空）、
  バージョン履歴（`sys_versions` テーブル無し）は **スキーマ未エクスポート**のため未実装。必要になったら Access から該当テーブルを再エクスポートする
