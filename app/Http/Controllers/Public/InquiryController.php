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
use Illuminate\Support\Facades\Http;
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

        $request->session()->put('inquiry_form_shown_at', now()->timestamp);

        return view('public.inquiry-form', [
            'prefill' => $prefill,
            'turnstileSiteKey' => config('services.turnstile.site_key'),
        ]);
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        $this->ensureEnabled();

        // ロボット対策。人間には見えないおとり欄（website）が埋まっている、または
        // フォーム表示から極端に早い送信は、成功したように見せて静かに捨てる
        // （エラーを返すとボットに学習・調整の手がかりを与えてしまうため）。
        if (filled($request->input('website')) || $this->submittedTooFast($request)) {
            $request->session()->forget('inquiry_form_shown_at');

            return redirect()->route('contact.thanks')->with([
                'inquiry_ticket' => '-',
                'inquiry_email' => (string) $request->input('email'),
            ]);
        }

        // Cloudflare Turnstile で人間確認（サイトキー未設定＝ローカル開発時などは検証をスキップ）。
        if (! $this->verifyTurnstile($request)) {
            return back()->withInput()->withErrors([
                'cf-turnstile-response' => 'ロボットではないことの確認に失敗しました。もう一度お試しください。',
            ]);
        }
        $request->session()->forget('inquiry_form_shown_at');

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

    /** フォーム表示から数秒未満での送信は人間には早すぎるためボットとみなす。 */
    private function submittedTooFast(Request $request): bool
    {
        $shownAt = $request->session()->get('inquiry_form_shown_at');
        if ($shownAt === null) {
            return false;
        }

        return (now()->timestamp - (int) $shownAt) < 3;
    }

    /**
     * Cloudflare Turnstile のトークンをサーバー側で検証する。
     * シークレットキー未設定（ローカル開発など）の場合は検証をスキップして true を返す。
     */
    private function verifyTurnstile(Request $request): bool
    {
        $secret = config('services.turnstile.secret_key');
        if (blank($secret)) {
            return true;
        }

        $token = (string) $request->input('cf-turnstile-response');
        if ($token === '') {
            return false;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        return (bool) $response->json('success');
    }
}
