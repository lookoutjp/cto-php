<?php

namespace App\Support;

use App\Models\ChangeRequest;
use App\Models\Content;
use App\Models\Problem;
use App\Models\Product;
use App\Models\Risk;
use App\Models\RoutineWorkList;
use App\Models\Todo;
use App\Models\Wbs;
use Illuminate\Database\Eloquent\Model;

/**
 * 添付を持てるモデルの一覧（`AttachmentsPanel` / `AttachmentController` の型解決）。
 * Livewire にモデルを渡す代わりに短い type 文字列 + id で扱う。
 */
class Attachables
{
    /** @var array<string, class-string<Model>> */
    public const TYPES = [
        'content' => Content::class,
        'wbs' => Wbs::class,
        'todo' => Todo::class,
        'problem' => Problem::class,
        'risk' => Risk::class,
        'product' => Product::class,
        'routinework' => RoutineWorkList::class,
        'change' => ChangeRequest::class,
    ];

    public const LABELS = [
        'content' => 'コンテンツ', 'wbs' => 'WBS', 'todo' => 'TODO', 'problem' => '課題',
        'risk' => 'リスク', 'product' => '成果物', 'routinework' => '定例作業', 'change' => '変更管理',
    ];

    public static function classFor(string $type): ?string
    {
        return self::TYPES[strtolower($type)] ?? null;
    }

    public static function typeFor(Model $model): ?string
    {
        return array_search($model::class, self::TYPES, true) ?: null;
    }

    public static function resolve(string $type, int $id): ?Model
    {
        $class = self::classFor($type);
        if ($class === null) {
            return null;
        }

        $query = $class::query();

        // タスク系は論理削除（delete_to=1）を除外
        if (in_array($type, ['todo', 'problem', 'risk', 'product', 'routinework', 'change', 'wbs'], true)) {
            $query->where(fn ($w) => $w->where('delete_to', '!=', 1)->orWhereNull('delete_to'));
        }

        return $query->find($id);
    }
}
