<?php

namespace App\Filament\Resources\SiteCustomResource\Pages;

use App\Filament\Resources\SiteCustomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSiteCustoms extends ListRecords
{
    protected static string $resource = SiteCustomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
