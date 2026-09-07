<?php

namespace App\Filament\Resources\ContentSortManagerResource\Pages;

use App\Filament\Resources\ContentSortManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContentSortManager extends EditRecord
{
    protected static string $resource = ContentSortManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'approved' && blank($this->record->decided_at)) {
            $data['decided_at'] = now();
            $data['decided_by'] = auth()->id();
        }

        return $data;
    }
}
