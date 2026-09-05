<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class FileTag extends Model
{
    use BelongsToSite;

    protected $table = 'file_tags';

    public $timestamps = false;

    protected $guarded = [];

    // 注意: 意味的なタグ識別子は tag_id 列（files.tag_id 等が参照）。
    // 主キーの id は Filament 用に後付けした自動採番列。tag_id での絞り込みは
    // これまで通りクエリビルダで明示的に行うこと。

    protected static function booted(): void
    {
        static::creating(function (FileTag $tag) {
            if (blank($tag->tag_id)) {
                $tag->tag_id = (int) static::query()->withoutGlobalScope('site')->max('tag_id') + 1;
            }
            if (blank($tag->adddt)) {
                $tag->adddt = now();
            }
        });
    }
}
