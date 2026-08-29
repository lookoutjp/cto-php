<?php

namespace App\Filament\Resources\MemberRoomResource\Pages;

use App\Filament\Resources\MemberRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMemberRooms extends ListRecords
{
    protected static string $resource = MemberRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
