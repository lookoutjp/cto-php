<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    use BelongsToSite;

    /** 先行→後続の順序依存（旧ASP rtype='fromto'）。 */
    public const SEQUENCE = 'fromto';

    /** 単なる関連リンク（順序なし。旧ASP rtype='relation'）。 */
    public const RELATED = 'relation';

    /** 依存タイプ（rtype=SEQUENCE のとき）。FS=先行完了→後続開始 など。 */
    public const DEP_TYPES = [
        'FS' => '完了→開始',
        'SS' => '開始→開始',
        'FF' => '完了→完了',
        'SF' => '開始→完了',
    ];

    protected $table = 'relations';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'lag_days' => 'integer',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where(fn ($w) => $w->where('delete_to', '!=', 1)->orWhereNull('delete_to'));
    }
}
