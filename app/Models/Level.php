<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * 旧ASP lebel テーブル。組織の階層（部門・グループ・チーム名）兼 権限レベル（サイトごと）。
 * `level` がサイト内での識別子、`fatherlevel` が親の `level`（0 または null = ルート）。
 * UserDB 由来だが site_id を持つ完全なテナント別データなので BelongsToSite を付ける。
 */
class Level extends Model
{
    use BelongsToSite;

    protected $table = 'levels';

    public $timestamps = false;

    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'fatherlevel', 'level');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'fatherlevel', 'level')->orderBy('level');
    }

    /**
     * 現在サイトの組織階層をツリーで返す（旧 orgchart.asp）。
     * ルート（fatherlevel が 0 / null）の各ノードに、子を `kids` リレーションとして
     * 再帰的にぶら下げる。手編集データなので循環は visited セットで打ち切る。
     *
     * @return Collection<int, Level>
     */
    public static function tree(): Collection
    {
        $all = static::query()->orderBy('level')->get();

        $byFather = $all->groupBy(fn (Level $l) => (int) $l->fatherlevel);

        $attach = function (Level $node, array $seen) use (&$attach, $byFather) {
            $key = (int) $node->level;
            $kids = isset($seen[$key])
                ? collect()
                : ($byFather[$key] ?? collect())
                    ->map(fn (Level $child) => $attach($child, $seen + [$key => true]))
                    ->values();

            $node->setRelation('kids', $kids);

            return $node;
        };

        return $byFather->get(0, collect())
            ->merge($all->whereNull('fatherlevel'))
            ->unique('level')
            ->map(fn (Level $root) => $attach($root, []))
            ->values();
    }
}
