<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Concerns\RedirectsCreateBackToOrigin;
use App\Filament\Resources\ContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContent extends CreateRecord
{
    use RedirectsCreateBackToOrigin;

    protected static string $resource = ContentResource::class;

    /**
     * 管理者モードの「このカテゴリに記事を追加」からは ?content_sort=N が付く。
     * 既定値（投稿者など）を活かしたうえで、カテゴリだけ事前選択する。
     */
    protected function fillForm(): void
    {
        parent::fillForm();

        if ($categoryId = request()->integer('content_sort')) {
            $this->data['content_sort'] = $categoryId;
        }
    }
}
