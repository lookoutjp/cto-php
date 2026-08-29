<?php

namespace App\Filament\Resources\GuestbookCategoryResource\Pages;

use App\Filament\Resources\GuestbookCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuestbookCategory extends EditRecord
{
    protected static string $resource = GuestbookCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
