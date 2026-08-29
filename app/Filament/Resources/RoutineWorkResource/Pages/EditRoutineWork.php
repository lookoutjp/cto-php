<?php

namespace App\Filament\Resources\RoutineWorkResource\Pages;

use App\Filament\Resources\RoutineWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoutineWork extends EditRecord
{
    protected static string $resource = RoutineWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
