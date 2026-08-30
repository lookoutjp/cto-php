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

## 未対応（次のステップ）

- コンテンツ / WBS / タスクへの**添付**（`files` は今は独立ライブラリ。旧ASPにも無かった機能なので新規設計。
  polymorphic な `attachments` テーブルを別途）
- 画像のインラインプレビュー / サムネイル（`FileStorage::isImage()` は用意済み、UI 未使用）
- FileUpload でファイルを差し替えた際の旧 R2 オブジェクトの掃除（現状は孤児が残る）
- ダウンロードを `temporaryUrl()`（署名URLへリダイレクト）に切り替えると帯域がアプリを通らない。
  現状は `Storage::download()` でアプリがプロキシする（監査・アクセス制御がシンプル）
