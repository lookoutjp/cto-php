<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsItem;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NewsController extends Controller
{
    public function show(NewsItem $news): View
    {
        // 未公開（newsdate が未来）は 404。BelongsToSite が他サイト分は既に除外している。
        if ($news->newsdate === null || $news->newsdate->isFuture()) {
            throw new NotFoundHttpException;
        }

        $news->increment('clicks');

        $prev = NewsItem::query()->published()
            ->where('newsdate', '<', $news->newsdate)
            ->orderByDesc('newsdate')->first();

        $next = NewsItem::query()->published()
            ->where('newsdate', '>', $news->newsdate)
            ->where('newsdate', '<=', now())
            ->orderBy('newsdate')->first();

        return view('public.news-show', compact('news', 'prev', 'next'));
    }
}
