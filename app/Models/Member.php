<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable
{
    use Notifiable;

    protected $table = 'members';
    protected $primaryKey = 'member_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['password'];

    /**
     * この会員が所属するサイト(テナント)一覧。
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'member_room', 'member_id', 'site_id')
            ->withPivot('ninshou');
    }
}
