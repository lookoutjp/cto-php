<?php

namespace App\Filament\Resources\NewsItemResource\Pages;

use App\Filament\Concerns\RedirectsCreateBackToOrigin;
use App\Filament\Resources\NewsItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsItem extends CreateRecord
{
    use RedirectsCreateBackToOrigin;

    protected static string $resource = NewsItemResource::class;
}
