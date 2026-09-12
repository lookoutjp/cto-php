<?php

namespace App\Filament\Resources\LevelResource\Pages;

use App\Filament\Resources\LevelResource;
use App\Models\Level;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLevel extends EditRecord
{
    protected static string $resource = LevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                // サブレベルが残っている状態で削除すると、それらが孤児（親不明）になってしまうため禁止する。
                ->before(function (Actions\DeleteAction $action, Level $record) {
                    if (Level::query()->where('fatherlevel', $record->level)->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('削除できません')
                            ->body('このレベルにはサブレベルが存在します。先にサブレベルを削除するか、親を付け替えてください。')
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
