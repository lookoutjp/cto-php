<?php

use App\Http\Controllers\Member\TaskController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Member\TaskList;
use App\Http\Controllers\Public\ContentController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InquiryController;
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

Route::get('/contact', [InquiryController::class, 'create'])->name('contact.create');
Route::post('/contact', [InquiryController::class, 'store'])->name('contact.store');
Route::get('/contact/thanks', [InquiryController::class, 'thanks'])->name('contact.thanks');

// ---- 会員（Breeze）----
// Mypage（旧 Mypage.asp）: ログイン後の入口。route 名は Breeze 互換のため 'dashboard' のまま。
Route::get('/mypage', MypageController::class)->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 業務系タスク（todo / problem / risk） — 旧 todo.asp / Problem.asp / Risk.asp
    Route::whereIn('kind', ['todo', 'problem', 'risk'])->group(function () {
        Route::get('/tasks/{kind}', TaskList::class)->name('tasks.index');
        Route::get('/tasks/{kind}/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks/{kind}', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{kind}/{id}', [TaskController::class, 'show'])->whereNumber('id')->name('tasks.show');
        Route::get('/tasks/{kind}/{id}/edit', [TaskController::class, 'edit'])->whereNumber('id')->name('tasks.edit');
        Route::put('/tasks/{kind}/{id}', [TaskController::class, 'update'])->whereNumber('id')->name('tasks.update');
        Route::delete('/tasks/{kind}/{id}', [TaskController::class, 'destroy'])->whereNumber('id')->name('tasks.destroy');
    });
});

require __DIR__.'/auth.php';
