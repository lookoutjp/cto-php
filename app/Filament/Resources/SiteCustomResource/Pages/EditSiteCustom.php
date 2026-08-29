<?php

namespace App\Filament\Resources\SiteCustomResource\Pages;

use App\Filament\Resources\SiteCustomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSiteCustom extends EditRecord
{
    protected static string $resource = SiteCustomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
