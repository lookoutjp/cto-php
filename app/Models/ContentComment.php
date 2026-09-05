<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * コンテンツ記事へのユーザーコメント。旧ASP: ContentComment.asp / ContentCommentSon.asp。
 *
 * 表示は公開コンテンツ詳細に埋め込み（誰でも閲覧可）。
 * 投稿はプロジェクト参加者（ninshou 1/-1）のみ。
 * `time` 列は旧データにあわせて "Y/m/d H:i:s" 形式の文字列で保持する。
 */
class ContentComment extends Model
{
    use BelongsToSite;

    protected $table = 'content_comments';

    public $timestamps = false;

    protected $guarded = [];

    /** 権限(ninshou)は編集画面から外しているため、新規作成時は常に 0。 */
    protected $attributes = [
        'ninshou' => 0,
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function scopeForContent(Builder $query, int|string $contentId): Builder
    {
        return $query->where('content_id', (string) $contentId);
    }

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('id');
    }

    /** 文字列で保持している投稿時刻を Carbon で返す（表示用）。 */
    public function postedAt(): ?Carbon
    {
        return $this->time ? rescue(fn () => Carbon::parse($this->time), null, false) : null;
    }
}
