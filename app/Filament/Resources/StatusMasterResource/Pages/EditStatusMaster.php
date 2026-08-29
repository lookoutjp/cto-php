<?php

namespace App\Filament\Resources\StatusMasterResource\Pages;

use App\Filament\Resources\StatusMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatusMaster extends EditRecord
{
    protected static string $resource = StatusMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
