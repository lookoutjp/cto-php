<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInquiryRequest;
use App\Mail\InquiryAdminNotice;
use App\Mail\InquiryConfirmation;
use App\Models\Inquiry;
use App\Models\Member;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InquiryController extends Controller
{
    public function create(Request $request): View
    {
        $this->ensureEnabled();

        $user = $request->user();
        $prefill = $user instanceof Member ? [
            'customer_name' => $user->name,
            'customer_nameread' => $user->nameread,
            'address' => $user->address,
            'code' => $user->code,
            'phone' => $user->phone,
            'dayphone' => $user->dayphone,
            'email' => $user->email,
        ] : [];

        return view('public.inquiry-form', ['prefill' => $prefill, ...$this->newCaptcha($request)]);
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        $this->ensureEnabled();

        // ロボット対策。人間には見えないおとり欄（website）が埋まっている、または
        // フォーム表示から極端に早い送信は、成功したように見せて静かに捨てる
        // （エラーを返すとボットに学習・調整の手がかりを与えてしまうため）。
        if (filled($request->input('website')) || $this->submittedTooFast($request)) {
            $request->session()->forget(['inquiry_captcha_answer', 'inquiry_captcha_shown_at']);

            return redirect()->route('contact.thanks')->with([
                'inquiry_ticket' => '-',
                'inquiry_email' => (string) $request->input('email'),
            ]);
        }

        // 簡単な計算式で人間確認。
        $expectedAnswer = $request->session()->get('inquiry_captcha_answer');
        if ($expectedAnswer === null || (int) $request->input('captcha_answer') !== (int) $expectedAnswer) {
            return back()->withInput()->withErrors([
                'captcha_answer' => '計算の答えが正しくありません。もう一度お試しください。',
            ]);
        }
        $request->session()->forget(['inquiry_captcha_answer', 'inquiry_captcha_shown_at']);

        $site = $this->site();

        $inquiry = new Inquiry($request->validated());
        $inquiry->member_id = $request->user()?->getKey();
        $inquiry->create_date = now();
        $inquiry->state = 0;
        $inquiry->save(); // BelongsToSite が site_id を自動セット

        try {
            Mail::to($inquiry->email)->send(
                new InquiryConfirmation($inquiry, $site?->sitename ?? config('app.name'), $site?->sitedomain)
            );

            $adminMail = $site?->site_mail ?: $site?->comemail;
            if ($adminMail) {
                Mail::to($adminMail)->send(
                    new InquiryAdminNotice($inquiry, $site?->sitename ?? config('app.name'))
                );
            }
        } catch (\Throwable $e) {
            report($e); // メール失敗でも受付自体は成立させる
        }

        return redirect()->route('contact.thanks')->with([
            'inquiry_ticket' => $inquiry->ticket_number,
            'inquiry_email' => $inquiry->email,
        ]);
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('inquiry_ticket')) {
            return redirect()->route('contact.create');
        }

        return view('public.inquiry-thanks', [
            'ticket' => $request->session()->get('inquiry_ticket'),
            'email' => $request->session()->get('inquiry_email'),
        ]);
    }

    private function ensureEnabled(): void
    {
        if (! $this->site()?->hasFunction('otoiawasefunction')) {
            throw new NotFoundHttpException;
        }
    }

    private function site(): ?Room
    {
        return Room::find(app(CurrentSite::class)->id());
    }

    /**
     * ボット対策の簡単な計算式チャレンジを生成し、セッションに正解と表示時刻を保存する。
     *
     * @return array{captchaA: int, captchaB: int}
     */
    private function newCaptcha(Request $request): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        $request->session()->put('inquiry_captcha_answer', $a + $b);
        $request->session()->put('inquiry_captcha_shown_at', now()->timestamp);

        return ['captchaA' => $a, 'captchaB' => $b];
    }

    /** フォーム表示から数秒未満での送信は人間には早すぎるためボットとみなす。 */
    private function submittedTooFast(Request $request): bool
    {
        $shownAt = $request->session()->get('inquiry_captcha_shown_at');
        if ($shownAt === null) {
            return false;
        }

        return (now()->timestamp - (int) $shownAt) < 3;
    }
}
