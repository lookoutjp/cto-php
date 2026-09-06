<?php

namespace App\Filament\Resources\NewsItemResource\Pages;

use App\Filament\Concerns\RedirectsEditBackToOrigin;
use App\Filament\Resources\NewsItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNewsItem extends EditRecord
{
    use RedirectsEditBackToOrigin;

    protected static string $resource = NewsItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successRedirectUrl(fn () => $this->backTo ?? NewsItemResource::getUrl('index')),
        ];
    }
}
