<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ContentSort extends Model
{
    use BelongsToSite;

    protected $table = 'content_sorts';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * 未ログインの来訪者に見せてよいカテゴリ（旧ASP: ninshou is null or 0）。
     */
    public function scopePublicVisible(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->whereNull('ninshou')->orWhere('ninshou', 0));
    }

    public function scopeListingOrder(Builder $query): Builder
    {
        return $query->orderBy('junban')->orderBy('id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'father_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'father_id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'content_sort');
    }

    /**
     * 公開来訪者向けのカテゴリ階層（旧 contents.asp / inc_kataroguson.asp）。
     *
     * publicVisible なカテゴリだけで `father_id`（0 / null = ルート）ツリーを組み立て、
     * 各ノードに公開コンテンツを `contents`、子カテゴリを `kids` として付ける。
     * 自身にも子孫にも公開コンテンツが無いカテゴリは枝ごと除外する。
     * 循環はパスごとの visited セットで打ち切る。
     *
     * @return Collection<int, ContentSort>
     */
    public static function publicTree(): Collection
    {
        $cats = static::query()
            ->publicVisible()
            ->listingOrder()
            ->with(['contents' => fn ($q) => $q->published()->listingOrder()])
            ->get();

        $byFather = $cats->groupBy(fn (ContentSort $c) => (int) $c->father_id);

        $build = function (ContentSort $node, array $seen) use (&$build, $byFather) {
            $key = (int) $node->id;

            $kids = isset($seen[$key])
                ? collect()
                : ($byFather[$key] ?? collect())
                    ->map(fn (ContentSort $child) => $build($child, $seen + [$key => true]))
                    ->filter()
                    ->values();

            $node->setRelation('kids', $kids);

            if ($node->contents->isEmpty() && $kids->isEmpty()) {
                return null;
            }

            return $node;
        };

        return $byFather->get(0, collect())
            ->merge($cats->whereNull('father_id'))
            ->unique('id')
            ->map(fn (ContentSort $root) => $build($root, []))
            ->filter()
            ->values();
    }
}
