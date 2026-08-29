<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 社内メッセージ（伝言）。旧ASP: messages / Member_MessageSend.asp。
 *   from / to   … member_id（旧データには数値の legacy id も混在）
 *   readed      … 受信者が既読にしたか（0/1）
 *   delete_from … 送信者が送信箱から削除（0/1）
 *   delete_to   … 受信者が受信箱から削除（0/1）
 */
class MessageItem extends Model
{
    use BelongsToSite;

    protected $table = 'messages';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'time' => 'datetime',
        'readed' => 'boolean',
        'delete_from' => 'boolean',
        'delete_to' => 'boolean',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'from', 'member_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'to', 'member_id');
    }

    /** 受信箱: $memberId 宛で、受信者が削除していないもの。 */
    public function scopeInbox(Builder $q, string $memberId): Builder
    {
        return $q->where('to', $memberId)
            ->where(fn ($w) => $w->where('delete_to', false)->orWhereNull('delete_to'))
            ->orderByDesc('time')->orderByDesc('id');
    }

    /** 送信箱: $memberId 発で、送信者が削除していないもの。 */
    public function scopeOutbox(Builder $q, string $memberId): Builder
    {
        return $q->where('from', $memberId)
            ->where(fn ($w) => $w->where('delete_from', false)->orWhereNull('delete_from'))
            ->orderByDesc('time')->orderByDesc('id');
    }
}
