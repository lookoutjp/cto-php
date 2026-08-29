<?php

namespace App\Filament\Resources\FileItemResource\Pages;

use App\Filament\Resources\FileItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFileItems extends ListRecords
{
    protected static string $resource = FileItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
