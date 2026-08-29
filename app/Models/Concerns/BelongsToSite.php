<?php

namespace App\Models\Concerns;

use App\Support\CurrentSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 業務(tenant)テーブル用。単一DB + site_id 列による行レベルのテナント分離。
 *
 *  - 読み取り: CurrentSite が確定していれば site_id で自動絞り込み
 *  - 書き込み: 作成時に site_id が空なら CurrentSite の値を自動セット
 *
 * スコープを外したいとき:
 *   Model::withoutSiteScope()->...      // 全サイト横断
 *   Model::forSite('demo')->...         // 特定サイトに限定
 */
trait BelongsToSite
{
    public static function bootBelongsToSite(): void
    {
        static::addGlobalScope('site', function (Builder $builder) {
            $current = app(CurrentSite::class);
            $model = $builder->getModel();

            if ($current->deniesAll()) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $siteId = $current->idOrNull();

            if ($siteId !== null) {
                $builder->where($model->getTable().'.site_id', $siteId);
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->getAttribute('site_id'))) {
                $model->setAttribute('site_id', app(CurrentSite::class)->id());
            }
        });
    }

    public function scopeWithoutSiteScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('site');
    }

    public function scopeForSite(Builder $query, string $siteId): Builder
    {
        return $query->withoutGlobalScope('site')
            ->where($this->getTable().'.site_id', $siteId);
    }
}
