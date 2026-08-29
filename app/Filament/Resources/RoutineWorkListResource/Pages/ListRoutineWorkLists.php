<?php

namespace App\Filament\Resources\RoutineWorkListResource\Pages;

use App\Filament\Resources\RoutineWorkListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoutineWorkLists extends ListRecords
{
    protected static string $resource = RoutineWorkListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
