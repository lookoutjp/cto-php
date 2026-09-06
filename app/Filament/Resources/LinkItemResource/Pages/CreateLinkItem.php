<?php

namespace App\Filament\Resources\LinkItemResource\Pages;

use App\Filament\Concerns\RedirectsCreateBackToOrigin;
use App\Filament\Resources\LinkItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLinkItem extends CreateRecord
{
    use RedirectsCreateBackToOrigin;

    protected static string $resource = LinkItemResource::class;
}
