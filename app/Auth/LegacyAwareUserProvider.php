<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * 旧ASP由来の非bcryptパスワードでもログインでき、成功時に静かにbcryptへ
 * 移行する UserProvider。
 *
 * config/auth.php の providers.users.driver をこの 'legacy-aware-eloquent' に
 * することで、Breeze の /login・Filament の /admin/login どちらの経路でも
 * 同じ挙動になる（旧ASP自身の「初回ログイン成功時に新形式へ移行」方式を踏襲）。
 *
 * @see LegacyPasswordVerifier 旧形式（PBKDF2-HMAC-MD5 / 無ソルトMD5切り詰め）の検証
 */
class LegacyAwareUserProvider extends EloquentUserProvider
{
    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials): bool
    {
        $plain = $credentials['password'] ?? null;

        if ($plain === null || $plain === '') {
            return false;
        }

        $hashed = $user->getAuthPassword();

        if (self::isBcrypt($hashed)) {
            return $this->hasher->check($plain, $hashed);
        }

        // Laravel 12 の Hash::check() は非bcryptを渡すと例外を投げるため、
        // ここに来る時点で hasher は使わず旧形式チェックに回す。
        return LegacyPasswordVerifier::verify((string) $plain, $hashed) > 0;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false): void
    {
        $hashed = $user->getAuthPassword();

        if (! self::isBcrypt($hashed)) {
            // 旧形式 → bcrypt へ移行
            $user->forceFill([
                $user->getAuthPasswordName() => $this->hasher->make($credentials['password']),
            ])->save();

            return;
        }

        parent::rehashPasswordIfRequired($user, $credentials, $force);
    }

    private static function isBcrypt(mixed $hashed): bool
    {
        return is_string($hashed) && (str_starts_with($hashed, '$2y$') || str_starts_with($hashed, '$2b$') || str_starts_with($hashed, '$2a$'));
    }
}
