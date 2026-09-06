<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Concerns\RedirectsEditBackToOrigin;
use App\Filament\Resources\RoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoom extends EditRecord
{
    use RedirectsEditBackToOrigin;

    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successRedirectUrl(fn () => $this->backTo ?? RoomResource::getUrl('index')),
        ];
    }
}
