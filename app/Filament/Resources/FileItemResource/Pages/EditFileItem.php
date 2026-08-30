<?php

namespace App\Filament\Resources\FileItemResource\Pages;

use App\Filament\Resources\FileItemResource;
use App\Support\FileStorage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditFileItem extends EditRecord
{
    protected static string $resource = FileItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(fn () => $this->record->hasBytes()
                    && Storage::disk(FileStorage::DISK)->delete($this->record->storage_key)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return FileItemResource::fillFromUpload($data, $this->record);
    }
}
