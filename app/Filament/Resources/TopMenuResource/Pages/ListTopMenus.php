<?php

namespace App\Filament\Resources\TopMenuResource\Pages;

use App\Filament\Resources\TopMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTopMenus extends ListRecords
{
    protected static string $resource = TopMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
