<?php

namespace App\Filament\Resources\ContentSortResource\Pages;

use App\Filament\Concerns\RedirectsCreateBackToOrigin;
use App\Filament\Resources\ContentSortResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContentSort extends CreateRecord
{
    use RedirectsCreateBackToOrigin;

    protected static string $resource = ContentSortResource::class;
}
