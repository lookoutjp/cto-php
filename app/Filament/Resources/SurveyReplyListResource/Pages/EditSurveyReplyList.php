<?php

namespace App\Filament\Resources\SurveyReplyListResource\Pages;

use App\Filament\Resources\SurveyReplyListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSurveyReplyList extends EditRecord
{
    protected static string $resource = SurveyReplyListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
