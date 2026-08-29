<?php

namespace App\Filament\Resources\MemberRoomResource\Pages;

use App\Filament\Resources\MemberRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMemberRoom extends EditRecord
{
    protected static string $resource = MemberRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
