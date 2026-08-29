<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('q', ''));

        $faqs = Faq::query()
            ->when($keyword !== '', fn ($q) => $q
                ->where(fn ($w) => $w
                    ->where('question', 'ilike', '%'.$keyword.'%')
                    ->orWhere('answer', 'ilike', '%'.$keyword.'%')))
            ->orderBy('id')
            ->get();

        return view('public.faq', compact('faqs', 'keyword'));
    }
}
