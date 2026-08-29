<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * 掲示板の投稿。旧ASP: guestbook / meet.asp・meet_disp.asp・meet_re.asp。
 *
 * スレッド構造は自己参照:
 *   - parent : 直接の親投稿ID（ルートは '0'）— 文字列型
 *   - top    : スレッド先頭（ルート投稿）のID（ルートは '0'）— 文字列型
 *   - space_num : 表示インデントの深さ（ルート 0、返信は親+1）
 * revert / revert_date は管理員からの返信（Filament 側で編集）。
 */
class Guestbook extends Model
{
    use BelongsToSite;

    protected $table = 'guestbooks';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'category' => 'integer',
        'space_num' => 'integer',
        'orders' => 'integer',
        'create_date' => 'datetime',
        'revert_date' => 'datetime',
        'answer_date' => 'datetime',
    ];

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(GuestbookCategory::class, 'category');
    }

    /** 投稿者（user_name に会員IDが入る）。 */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_name', 'member_id');
    }

    /** ルート投稿（スレッド）だけ。旧: parent='0' */
    public function scopeThreads(Builder $query): Builder
    {
        return $query->whereRaw("coalesce(parent, '0') = '0'");
    }

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category', $categoryId);
    }

    /** データ不良（旧Access由来の空行）を除外。 */
    public function scopeReal(Builder $query): Builder
    {
        return $query->whereNotNull('title')->whereNotNull('parent');
    }

    public function isThread(): bool
    {
        return in_array((string) ($this->parent ?? '0'), ['0', ''], true);
    }

    public function hasManagerReply(): bool
    {
        return filled($this->revert);
    }

    /**
     * このスレッドの全返信を1クエリで取得し、parent でぶら下げた木を返す。
     * 返り値は「ルート直下の返信」の Collection（各要素に children を再帰セット）。
     */
    public function replyTree(): Collection
    {
        $all = static::query()->real()
            ->where('top', (string) $this->id)
            ->orderBy('id')
            ->get();

        $byParent = $all->groupBy(fn (self $g) => (string) $g->parent);

        $attach = function (self $node) use (&$attach, $byParent) {
            $node->setRelation('children', ($byParent[(string) $node->id] ?? collect())
                ->each(fn (self $child) => $attach($child)));

            return $node;
        };

        return ($byParent[(string) $this->id] ?? collect())->each(fn (self $n) => $attach($n));
    }
}
