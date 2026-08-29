<?php

namespace App\Auth\Passwords;

use Carbon\Carbon;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * 旧Access由来の password_reset_tokens テーブル
 * (member_id, site_id, token, createdt, expires) に合わせたトークンリポジトリ。
 *
 * Laravel標準のDatabaseTokenRepositoryはemail/token/created_atという
 * カラム構成を前提にしているため、そのままでは使えず、これを自作している。
 * config/auth.php の passwords.users.driver = 'member_tokens' で有効化。
 * 登録は AppServiceProvider::boot() の Password::extend() で行っている。
 */
class MemberTokenRepository implements TokenRepositoryInterface
{
    protected string $table = 'password_reset_tokens';

    public function __construct(
        protected int $expireMinutes = 60,
        protected int $throttleSeconds = 60,
    ) {
    }

    public function create(CanResetPassword $user): string
    {
        $memberId = $user->getAuthIdentifier();

        $this->deleteExisting($memberId);

        $token = $this->createNewToken();

        DB::table($this->table)->insert([
            'member_id' => $memberId,
            'site_id' => null,
            'token' => Hash::make($token),
            'createdt' => Carbon::now(),
            'expires' => Carbon::now()->addMinutes($this->expireMinutes),
        ]);

        return $token;
    }

    public function exists(CanResetPassword $user, #[\SensitiveParameter] $token): bool
    {
        $record = $this->firstRecord($user->getAuthIdentifier());

        return $record
            && ! $this->expired($record->expires ?? null)
            && Hash::check($token, $record->token);
    }

    public function recentlyCreatedToken(CanResetPassword $user): bool
    {
        $record = $this->firstRecord($user->getAuthIdentifier());

        return $record && $this->recentlyCreated($record->createdt ?? null);
    }

    public function delete(CanResetPassword $user): void
    {
        $this->deleteExisting($user->getAuthIdentifier());
    }

    public function deleteExpired(): void
    {
        DB::table($this->table)->where('expires', '<', Carbon::now())->delete();
    }

    protected function firstRecord(string $memberId): ?object
    {
        return DB::table($this->table)->where('member_id', $memberId)->first();
    }

    protected function deleteExisting(string $memberId): void
    {
        DB::table($this->table)->where('member_id', $memberId)->delete();
    }

    protected function expired(?string $expires): bool
    {
        if (! $expires) {
            return true;
        }

        return Carbon::parse($expires)->isPast();
    }

    protected function recentlyCreated(?string $createdt): bool
    {
        if (! $createdt) {
            return false;
        }

        return Carbon::parse($createdt)->addSeconds($this->throttleSeconds)->isFuture();
    }

    public function createNewToken(): string
    {
        return hash_hmac('sha256', Str::random(40), config('app.key'));
    }
}
