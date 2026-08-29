<?php

namespace App\Support;

use App\Models\Problem;
use App\Models\Product;
use App\Models\Risk;
use App\Models\Todo;
use App\Models\Wbs;
use Illuminate\Database\Eloquent\Model;

/**
 * relations テーブルの {kind, id} を、実際のレコード（Wbs/Todo/…）に解決する。
 * kind をまたいだ先行/後続リンクの表示に使う。
 */
class TaskRef
{
    /** @var array<string, class-string<Model>> */
    public const KINDS = [
        'wbs' => Wbs::class,
        'todo' => Todo::class,
        'problem' => Problem::class,
        'risk' => Risk::class,
        'product' => Product::class,
    ];

    public const LABELS = [
        'wbs' => 'WBS', 'todo' => 'TODO', 'problem' => '課題', 'risk' => 'リスク', 'product' => '成果物',
    ];

    public static function label(string $kind): string
    {
        return self::LABELS[$kind] ?? $kind;
    }

    public static function resolve(string $kind, int $id): ?Model
    {
        $model = self::KINDS[strtolower($kind)] ?? null;
        if ($model === null) {
            return null;
        }

        return $model::query()
            ->where(fn ($w) => $w->where('delete_to', '!=', 1)->orWhereNull('delete_to'))
            ->find($id);
    }

    /** その kind の未削除レコードを [id => title] で（セレクト用）。 */
    public static function options(string $kind): \Illuminate\Support\Collection
    {
        $model = self::KINDS[strtolower($kind)] ?? null;
        if ($model === null) {
            return collect();
        }

        return $model::query()
            ->where(fn ($w) => $w->where('delete_to', '!=', 1)->orWhereNull('delete_to'))
            ->orderByDesc('id')
            ->limit(500)
            ->pluck('title', 'id');
    }

    /** レコードから「期限」相当の日付を取り出す（wbs は godate/duedate 両方あり得る）。 */
    public static function endDate(?Model $m): ?\Illuminate\Support\Carbon
    {
        return $m?->duedate ?? $m?->complete_date ?? null;
    }

    public static function startDate(?Model $m): ?\Illuminate\Support\Carbon
    {
        return $m?->godate ?? $m?->start_date ?? null;
    }
}
