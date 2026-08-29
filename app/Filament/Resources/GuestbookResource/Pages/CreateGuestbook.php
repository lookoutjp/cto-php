<?php

namespace App\Filament\Resources\GuestbookResource\Pages;

use App\Filament\Resources\GuestbookResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGuestbook extends CreateRecord
{
    protected static string $resource = GuestbookResource::class;
}
