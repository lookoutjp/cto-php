<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * セルフサーブのテナント（サイト）作成。旧ASPには存在しない新機能。
 *
 * これまで新規テナント（rooms 行）は運営者（スーパー管理者）が Filament から
 * 手作業で作成するしかなかった。ここでは「新しい会社が自分でワークスペースを
 * 作成する」導線を提供する: rooms + members + member_room(ninshou=-1) を
 * まとめて作成し、そのまま管理員としてログインさせる。
 *
 * 新テナントは free プラン・書き込み系機能を一通り有効にした状態で始まる。
 * 制約: 現状カスタムドメイン/サブドメインの自動払い出しは無いため、新テナントの
 * 「公開フロント」(`/` 等はホスト名で解決) にはまだ独自URLで到達できない。
 * 会員向け機能（/admin, /dashboard, /tasks, /wbs 等）はログイン後すぐ使える
 * （テナント解決はセッションベースのため）。
 */
class TenantSignupController extends Controller
{
    /** 新規テナント作成時に有効化する機能一式（内部の共同作業機能。公開サイト系は含めない）。 */
    private const DEFAULT_FUNCTIONS = 'todofunction,problemfunction,riskfunction,productfunction,'
        .'routineworkfunction,changefunction,wbsfunction,memberlistfunction,onlinemembersfunction,'
        .'dengonfunction,filemanagefunction,surveyfunction,freeguestbookfunction';

    /** site_id として使わせない予約語（既存テナントIDや紛らわしい語）。 */
    private const RESERVED_SITE_IDS = [
        'www', 'admin', 'api', 'app', 'null', 'undefined', 'root', 'system',
        'support', 'test', 'demo', 'staging', 'production', 'signup', 'login',
        'register', 'legal', 'stripe', 'mail', 'ftp', 'localhost',
    ];

    public function create(): View
    {
        return view('auth.tenant-signup');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sitename' => ['required', 'string', 'max:250'],
            'site_id' => [
                'required', 'string', 'min:3', 'max:30',
                'regex:/^[a-z][a-z0-9-]*$/',
                Rule::notIn(self::RESERVED_SITE_IDS),
                Rule::unique(Room::class, 'site_id'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Member::class.',email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [], [
            'sitename' => '会社名・サイト名', 'site_id' => 'サイトID',
            'name' => 'お名前', 'email' => 'メールアドレス', 'password' => 'パスワード',
        ]);

        $member = DB::transaction(function () use ($data) {
            Room::create([
                'site_id' => $data['site_id'],
                'sitename' => $data['sitename'],
                'site_joutai' => 1,
                'function_list' => self::DEFAULT_FUNCTIONS,
            ]);

            $member = Member::create([
                'member_id' => (string) Str::uuid(),
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'regtime' => now(),
            ]);

            MemberRoom::create([
                'member_id' => $member->getKey(),
                'site_id' => $data['site_id'],
                'ninshou' => -1, // 作成者はそのテナントの管理員
            ]);

            return $member;
        });

        event(new Registered($member));

        Auth::login($member);
        // ResolveCurrentSite が「管理員として1サイトのみ所属」を見て admin_site_id を自動的に
        // このテナントへ確定させるため、ここでセッションを直接いじる必要はない。

        return redirect('/admin')
            ->with('status', "「{$data['sitename']}」を作成しました。まずはメンバーの招待やプランの確認から始めましょう。");
    }
}
