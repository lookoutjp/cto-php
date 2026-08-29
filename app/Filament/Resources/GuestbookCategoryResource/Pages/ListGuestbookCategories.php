<?php

namespace App\Filament\Resources\GuestbookCategoryResource\Pages;

use App\Filament\Resources\GuestbookCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuestbookCategories extends ListRecords
{
    protected static string $resource = GuestbookCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
