<?php

use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\ContentController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsController;
use App\Livewire\Public\NewsIndex;
use Illuminate\Support\Facades\Route;

// ---- 公開フロント（旧ASP: index / news / contents / faq 相当）----
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/news', NewsIndex::class)->name('news.index');
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

Route::get('/contents', [ContentController::class, 'index'])->name('contents.index');
Route::get('/contents/{content}', [ContentController::class, 'show'])->name('contents.show');

Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

// ---- 会員（Breeze）----
// Mypage（旧 Mypage.asp）: ログイン後の入口。route 名は Breeze 互換のため 'dashboard' のまま。
Route::get('/mypage', MypageController::class)->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
