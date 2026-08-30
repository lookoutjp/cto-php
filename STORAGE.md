# ファイルストレージ（S3 / Cloudflare R2）

会員ファイルライブラリ（旧 `filelist.asp`）の実体はオブジェクトストレージに置く。
ファイルは**非公開**で、ダウンロードはアプリ経由（テナント境界チェック後にストリーム）。

## 構成

| 要素 | 役割 |
|---|---|
| `config/filesystems.php` の `s3` ディスク | `AWS_*` を読む。R2 は `region=auto` / `use_path_style_endpoint=true` / `endpoint=https://<accountid>.r2.cloudflarestorage.com` |
| `.env` の `FILESYSTEM_DISK=s3` ＋ `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY` / `AWS_BUCKET` / `AWS_ENDPOINT` | 接続情報 |
| `App\Support\FileStorage` | disk 名（`s3`）、許可拡張子、上限（25MB）、キー生成 `sites/{site_id}/files/{uuid}.{ext}`、サイズ整形 |
| `App\Models\FileItem`（`files` テーブル） | `storage_key`（null = 旧Access由来で実体未アップロード）・`size_bytes`・`mime` 列を `2026_09_03_000000` で追加。`tags()`/`tagIds()`（`tag_id` はカンマ囲みID文字列 `,6,7,`）、`downloadName()`、`hasBytes()` |
| `App\Http\Controllers\Member\FileController` | `/files`（一覧＋タグ絞り込み＋アップロード）、`/files/{id}/download`（`Storage::download`）、`DELETE /files/{id}`（R2オブジェクトも削除）。`filemanagefunction` ＋ `EnsureProjectMember` ＋ `EnsureTenantBillingActive` |
| `App\Support\Plans::storageUsageBytes()` / `withinStorageLimit()` | `files.size_bytes` の合計でプラン容量（`storage_mb`）を従量チェック。アップロード時と `/admin/billing` の使用量表示で使用 |

## 依存

- `composer require league/flysystem-aws-s3-v3`（Laravel の s3 ドライバに必須）

## 権限・境界

- 一覧・アップロード・ダウンロード: 現在サイトのプロジェクト参加者（`ninshou` 1/-1）
- 削除: アップロード者本人 または サイト管理員
- `FileItem` は `BelongsToSite` で自動スコープ。`download()` は `findOrFail` が他サイトを弾く
- キーにも `site_id` を含める（`sites/{site_id}/files/...`）ので、万一スコープ漏れがあってもテナント跨ぎは起きにくい

## 旧データの移行

- 旧Access `files` のメタデータ（www 50件、demo/miraipm は 0）は移行済み。
- 実体バイトは `schema-gen/migrate_legacy_files.php` で旧サーバの `files/{siteid}/WebUp/...` から
  S3/R2 に吸い上げる（`--dry` でプレビュー可）。旧ファイル名は `{id}.{ext}` / `{id}_{rand}.{ext}` /
  `{folder}/{id}_{rand}_{renban}.{ext}` が混在するので「id 前置 + 拡張子一致」で探し、
  foldername 一致 > `{id}_` 形式 > サイズ最大 の順で1件選ぶ。
- **実行済み（www）**: 50行中 31件を R2 へ。残り 19件は旧サーバから既に消えていた（`storage_key` null のまま、
  一覧で「実体未移行」表示・DLボタンなし）。

## Filament（管理画面）

`FileItemResource`（`/admin/file-items`）でも一覧・アップロード・DL・削除ができる。
`FileUpload` を `storage_key` にバインドし `getUploadedFileNameForStorageUsing` で `uuid.ext` キー、
`FileItemResource::fillFromUpload()` が保存前に `fileext`/`filename`/`size_bytes`/`mime`/`tag_id`/`member_id` を整える。
削除アクションは R2 オブジェクトも消す。BelongsToSite で現在の管理サイトに自動スコープ。

## 添付ファイル（コンテンツ / WBS / タスク）— 旧ASPに無かった新機能

`files`（独立ライブラリ）とは別の polymorphic な `attachments` テーブル（`2026_09_04_000000`）。

| 要素 | 役割 |
|---|---|
| `App\Models\Attachment`（`attachments`） | `attachable_type`/`attachable_id` の morphTo、`storage_key`/`original_name`/`ext`/`size_bytes`/`mime`/`member_id`。`BelongsToSite` |
| `App\Models\Concerns\HasAttachments` | `attachments()` morphMany。**物理削除**（`deleting`）＋**論理削除**（`updated` で `delete_to` が 1 になったら）の両方で添付レコード＋R2オブジェクトを消す（`purgeAttachments()`） |
| 適用モデル | Content / Wbs / Todo / Problem / Risk / Product / RoutineWorkList / ChangeRequest |
| `App\Support\Attachables` | 短い type 文字列（`content`/`wbs`/`todo`…）→ モデルクラスの解決。Livewire にモデルを渡さない |
| `App\Livewire\AttachmentsPanel` | `<livewire:attachments-panel type="wbs" :id="..." />`。task-show / wbs-show / contents-show に埋め込み。一覧（画像は `temporaryUrl` でサムネイル）＋アップロード＋削除 |
| `App\Filament\RelationManagers\AttachmentsRelationManager` | 管理画面の「添付ファイル」タブ。Content / Wbs / Todo / Problem / Risk / Product / RoutineWorkList / ChangeRequest の各 Resource の `getRelations()` に登録。※ private disk では `ImageColumn`（`->url()` が例外）を使えないので拡張子バッジ + 🖼 表示。`$isLazy = false` |
| `App\Http\Controllers\AttachmentController@download` | `/attachments/{id}/download`。**認可**: コンテンツの添付は公開コンテンツならゲストも可 / 非公開・WBS・タスクはプロジェクト参加者のみ。添付先が消えていたら 404。R2 からアプリ経由でストリーム |
| `php artisan attachments:prune [--dry]` | 添付先が消えている / 論理削除（`delete_to=1`）された孤児 attachment を掃除（レコード + R2） |
| キー | `sites/{site_id}/attachments/{uuid}.{ext}` |
| 容量 | `Plans::storageUsageBytes()` は `files` ＋ `attachments` の合計 |

## 画像プレビュー

会員ファイルライブラリ `/files` と添付パネルで、画像（`FileStorage::isImage()`）は
`Storage::disk('s3')->temporaryUrl($key, +30min)` の署名URLを `<img>` に出してサムネイル表示。
R2 が直接配信するのでアプリの帯域は使わない（DL 本体は従来どおりアプリ経由ストリーム）。

## 未対応（次のステップ）

- R2 バケット側の CORS（会員フロントの画像サムネイルは `<img>` なので不要だが、
  将来クライアント側で `fetch` するなら R2 の CORS 設定が要る）
- 画像以外（PDF等）のプレビュー
