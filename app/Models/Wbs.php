<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use App\Models\Concerns\TaskModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wbs extends Model
{
    use BelongsToSite;
    use TaskModel;

    public static string $taskKind = 'wbs';
    public static ?string $taskDateColumn = 'duedate';

    protected $table = 'wbs';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'duedate' => 'datetime',
        'start_date' => 'datetime',
        'complete_date' => 'datetime',
        'dotoday' => 'datetime',
        'renewdate' => 'datetime',
        'iscategory' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'father_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'father_id')->orderBy('junban')->orderBy('jun')->orderBy('id');
    }

    /**
     * 配下すべての子孫を再帰CTEで一括取得する（旧ASPのget_wbs_Sons相当）。
     * N+1を避けるため、1回のクエリで階層全体を取得する。
     */
    public static function descendantsOf(int $id)
    {
        return self::query()
            ->fromRaw('(
                WITH RECURSIVE wbs_tree AS (
                    SELECT * FROM wbs WHERE father_id = ?
                    UNION ALL
                    SELECT w.* FROM wbs w
                    INNER JOIN wbs_tree t ON w.father_id = t.id
                )
                SELECT * FROM wbs_tree
            ) as wbs', [$id])
            ->get();
    }

    /**
     * サイトの WBS 全件から親子ツリーを組み立てて返す（ルート配列）。
     * father_id が null / 0 のものをルートとみなす。
     */
    public static function tree(): \Illuminate\Support\Collection
    {
        $all = static::query()->notDeleted()
            ->with(['statusMaster', 'assignee', 'team'])
            ->orderBy('junban')->orderBy('jun')->orderBy('id')
            ->get();

        $byFather = $all->groupBy(fn ($w) => (int) $w->father_id);

        $attach = function ($node) use (&$attach, $byFather) {
            $node->setRelation('kids', ($byFather[(int) $node->id] ?? collect())->each($attach));

            return $node;
        };

        return ($byFather[0] ?? collect())
            ->merge($all->whereNull('father_id'))
            ->unique('id')
            ->each($attach)
            ->values();
    }
}
