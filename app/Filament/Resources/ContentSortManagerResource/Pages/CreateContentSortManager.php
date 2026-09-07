<?php

namespace App\Filament\Resources\ContentSortManagerResource\Pages;

use App\Filament\Resources\ContentSortManagerResource;
use App\Models\ContentSort;
use Filament\Resources\Pages\CreateRecord;

class CreateContentSortManager extends CreateRecord
{
    protected static string $resource = ContentSortManagerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['site_id'] = (string) (ContentSort::query()
            ->whereKey($data['content_sort_id'])->value('site_id') ?? '');

        $data['applied_at'] ??= now();

        if (($data['status'] ?? 'approved') === 'approved') {
            $data['decided_at'] = now();
            $data['decided_by'] = auth()->id();
        }

        return $data;
    }
}
