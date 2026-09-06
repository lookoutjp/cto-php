<?php

namespace App\Filament\Resources\ContentSortResource\Pages;

use App\Filament\Concerns\RedirectsCreateBackToOrigin;
use App\Filament\Resources\ContentSortResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContentSort extends CreateRecord
{
    use RedirectsCreateBackToOrigin;

    protected static string $resource = ContentSortResource::class;

    /**
     * 管理者モードのカテゴリ詳細ページ「＋カテゴリを追加」からは ?father_id=N が付く
     * （N はそのカテゴリの親＝同じ階層に足す想定。トップレベルなら 0＝ルート）。
     * 親カテゴリを事前選択した状態でフォームを開く。
     */
    protected function fillForm(): void
    {
        parent::fillForm();

        if (request()->has('father_id')) {
            $this->data['father_id'] = request()->integer('father_id');
        }
    }
}
