<?php

namespace App\Filament\Resources\SurveyChoiceResultResource\Pages;

use App\Filament\Resources\SurveyChoiceResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSurveyChoiceResult extends EditRecord
{
    protected static string $resource = SurveyChoiceResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
