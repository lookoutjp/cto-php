<?php

namespace App\Support;

use App\Models\Relation;
use Illuminate\Support\Collection;

/**
 * relations テーブルの読み書き。旧ASP の先行(fromto)/関連(relation)を扱う。
 *
 * fromto: id_from が先行、id_to が後続。
 */
class Relations
{
    /** 後続タスク（この {kind,id} が先行）。 */
    public static function successors(string $kind, int $id): Collection
    {
        return self::hydrate(
            Relation::query()->active()
                ->where('rtype', Relation::SEQUENCE)
                ->where('id_from_kind', $kind)->where('id_from', $id)
                ->get(),
            fn (Relation $r) => [$r->id_to_kind, (int) $r->id_to, $r->id],
        );
    }

    /** 先行タスク（この {kind,id} が後続）。 */
    public static function predecessors(string $kind, int $id): Collection
    {
        return self::hydrate(
            Relation::query()->active()
                ->where('rtype', Relation::SEQUENCE)
                ->where('id_to_kind', $kind)->where('id_to', $id)
                ->get(),
            fn (Relation $r) => [$r->id_from_kind, (int) $r->id_from, $r->id],
        );
    }

    /** 関連（順序なし）。両方向。 */
    public static function related(string $kind, int $id): Collection
    {
        $rows = Relation::query()->active()
            ->where('rtype', Relation::RELATED)
            ->where(fn ($w) => $w
                ->where(fn ($x) => $x->where('id_from_kind', $kind)->where('id_from', $id))
                ->orWhere(fn ($x) => $x->where('id_to_kind', $kind)->where('id_to', $id)))
            ->get();

        return self::hydrate($rows, function (Relation $r) use ($kind, $id) {
            $isFrom = $r->id_from_kind === $kind && (int) $r->id_from === $id;

            return $isFrom
                ? [$r->id_to_kind, (int) $r->id_to, $r->id]
                : [$r->id_from_kind, (int) $r->id_from, $r->id];
        });
    }

    public static function add(
        string $fromKind, int $fromId, string $toKind, int $toId, string $rtype,
        string $depType = 'FS', int $lagDays = 0,
    ): void {
        if ($fromKind === $toKind && $fromId === $toId) {
            return;
        }

        $exists = Relation::query()->active()
            ->where('rtype', $rtype)
            ->where('id_from_kind', $fromKind)->where('id_from', $fromId)
            ->where('id_to_kind', $toKind)->where('id_to', $toId)
            ->exists();
        if ($exists) {
            return;
        }

        $rel = new Relation;
        $rel->rtype = $rtype;
        $rel->id_from_kind = $fromKind;
        $rel->id_from = $fromId;
        $rel->id_to_kind = $toKind;
        $rel->id_to = $toId;
        $rel->dep_type = in_array($depType, ['FS', 'SS', 'FF', 'SF'], true) ? $depType : 'FS';
        $rel->lag_days = $lagDays;
        $rel->delete_to = 0;
        $rel->save(); // BelongsToSite が site_id をセット
    }

    public static function remove(int $relationId): void
    {
        Relation::query()->active()->whereKey($relationId)->update(['delete_to' => 1]);
    }

    /**
     * @param  Collection<int, Relation>  $rows
     * @param  callable(Relation): array{0:string,1:int,2:int}  $pick  [otherKind, otherId, relationId]
     */
    private static function hydrate(Collection $rows, callable $pick): Collection
    {
        return $rows->map(function (Relation $r) use ($pick) {
            [$kind, $otherId, $relId] = $pick($r);
            $kind = strtolower((string) $kind);
            $model = TaskRef::resolve($kind, $otherId);

            return (object) [
                'relation_id' => $relId,
                'kind' => $kind,
                'kind_label' => TaskRef::label($kind),
                'id' => $otherId,
                'model' => $model,
                'title' => $model?->title ?? "(削除済み #{$otherId})",
                'dep_type' => $r->dep_type ?: 'FS',
                'lag_days' => (int) $r->lag_days,
            ];
        })->filter(fn ($x) => $x->kind !== '' );
    }
}
