<?php

namespace App\Filament\Resources\FileItemResource\Pages;

use App\Filament\Resources\FileItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFileItem extends EditRecord
{
    protected static string $resource = FileItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
