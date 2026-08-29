<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutineWork extends Model
{
    use BelongsToSite;

    protected $table = 'routine_works';
    public $timestamps = false;
    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(RoutineWorkList::class, 'routine_work_id');
    }
}
