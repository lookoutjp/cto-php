<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsItem;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $latestNews = NewsItem::query()->published()->listingOrder()->limit(5)->get();

        return view('public.home', compact('latestNews'));
    }
}
