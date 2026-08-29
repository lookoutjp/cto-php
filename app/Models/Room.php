<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    protected $table = 'rooms';
    protected $primaryKey = 'site_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * このサイト(テナント)に所属する会員一覧。
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_room', 'site_id', 'member_id')
            ->withPivot('ninshou');
    }
}
