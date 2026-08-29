<?php

namespace App\Filament\Resources\SurveyChoiceResultResource\Pages;

use App\Filament\Resources\SurveyChoiceResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSurveyChoiceResults extends ListRecords
{
    protected static string $resource = SurveyChoiceResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
