<?php

namespace App\Filament\Resources\FileItemResource\Pages;

use App\Filament\Resources\FileItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFileItem extends CreateRecord
{
    protected static string $resource = FileItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return FileItemResource::fillFromUpload($data);
    }
}
