<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class TopMenu extends Model
{
    use BelongsToSite;

    protected $table = 'top_menus';

    public $timestamps = false;

    protected $guarded = [];

    /** 表示名（旧Access由来の固定長カラムなので前後の空白を落とす）。 */
    public function label(): string
    {
        return trim((string) $this->menuname);
    }

    public function isExternal(): bool
    {
        return (bool) preg_match('#^https?://#i', trim((string) $this->linkaddress));
    }
}
