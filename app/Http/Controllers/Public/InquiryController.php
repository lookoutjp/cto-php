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

        return view('public.inquiry-form', ['prefill' => $prefill]);
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        $this->ensureEnabled();
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
}
