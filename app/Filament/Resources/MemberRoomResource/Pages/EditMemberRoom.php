<?php

namespace App\Filament\Resources\MemberRoomResource\Pages;

use App\Filament\Resources\MemberRoomResource;
use App\Models\Member;
use App\Support\CurrentSite;
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

    /**
     * フォームの disabled() はUI上の制約に過ぎず、Livewire へのリクエストを直接
     * 細工されれば無視できてしまう。site_id は非スーパー管理者に対してここで
     * サーバー側で強制上書きする（既存の自サイト行を他サイトへ付け替える攻撃を防ぐ）。
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        if (! ($user instanceof Member && $user->isSuperAdmin())) {
            $data['site_id'] = app(CurrentSite::class)->id();
        }

        return $data;
    }
}
