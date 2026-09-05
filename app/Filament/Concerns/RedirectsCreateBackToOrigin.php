<?php

namespace App\Filament\Concerns;

use App\Support\BackUrl;

/**
 * 管理者モードの「＋追加」から `?back=` 付きで遷移してきた場合、
 * 作成後に元のページへ戻す。付いていなければ既定の遷移（Filament標準）のまま。
 */
trait RedirectsCreateBackToOrigin
{
    public ?string $backTo = null;

    public function mount(): void
    {
        parent::mount();

        $this->backTo = BackUrl::sanitize(request()->query('back'));
    }

    protected function getRedirectUrl(): string
    {
        return $this->backTo ?? parent::getRedirectUrl();
    }
}
