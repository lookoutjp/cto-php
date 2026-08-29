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
| `app/Support/CurrentSite.php` | 現在アクティブなサイトを保持するシングルトン。旧 `Session("yhl.SiteID")` 相当 |
| `app/Models/Concerns/BelongsToSite.php` | 業務モデル用トレイト。読み取りをグローバルスコープで絞り、作成時に `site_id` を自動セット |
| `config/app.php` の `default_site` | `CurrentSite` が解決できないときの fallback（`APP_DEFAULT_SITE`、既定 `www`） |
| `database/migrations/2026_08_30_000000_add_site_id_to_tenant_tables.php` | 35テーブルへの `site_id` 追加 + 既存行を `www` でバックフィル |
| `app/Http/Middleware/ResolveCurrentSite.php` | リクエストごとに `session('site_id')`→Member所属サイトの順で `CurrentSite` を確定。web グループ全体 + Filamentパネルの authMiddleware に登録済み |
| `app/Livewire/SiteSwitcher.php` + `resources/views/livewire/site-switcher.blade.php` | Filament管理画面トップバーのサイト切替セレクタ。`rooms` 全件を出し、選択で `session('site_id')` を更新して画面をフルリロード。サイトが1つのときは非表示 |

## CurrentSite の解決順

1. 明示的に `set()` された値（Filamentのサイト切替、コンソール等）
2. `session('site_id')`
3. ログイン中 `Member` が所属する最初のサイト（`member_room`）
4. 未確定（`idOrNull()` が null）→ **スコープ無効**（全サイトが見える）。`id()` は `default_site` を返す

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

- **サイト切替の権限制御**: 現状ログイン中の Member は `rooms` 全件に切り替えられる。
  本来は「所属サイト（`member_room`）のみ」または「スーパー管理者のみ全サイト」に絞るべき。
  `SiteSwitcher::render()` / `updatedSiteId()` の候補一覧と、`ResolveCurrentSite` の検証を
  Member の所属で絞る。
- **`site_id` の NOT NULL 化**: 全テナント投入後に別マイグレーションで。
- **他テナント（demo / miraipm）のデータ投入**: `schema-gen/load_data.php` にジョブを追加。
- **`Wbs::descendantsOf()` の再帰CTE**: 外側クエリのグローバルスコープで結果的に絞られているが、
  CTE内部にも `site_id` 条件を入れた方が安全（大規模データ時のパフォーマンス）。
