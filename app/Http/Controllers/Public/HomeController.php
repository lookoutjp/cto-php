<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\NewsItem;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $latestNews = NewsItem::query()->published()->listingOrder()->limit(5)->get();

        $site = Room::find(app(CurrentSite::class)->id());

        $recommended = $site?->hasFunction('osusumecontentsfunction')
            ? Content::query()->publiclyVisible()->recommended()->listingOrder()->limit(5)->get()
            : collect();

        $popular = $site?->hasFunction('ninkicontentsfunction')
            ? Content::query()->publiclyVisible()->orderByDesc('clicks')->limit(5)->get()
            : collect();

        // 左サイドバーの「カテゴリ」は全ページ共通（components.layouts.public のコンポーザーが注入）。

        return view('public.home', compact('latestNews', 'recommended', 'popular'));
    }
}
