<?php

namespace App\Filament\Resources\SurveyChoiceResource\Pages;

use App\Filament\Resources\SurveyChoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSurveyChoices extends ListRecords
{
    protected static string $resource = SurveyChoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
