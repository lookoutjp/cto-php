<?php

namespace App\Filament\Resources\MemberRoomResource\Pages;

use App\Filament\Resources\MemberRoomResource;
use App\Models\Member;
use App\Support\CurrentSite;
use Filament\Resources\Pages\CreateRecord;

class CreateMemberRoom extends CreateRecord
{
    protected static string $resource = MemberRoomResource::class;

    /**
     * フォームの disabled() はUI上の制約に過ぎず、Livewire へのリクエストを直接
     * 細工されれば無視できてしまう。site_id は非スーパー管理者に対してここで
     * サーバー側で強制上書きする（他サイトへの権限昇格を防ぐ）。
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (! ($user instanceof Member && $user->isSuperAdmin())) {
            $data['site_id'] = app(CurrentSite::class)->id();
        }

        return $data;
    }
}
