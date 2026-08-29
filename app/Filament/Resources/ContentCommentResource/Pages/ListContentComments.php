<?php

namespace App\Filament\Resources\ContentCommentResource\Pages;

use App\Filament\Resources\ContentCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContentComments extends ListRecords
{
    protected static string $resource = ContentCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
