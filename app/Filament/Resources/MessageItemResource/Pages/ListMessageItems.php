<?php

namespace App\Filament\Resources\MessageItemResource\Pages;

use App\Filament\Resources\MessageItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMessageItems extends ListRecords
{
    protected static string $resource = MessageItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
