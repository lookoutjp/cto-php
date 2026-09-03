<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use BelongsToSite;

    protected $table = 'inquiries';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'create_date' => 'datetime',
        'treated_date' => 'datetime',
    ];

    /** 問い合わせ番号（旧ASP: "T" + id）。 */
    public function getTicketNumberAttribute(): string
    {
        return 'T'.$this->id;
    }
}
