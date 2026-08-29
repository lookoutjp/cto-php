<?php

namespace App\Filament\Resources\MailListResource\Pages;

use App\Filament\Resources\MailListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMailList extends EditRecord
{
    protected static string $resource = MailListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
