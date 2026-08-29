<?php

namespace App\Livewire;

use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Filament管理画面のトップバーに置くサイト(テナント)切替セレクタ。
 *
 * 選択値は session('site_id') に保存し、ResolveCurrentSite ミドルウェアが
 * 次リクエスト以降の CurrentSite に反映する。切替時はフルリロードして
 * 画面全体（Filamentのテーブル等）を再取得させる。
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
        $valid = Room::query()->pluck('site_id')->all();

        if (! in_array($value, $valid, true)) {
            return;
        }

        session(['site_id' => $value]);

        $this->redirect(url()->previous() ?: '/admin');
    }

    public function render(): View
    {
        return view('livewire.site-switcher', [
            'sites' => Room::query()
                ->orderBy('site_id')
                ->get(['site_id', 'sitename'])
                ->mapWithKeys(fn (Room $r) => [
                    $r->site_id => $r->sitename ? "{$r->sitename}（{$r->site_id}）" : $r->site_id,
                ]),
        ]);
    }
}
