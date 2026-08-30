<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\ContentSort;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentController extends Controller
{
    public function index(): View
    {
        $categories = ContentSort::publicTree();

        return view('public.contents-index', compact('categories'));
    }

    public function show(Content $content): View
    {
        $visibleSortIds = ContentSort::query()->publicVisible()->pluck('id');

        if ((int) $content->ok !== 1 || ! $visibleSortIds->contains($content->content_sort)) {
            throw new NotFoundHttpException;
        }

        $content->increment('clicks');
        $content->loadMissing('sort');

        return view('public.contents-show', compact('content'));
    }
}
