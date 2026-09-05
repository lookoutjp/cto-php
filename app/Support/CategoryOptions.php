<?php

namespace App\Support;

use App\Models\ContentSort;

/**
 * Filament のカテゴリ選択（親カテゴリ / コンテンツの所属カテゴリ等）で使う
 * 階層インデント付きの [id => ラベル] を組み立てる。
 */
class CategoryOptions
{
    /**
     * @param  bool  $withRoot  先頭に [0 => 'ルート'] を含める
     * @param  int|null  $excludeId  このカテゴリ自身と子孫を除外（親選択の循環防止用）
     * @return array<int, string>
     */
    public static function indented(bool $withRoot = false, ?int $excludeId = null): array
    {
        $all = ContentSort::query()->orderBy('junban')->orderBy('id')->get();

        $excluded = [];
        if ($excludeId !== null) {
            $excluded[$excludeId] = true;
            $stack = [$excludeId];
            while ($stack) {
                $parentId = array_pop($stack);
                foreach ($all->where('father_id', $parentId) as $child) {
                    if (! isset($excluded[$child->id])) {
                        $excluded[$child->id] = true;
                        $stack[] = $child->id;
                    }
                }
            }
        }

        $byFather = $all->groupBy(fn (ContentSort $c) => (int) $c->father_id);

        $options = $withRoot ? [0 => 'ルート'] : [];

        $walk = function (int $fatherId, int $depth) use (&$walk, $byFather, $excluded, &$options) {
            foreach ($byFather->get($fatherId, collect()) as $node) {
                if (isset($excluded[$node->id])) {
                    continue;
                }
                $options[(int) $node->id] = str_repeat('　', $depth).($depth > 0 ? '└ ' : '').$node->name;
                $walk((int) $node->id, $depth + 1);
            }
        };
        $walk(0, 0);

        return $options;
    }
}
