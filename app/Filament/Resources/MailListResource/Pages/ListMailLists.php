<?php

namespace App\Filament\Resources\MailListResource\Pages;

use App\Filament\Resources\MailListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMailLists extends ListRecords
{
    protected static string $resource = MailListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
