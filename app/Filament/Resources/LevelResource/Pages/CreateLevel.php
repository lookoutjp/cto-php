<?php

namespace App\Filament\Resources\LevelResource\Pages;

use App\Filament\Resources\LevelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLevel extends CreateRecord
{
    protected static string $resource = LevelResource::class;

    /**
     * 組織図の「サブレベルを追加」リンク（?father=）で指定された親レベルを、
     * フォームの初期値として使うためセッションに記録する。
     * fillForm() が動く前（親の mount() 内）にセットする必要がある。
     */
    public function mount(): void
    {
        if (filled($father = request()->query('father'))) {
            session(['levels.create.father' => (string) $father]);
        }

        parent::mount();
    }

    /**
     * 「保存して、続けて作成」で次のフォームにも同じ親レベルを引き継ぐため、
     * 今回使った親レベルをセッションに記録しておく。
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        session(['levels.create.father' => (string) ($data['fatherlevel'] ?? '0')]);

        return $data;
    }
}
