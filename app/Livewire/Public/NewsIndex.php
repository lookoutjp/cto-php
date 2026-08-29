<?php

namespace App\Livewire\Public;

use App\Models\NewsItem;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * 公開ニュース一覧（旧ASP news.asp 相当）。
 * サイト絞り込みは NewsItem の BelongsToSite グローバルスコープが行う。
 */
class NewsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $keyword = '';

    public function updatedKeyword(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $news = NewsItem::query()
            ->published()
            ->when($this->keyword !== '', fn ($q) => $q->where('title', 'ilike', '%'.$this->keyword.'%'))
            ->listingOrder()
            ->paginate(32);

        return view('livewire.public.news-index', ['news' => $news])
            ->layout('components.layouts.public', ['title' => 'ニュース']);
    }
}
