<?php

namespace App\Filament\Resources\MessageItemResource\Pages;

use App\Filament\Resources\MessageItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMessageItem extends EditRecord
{
    protected static string $resource = MessageItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
