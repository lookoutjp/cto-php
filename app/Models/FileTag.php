<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class FileTag extends Model
{
    use BelongsToSite;

    protected $table = 'file_tags';

    // 注意: 元スキーマは複合キーのため、Eloquentの主キー機能は使えません。
    // save()/find()は使わず、クエリビルダで明示的に条件指定してください。
    public $timestamps = false;

    protected $guarded = [];
}
