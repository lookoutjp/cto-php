<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

/**
 * 旧ASP lebel テーブル。権限レベル兼「チーム/グループ」名（サイトごと）。
 * UserDB 由来だが site_id を持つ完全なテナント別データなので BelongsToSite を付ける。
 */
class Level extends Model
{
    use BelongsToSite;

    protected $table = 'levels';
    public $timestamps = false;
    protected $guarded = [];
}
