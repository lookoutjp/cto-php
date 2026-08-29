<?php

namespace App\Filament\Resources\ContentSortResource\Pages;

use App\Filament\Resources\ContentSortResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContentSorts extends ListRecords
{
    protected static string $resource = ContentSortResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
