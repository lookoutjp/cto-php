<?php

namespace App\Filament\Resources\StatusMasterResource\Pages;

use App\Filament\Resources\StatusMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStatusMasters extends ListRecords
{
    protected static string $resource = StatusMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
