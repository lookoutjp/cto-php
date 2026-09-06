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
     * 旧ASP reguser_*.asp 相当。会員（members）はプラットフォーム全体で一元管理し、
     * 現在のサイトに対しては「加入申請中（承認待ち）」の member_room 行を作る。
     * サイト管理員が「会員権限」画面で承認すると ninshou（閲覧のみ/参加者/管理員）が付与される。
     * members.signup_site に登録元サイトを記録する。
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureRegistrationOpen();

        $room = Room::find(app(CurrentSite::class)->id());
        if ($room && ! $room->canAddMembers(1)) {
            throw ValidationException::withMessages([
                'email' => 'このサイトは現在のプランの会員数上限に達しているため、新規登録を受け付けていません。サイト管理者にお問い合わせください。',
            ]);
        }

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
                'signup_site' => $siteId,
                'name' => $data['name'],
                'nameread' => $data['nameread'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'regtime' => now(),
            ]);

            // 登録＝そのサイトへの「加入申請」。管理員が「会員権限」画面で承認すると
            // ninshou が付与される（承認待ちの間は applied_at 有・approved_at 無・ninshou NULL）。
            MemberRoom::query()->withoutGlobalScope('confirmed')->updateOrCreate(
                ['member_id' => $member->getKey(), 'site_id' => $siteId],
                ['ninshou' => null, 'applied_at' => now(), 'approved_at' => null],
            );

            return $member;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))
            ->with('status', 'ご登録ありがとうございます。サイト管理員の承認をお待ちください。承認までの間も公開コンテンツはご覧いただけます。');
    }

    private function ensureRegistrationOpen(): void
    {
        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction('newmemberregfunction')) {
            throw new NotFoundHttpException;
        }
    }
}
