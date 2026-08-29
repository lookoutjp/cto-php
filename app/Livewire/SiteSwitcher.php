<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Filament管理画面のトップバーに置くサイト(テナント)切替セレクタ。
 *
 * 選択肢はログイン中 Member の accessibleSiteIds() に限る（所属サイトのみ／
 * スーパー管理者は全サイト）。選択値は session('site_id') に保存し、
 * ResolveCurrentSite ミドルウェアが次リクエスト以降の CurrentSite に反映する。
 * 切替時はフルリロードして画面全体を再取得させる。
 */
class SiteSwitcher extends Component
{
    public string $siteId = '';

    public function mount(): void
    {
        $this->siteId = app(CurrentSite::class)->id();
    }

    public function updatedSiteId(string $value): void
    {
        if (! $this->allowedSiteIds()->contains($value)) {
            return;
        }

        session(['admin_site_id' => $value]);

        $this->redirect(url()->previous() ?: '/admin');
    }

    public function render(): View
    {
        $allowed = $this->allowedSiteIds();

        $sites = Room::query()
            ->whereIn('site_id', $allowed->all())
            ->orderBy('site_id')
            ->get(['site_id', 'sitename'])
            ->mapWithKeys(fn (Room $r) => [
                $r->site_id => $r->sitename ? "{$r->sitename}（{$r->site_id}）" : $r->site_id,
            ]);

        return view('livewire.site-switcher', ['sites' => $sites]);
    }

    private function allowedSiteIds(): Collection
    {
        $user = auth()->user();

        return $user instanceof Member
            ? $user->manageableSiteIds()
            : collect();
    }
}
