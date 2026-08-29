<?php

namespace App\Filament\Resources\RoutineWorkListResource\Pages;

use App\Filament\Resources\RoutineWorkListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoutineWorkList extends EditRecord
{
    protected static string $resource = RoutineWorkListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
