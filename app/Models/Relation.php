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

    protected $table = 'relations';
    public $timestamps = false;
    protected $guarded = [];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where(fn ($w) => $w->where('delete_to', '!=', 1)->orWhereNull('delete_to'));
    }
}
