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

## 旧データ

- 旧Access `files` のメタデータ（50件、www のみ）は移行済みだが**実体バイトは未アップロード**（`storage_key` が null）。
  一覧では「（実体未移行）」と表示、ダウンロードボタンは出さない。
- 旧サーバの `files/{siteid}/WebUp/{foldername}/{id}_{filename}_{renban}.{ext}` から吸い出して
  `Storage::disk('s3')->put(FileStorage::keyFor(...), ...)` → `storage_key`/`size_bytes`/`mime` を埋める
  一括スクリプトを書けば移行できる（未着手）。

## 未対応（次のステップ）

- Filament `FileItemResource` でのアップロード（現状は会員フロント `/files` のみ）
- コンテンツ / WBS / タスクへの**添付**（`files` は今は独立ライブラリ。polymorphic な添付は別設計）
- 旧実ファイルの一括移行スクリプト
- 画像のインラインプレビュー / サムネイル（`FileStorage::isImage()` は用意済み、UI 未使用）
- ダウンロードを `temporaryUrl()`（署名URLへリダイレクト）に切り替えると帯域がアプリを通らない。
  現状は `Storage::download()` でアプリがプロキシする（監査・アクセス制御がシンプル）
