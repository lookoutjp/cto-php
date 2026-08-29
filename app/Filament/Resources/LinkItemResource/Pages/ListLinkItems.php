<?php

namespace App\Filament\Resources\LinkItemResource\Pages;

use App\Filament\Resources\LinkItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLinkItems extends ListRecords
{
    protected static string $resource = LinkItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
