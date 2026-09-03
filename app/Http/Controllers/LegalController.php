<?php

namespace App\Http\Controllers;

use App\Support\Plans;
use Illuminate\Contracts\View\View;

/**
 * サービス共通の法務ページ（テナント非依存）。
 * 特定商取引法に基づく表記 / 利用規約 / プライバシーポリシー。
 */
class LegalController extends Controller
{
    public function tokushoho(): View
    {
        return view('legal.tokushoho', [
            'legal' => config('legal'),
            'plans' => Plans::all(),
        ]);
    }

    public function terms(): View
    {
        return view('legal.terms', ['legal' => config('legal')]);
    }

    public function privacy(): View
    {
        return view('legal.privacy', ['legal' => config('legal')]);
    }
}
