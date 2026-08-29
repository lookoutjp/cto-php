<?php

namespace App\Filament\Resources\ContentSortResource\Pages;

use App\Filament\Resources\ContentSortResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContentSort extends EditRecord
{
    protected static string $resource = ContentSortResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
