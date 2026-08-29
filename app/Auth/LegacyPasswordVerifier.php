<?php

namespace App\Auth;

/**
 * 旧ASP(include/passwordhash.asp, include/md5.asp)が使っていたパスワード保存形式を検証する。
 *
 * 旧システムには2形式が混在している。
 *   1) 新形式: "salt$hash" … PBKDF2-HMAC-MD5相当（反復250回、salt8byte、鍵長16byte）
 *      PHPのhash_pbkdf2()は標準PBKDF2実装であり、旧VBScript実装(PwHash_PBKDF2)と
 *      アルゴリズム的に完全に一致するため、そのまま使って検証できる。
 *   2) 旧形式: 無ソルトMD5を16文字(8byte)に切り詰めたもの
 *
 * verify()が2(旧形式一致)を返した場合、呼び出し側でbcryptへの再ハッシュ移行を行うこと。
 */
class LegacyPasswordVerifier
{
    private const ITERATIONS = 250;
    private const KEY_BYTES = 16;

    /**
     * @return int 0=不一致 / 1=新形式(salt$hash)で一致 / 2=旧形式(16文字MD5)で一致
     */
    public static function verify(string $plain, ?string $stored): int
    {
        if (! $stored) {
            return 0;
        }

        if (str_contains($stored, '$')) {
            [$saltHex, $expected] = explode('$', $stored, 2);
            $actual = self::pbkdf2($plain, $saltHex);

            return hash_equals($expected, $actual) ? 1 : 0;
        }

        // 旧形式: 無ソルトMD5の先頭16文字
        $legacy = substr(md5($plain), 0, 16);

        return hash_equals($stored, $legacy) ? 2 : 0;
    }

    private static function pbkdf2(string $plain, string $saltHex): string
    {
        $salt = hex2bin($saltHex);
        if ($salt === false) {
            return '';
        }

        return bin2hex(hash_pbkdf2('md5', $plain, $salt, self::ITERATIONS, self::KEY_BYTES, true));
    }
}
