<?php

namespace App\Filament\Concerns;

use App\Support\BackUrl;

/**
 * 管理者モードの編集アイコンから `?back=` 付きで遷移してきた場合、
 * 保存後に元のページへ戻す。付いていなければ既定の遷移（Filament標準）のまま。
 */
trait RedirectsEditBackToOrigin
{
    public ?string $backTo = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->backTo = BackUrl::sanitize(request()->query('back'));
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->backTo ?? parent::getRedirectUrl();
    }
}
