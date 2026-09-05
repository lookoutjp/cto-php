<?php

namespace App\Filament\Resources\TopMenuResource\Pages;

use App\Filament\Concerns\RedirectsCreateBackToOrigin;
use App\Filament\Resources\TopMenuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTopMenu extends CreateRecord
{
    use RedirectsCreateBackToOrigin;

    protected static string $resource = TopMenuResource::class;
}
