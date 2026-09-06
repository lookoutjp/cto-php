<?php

namespace App\Filament\Resources\LinkItemResource\Pages;

use App\Filament\Concerns\RedirectsEditBackToOrigin;
use App\Filament\Resources\LinkItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLinkItem extends EditRecord
{
    use RedirectsEditBackToOrigin;

    protected static string $resource = LinkItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successRedirectUrl(fn () => $this->backTo ?? LinkItemResource::getUrl('index')),
        ];
    }
}
