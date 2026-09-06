<?php

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Concerns\RedirectsCreateBackToOrigin;
use App\Filament\Resources\FaqResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
    use RedirectsCreateBackToOrigin;

    protected static string $resource = FaqResource::class;
}
