<?php

use App\Http\Controllers\Member\BoardController;
use App\Http\Controllers\Member\MemberListController;
use App\Http\Controllers\Member\RoutineWorkController;
use App\Http\Controllers\Member\SurveyController;
use App\Http\Controllers\Member\TaskController;
use App\Http\Controllers\Member\WbsController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureProjectMember;
use App\Livewire\Member\TaskList;
use App\Http\Controllers\Public\ContentController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InquiryController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\SitePageController;
use App\Livewire\Public\NewsIndex;
use Illuminate\Support\Facades\Route;

// ---- 公開フロント（旧ASP: index / news / contents / faq 相当）----
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/news', NewsIndex::class)->name('news.index');
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

Route::get('/contents', [ContentController::class, 'index'])->name('contents.index');
Route::get('/contents/{content}', [ContentController::class, 'show'])->name('contents.show');

Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

// 管理員の言葉 / リンク集（旧 managerwords.asp / friendlink）
Route::get('/manager', [SitePageController::class, 'managerWords'])->name('manager-words');
Route::get('/links', [SitePageController::class, 'links'])->name('links.index');

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

    // 業務系（TODO / 課題 / リスク / WBS / サーベイ） — プロジェクト参加者(ninshou 1 or -1)のみ
    Route::middleware(EnsureProjectMember::class)->group(function () {
        Route::whereIn('kind', \App\Support\TaskKind::slugs())->group(function () {
            Route::get('/tasks/{kind}', TaskList::class)->name('tasks.index');
            Route::get('/tasks/{kind}/create', [TaskController::class, 'create'])->name('tasks.create');
            Route::post('/tasks/{kind}', [TaskController::class, 'store'])->name('tasks.store');
            Route::get('/tasks/{kind}/{id}', [TaskController::class, 'show'])->whereNumber('id')->name('tasks.show');
            Route::get('/tasks/{kind}/{id}/edit', [TaskController::class, 'edit'])->whereNumber('id')->name('tasks.edit');
            Route::put('/tasks/{kind}/{id}', [TaskController::class, 'update'])->whereNumber('id')->name('tasks.update');
            Route::delete('/tasks/{kind}/{id}', [TaskController::class, 'destroy'])->whereNumber('id')->name('tasks.destroy');
        });

        // WBS（階層） — 旧 wbs.asp / WbsAdd.asp / WbsDetail.asp
        Route::get('/wbs', [WbsController::class, 'index'])->name('wbs.index');
        Route::get('/wbs/check', [WbsController::class, 'check'])->name('wbs.check');
        Route::get('/wbs/schedule', [WbsController::class, 'schedule'])->name('wbs.schedule');
        Route::get('/wbs/load', [WbsController::class, 'load'])->name('wbs.load');
        Route::post('/wbs/schedule/apply', [WbsController::class, 'applySchedule'])->name('wbs.schedule.apply');
        Route::get('/wbs/holidays', [WbsController::class, 'holidays'])->name('wbs.holidays');
        Route::post('/wbs/holidays', [WbsController::class, 'storeHoliday'])->name('wbs.holidays.store');
        Route::delete('/wbs/holidays/{id}', [WbsController::class, 'destroyHoliday'])->whereNumber('id')->name('wbs.holidays.destroy');
        Route::get('/wbs/create', [WbsController::class, 'create'])->name('wbs.create');
        Route::post('/wbs', [WbsController::class, 'store'])->name('wbs.store');
        Route::get('/wbs/{id}', [WbsController::class, 'show'])->whereNumber('id')->name('wbs.show');
        Route::get('/wbs/{id}/edit', [WbsController::class, 'edit'])->whereNumber('id')->name('wbs.edit');
        Route::put('/wbs/{id}', [WbsController::class, 'update'])->whereNumber('id')->name('wbs.update');
        Route::delete('/wbs/{id}', [WbsController::class, 'destroy'])->whereNumber('id')->name('wbs.destroy');
        Route::post('/wbs/reorder', [WbsController::class, 'reorder'])->name('wbs.reorder');

        // サーベイ（アンケート） — 旧 SurveyList_My.asp / Survey.asp / SurveyList_Mytask.asp
        Route::get('/surveys', [SurveyController::class, 'index'])->name('surveys.index');
        Route::get('/surveys/manage', [SurveyController::class, 'manage'])->name('surveys.manage');
        Route::get('/surveys/create', [SurveyController::class, 'create'])->name('surveys.create');
        Route::post('/surveys', [SurveyController::class, 'store'])->name('surveys.store');
        Route::get('/surveys/{id}', [SurveyController::class, 'show'])->whereNumber('id')->name('surveys.show');
        Route::get('/surveys/{id}/edit', [SurveyController::class, 'edit'])->whereNumber('id')->name('surveys.edit');
        Route::put('/surveys/{id}', [SurveyController::class, 'update'])->whereNumber('id')->name('surveys.update');
        Route::delete('/surveys/{id}', [SurveyController::class, 'destroy'])->whereNumber('id')->name('surveys.destroy');
        Route::post('/surveys/{id}/toggle-open', [SurveyController::class, 'toggleOpen'])->whereNumber('id')->name('surveys.toggle-open');
        Route::post('/surveys/{id}/answer', [SurveyController::class, 'answer'])->whereNumber('id')->name('surveys.answer');

        // 定例作業の一括生成 — 旧 RoutineWorkMake.asp
        Route::get('/routinework/generate', [RoutineWorkController::class, 'generateForm'])->name('routinework.generate');
        Route::post('/routinework/generate', [RoutineWorkController::class, 'generate'])->name('routinework.generate.run');

        // メンバー一覧 — 旧 memberlist.asp
        Route::get('/members', [MemberListController::class, 'index'])->name('members.index');

        // 掲示板（コミュニティ） — 旧 meetlist.asp / meet.asp / meet_disp.asp / meetadd.asp / meet_re.asp
        Route::get('/board', [BoardController::class, 'index'])->name('board.index');
        Route::get('/board/threads/{thread}', [BoardController::class, 'show'])->whereNumber('thread')->name('board.show');
        Route::post('/board/threads/{thread}/reply', [BoardController::class, 'reply'])->whereNumber('thread')->name('board.reply');
        Route::get('/board/categories/{category}', [BoardController::class, 'category'])->whereNumber('category')->name('board.category');
        Route::get('/board/categories/{category}/new', [BoardController::class, 'create'])->whereNumber('category')->name('board.create');
        Route::post('/board/categories/{category}', [BoardController::class, 'store'])->whereNumber('category')->name('board.store');
    });
});

require __DIR__.'/auth.php';
