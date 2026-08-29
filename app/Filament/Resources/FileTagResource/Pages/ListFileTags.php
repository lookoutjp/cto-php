<?php

namespace App\Filament\Resources\FileTagResource\Pages;

use App\Filament\Resources\FileTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFileTags extends ListRecords
{
    protected static string $resource = FileTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
