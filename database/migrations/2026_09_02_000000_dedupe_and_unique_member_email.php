<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 旧Access 由来で members.email が重複していた（office@cto.jp が 10 件など）。
 * ログインは Member::where('email', ...)->first() で最初の1件を拾うため、
 * 重複アカウントは意図しない方でログインしてしまう。
 *
 * - 各重複メールについて「最終ログインが最も新しい」会員を代表として bare email を維持
 * - それ以外は local-part に +<member_id> を付けて一意化（プラスアドレッシング）
 * - members.email に一意インデックスを張る
 */
return new class extends Migration
{
    public function up(): void
    {
        $dupEmails = DB::table('members')
            ->select('email')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->groupBy('email')
            ->havingRaw('count(*) > 1')
            ->pluck('email');

        foreach ($dupEmails as $email) {
            $members = DB::table('members')
                ->where('email', $email)
                ->orderByRaw('loginedtime desc nulls last')
                ->orderByRaw('regtime desc nulls last')
                ->orderBy('member_id')
                ->get(['member_id', 'email']);

            // 先頭（＝代表）は据え置き、残りを一意化
            foreach ($members->skip(1) as $m) {
                DB::table('members')
                    ->where('member_id', $m->member_id)
                    ->update(['email' => $this->suffixed($email, $m->member_id)]);
            }
        }

        Schema::table('members', function (Blueprint $table) {
            $table->unique('email', 'members_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique('members_email_unique');
        });
        // 一意化した local-part の +suffix は戻さない（元データも重複だったため）
    }

    private function suffixed(string $email, string $memberId): string
    {
        $tag = preg_replace('/[^A-Za-z0-9._-]/', '', $memberId);
        $tag = $tag !== '' ? substr($tag, 0, 20) : substr(md5($memberId), 0, 12);

        if (str_contains($email, '@')) {
            [$local, $domain] = explode('@', $email, 2);

            return "{$local}+{$tag}@{$domain}";
        }

        return "{$email}+{$tag}";
    }
};
