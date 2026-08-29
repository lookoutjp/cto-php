<?php

namespace App\Filament\Resources\RoutineWorkResource\Pages;

use App\Filament\Resources\RoutineWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoutineWorks extends ListRecords
{
    protected static string $resource = RoutineWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
