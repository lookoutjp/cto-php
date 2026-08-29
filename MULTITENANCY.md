# マルチテナント設計（単一DB + 行スコープ）

旧ASPは `templatedb.mdb` をテナント（サイト）ごとに物理コピーしていた。
有償SaaS化に向けて、**単一のPostgreSQL DB + `site_id` 列による行レベル分離**に変更した。

## 用語

- **サイト / テナント** = 旧ASPの `SiteID`（例: `www`, `demo`, `miraipm`）。`rooms` テーブルの主キー。
- 認証系（`members` / `member_room` / `rooms` / `levels`）は元々全サイト共有なので変更なし。
- 業務系35テーブル（`contents` / `todos` / `wbs` / `inquiries` …）に `site_id`（string 50, index, nullable）を追加。

## 構成要素

| ファイル | 役割 |
|---|---|
| `app/Support/CurrentSite.php` | 現在アクティブなサイトを保持するシングルトン。旧 `Session("yhl.SiteID")` 相当。`denyAll()` で「1件も見せない」状態にできる |
| `app/Models/Concerns/BelongsToSite.php` | 業務モデル用トレイト。読み取りをグローバルスコープで絞り、作成時に `site_id` を自動セット。`deniesAll()` 時は `1=0` |
| `app/Models/Member.php` | `FilamentUser` 実装。`canAccessPanel()`、`manageableSiteIds()`（管理員=ninshou -1 のサイト）、`accessibleSiteIds()`（所属サイト全部）、`isSuperAdmin()`、`managesSite()` |
| `config/app.php` の `default_site` / `super_admin_member_ids` | fallback サイト（`APP_DEFAULT_SITE`、既定 `www`）／ 全サイトを管理できる運営者の member_id（`APP_SUPER_ADMIN_MEMBER_IDS`、カンマ区切り） |
| `database/migrations/2026_08_30_000000_add_site_id_to_tenant_tables.php` | 35テーブルへの `site_id` 追加 + 既存行を `www` でバックフィル |
| `app/Http/Middleware/ResolveCurrentSite.php` | ログイン中 Member について `session('site_id')`（`manageableSiteIds()` に含まれる場合のみ）→ 先頭サイト の順で `CurrentSite` を確定。管理サイトが無ければ `denyAll()`。web グループ全体 + Filamentパネルの authMiddleware に登録済み |
| `app/Livewire/SiteSwitcher.php` + `resources/views/livewire/site-switcher.blade.php` | Filament管理画面トップバーのサイト切替セレクタ。**候補は `Member::manageableSiteIds()` に限定**。選択で `session('site_id')` 更新 + フルリロード。候補が1つ以下のときは非表示 |

## CurrentSite の解決順

通常は `ResolveCurrentSite` ミドルウェアがリクエスト開始時に `set()` する。
明示的に未設定のまま参照された場合のフォールバック:

1. 明示的に `set()` された値（サイト切替、コンソール等）
2. `session('site_id')`
3. ログイン中 `Member` の `manageableSiteIds()` の先頭
4. 未確定（`idOrNull()` が null）→ **スコープ無効**（全サイトが見える）。`id()` は `default_site` を返す

`denyAll()` 状態（管理サイトの無いログインユーザー）のときは上記に関わらず1件も返さない。

## 権限モデル（旧ASP ninshou の対応）

- `member_room.ninshou`: `-1` = そのサイトの管理員、`0`/`1` = 一般権限レベル（旧ASP `isManager` は `ninshou == -1`）
- **`/admin`（Filament）へのアクセス** = `Member::canAccessPanel()` = 「1サイト以上の管理員」または「スーパー管理者」。
  一般会員（ninshou 0/1 のみ）はログイン画面で `These credentials do not match our records.` になる。
- **パスワード**: `members.password` は旧ASP由来の非bcryptハッシュが大半。
  `App\Auth\LegacyAwareUserProvider`（`config/auth.php` の `providers.users.driver = legacy-aware-eloquent`）が
  旧形式（PBKDF2-HMAC-MD5 / 無ソルトMD5切り詰め、`App\Auth\LegacyPasswordVerifier`）を検証し、
  ログイン成功時に bcrypt へ静かに移行する。Breeze `/login` と Filament `/admin/login` の両方で機能する。
- **サイト切替の候補・`CurrentSite` の対象** = `Member::manageableSiteIds()`
  - スーパー管理者（`config('app.super_admin_member_ids')`）: `rooms` 全件
  - それ以外: `member_room` で `ninshou = -1` のサイトのみ
- `accessibleSiteIds()`（所属サイト全部、ninshou 問わない）は将来のフロント用。現状の管理画面フローでは未使用。
- 想定挙動: 管理サイト1つのみ → 切替UIは出ない。管理サイト0 → `/admin` に入れない（入れても `denyAll`）。

## 使い方

```php
// 通常: CurrentSite に従って自動で絞られる
Content::all();

// 全サイト横断
Content::withoutSiteScope()->get();

// 特定サイト
Content::forSite('demo')->get();

// テナントを明示切替（バッチ処理など）
app(\App\Support\CurrentSite::class)->set('demo');
```

## 未対応（次のステップ）

- **管理画面内のリソース単位の権限**: サイト内での編集可否（旧 `admin_kengen.asp` 相当）は未実装。
  Filament の Policy で `Member::managesSite()` を使う想定。
- **`members.email` の重複**: `office` / `u187` / `demouser` が同一メール。email ログインは先頭1件を拾う。
  実運用前に一意化が必要。
- **`site_id` の NOT NULL 化**: 全テナント投入後に別マイグレーションで。
- **他テナント（demo / miraipm）のデータ投入**: `schema-gen/load_data.php` にジョブを追加。
- **`Wbs::descendantsOf()` の再帰CTE**: 外側クエリのグローバルスコープで結果的に絞られているが、
  CTE内部にも `site_id` 条件を入れた方が安全（大規模データ時のパフォーマンス）。
