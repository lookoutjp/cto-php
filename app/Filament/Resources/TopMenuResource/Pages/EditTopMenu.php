<?php

namespace App\Filament\Resources\TopMenuResource\Pages;

use App\Filament\Concerns\RedirectsEditBackToOrigin;
use App\Filament\Resources\TopMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTopMenu extends EditRecord
{
    use RedirectsEditBackToOrigin;

    protected static string $resource = TopMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successRedirectUrl(fn () => $this->backTo ?? TopMenuResource::getUrl('index')),
        ];
    }
}
