<?php

namespace App\Filament\Resources\ContentSortManagerResource\Pages;

use App\Filament\Resources\ContentSortManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContentSortManagers extends ListRecords
{
    protected static string $resource = ContentSortManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('カテゴリ管理員を指定'),
        ];
    }
}
