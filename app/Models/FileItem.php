<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FileItem extends Model
{
    use BelongsToSite;

    protected $table = 'files';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'adddt' => 'datetime',
        'size_bytes' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    /** 実体がオブジェクトストレージにあるか（旧Access由来は未アップロード）。 */
    public function hasBytes(): bool
    {
        return filled($this->storage_key);
    }

    /** ダウンロード時のファイル名。 */
    public function downloadName(): string
    {
        $base = Str::of((string) $this->filename)->replace(['/', '\\'], '-')->trim()->value() ?: 'file';
        $ext = Str::of((string) $this->fileext)->lower()->ltrim('.')->value();

        return $ext !== '' && ! Str::endsWith(Str::lower($base), '.'.$ext)
            ? "{$base}.{$ext}"
            : $base;
    }

    /**
     * tag_id 列（旧ASP: ",6,7," のようなカンマ囲みID文字列）を tag_id の配列にする。
     *
     * @return array<int, int>
     */
    public function tagIds(): array
    {
        return collect(explode(',', (string) $this->tag_id))
            ->map(fn ($v) => (int) trim($v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** @return Collection<int, FileTag> */
    public function tags(): Collection
    {
        $ids = $this->tagIds();
        if (empty($ids)) {
            return collect();
        }

        return FileTag::query()->whereIn('tag_id', $ids)->orderBy('tagname')->get();
    }

    public function scopeWithTag(Builder $q, int $tagId): Builder
    {
        return $q->where('tag_id', 'like', "%,{$tagId},%");
    }

    public function scopeListingOrder(Builder $q): Builder
    {
        return $q->orderByDesc('id');
    }
}
