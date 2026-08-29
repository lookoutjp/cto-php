<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $this->ensureRegistrationOpen();

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * 旧ASP reguser_*.asp 相当。現在のサイトに対して「コンテンツ閲覧のみ（ninshou = 0）」の
     * 会員として登録する。プロジェクト機能を使うには管理員が member_room の ninshou を
     * 1（参加者）以上に引き上げる必要がある（＝旧ASP の「本承認」）。
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureRegistrationOpen();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nameread' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Member::class.',email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [], [
            'name' => 'お名前', 'nameread' => 'ふりがな', 'email' => 'メールアドレス',
            'phone' => '電話番号', 'password' => 'パスワード',
        ]);

        $siteId = app(CurrentSite::class)->id();

        $user = DB::transaction(function () use ($data, $siteId) {
            $member = Member::create([
                // member_id は旧Acc ではオートナンバーでなく手動発行の文字列キーだったため UUID を使う。
                'member_id' => (string) Str::uuid(),
                'name' => $data['name'],
                'nameread' => $data['nameread'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'regtime' => now(),
            ]);

            MemberRoom::updateOrCreate(
                ['member_id' => $member->getKey(), 'site_id' => $siteId],
                ['ninshou' => 0], // コンテンツ閲覧のみ。管理員が承認して引き上げる。
            );

            return $member;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))
            ->with('status', 'ご登録ありがとうございます。プロジェクト機能のご利用には管理員の承認が必要です。');
    }

    private function ensureRegistrationOpen(): void
    {
        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction('newmemberregfunction')) {
            throw new NotFoundHttpException;
        }
    }
}
