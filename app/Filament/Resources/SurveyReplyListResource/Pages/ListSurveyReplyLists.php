<?php

namespace App\Filament\Resources\SurveyReplyListResource\Pages;

use App\Filament\Resources\SurveyReplyListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSurveyReplyLists extends ListRecords
{
    protected static string $resource = SurveyReplyListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
