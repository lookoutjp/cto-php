<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wbs extends Model
{
    use BelongsToSite;

    protected $table = 'wbs';
    public $timestamps = false;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'father_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'father_id');
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
}
