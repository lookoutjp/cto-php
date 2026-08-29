<?php

namespace App\Auth\Passwords;

use Illuminate\Auth\Passwords\PasswordBrokerManager;

/**
 * config/auth.php の passwords.users.driver = 'member_tokens' が指定された場合に
 * MemberTokenRepository を使うよう、標準のPasswordBrokerManagerを拡張したもの。
 * app/Providers/AppServiceProvider.php の register() で 'auth.password' に差し替えている。
 */
class CustomPasswordBrokerManager extends PasswordBrokerManager
{
    protected function createTokenRepository(array $config)
    {
        if (($config['driver'] ?? null) === 'member_tokens') {
            return new MemberTokenRepository(
                $config['expire'] ?? 60,
                $config['throttle'] ?? 60,
            );
        }

        return parent::createTokenRepository($config);
    }
}
