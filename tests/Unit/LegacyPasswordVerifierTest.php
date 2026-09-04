<?php

namespace Tests\Unit;

use App\Auth\LegacyPasswordVerifier;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 旧ASPのパスワード保存形式（新形式 PBKDF2-HMAC-MD5 "salt$hash" / 旧形式 無ソルトMD5切り詰め）
 * の検証ロジック。既存会員が本サービスへ移行後もログインできる根幹なので個別にテストする。
 */
class LegacyPasswordVerifierTest extends TestCase
{
    #[Test]
    public function 旧形式_無ソルト_m_d5切り詰め_は一致すれば2を返す(): void
    {
        $plain = 'hunter2';
        $stored = substr(md5($plain), 0, 16);

        $this->assertSame(2, LegacyPasswordVerifier::verify($plain, $stored));
    }

    #[Test]
    public function 旧形式は不一致なら0を返す(): void
    {
        $stored = substr(md5('correct-password'), 0, 16);

        $this->assertSame(0, LegacyPasswordVerifier::verify('wrong-password', $stored));
    }

    #[Test]
    public function 新形式_saltドル記号ハッシュ_は一致すれば1を返す(): void
    {
        $plain = 'hunter2';
        $saltHex = bin2hex(random_bytes(8));
        $hash = bin2hex(hash_pbkdf2('md5', $plain, hex2bin($saltHex), 250, 16, true));
        $stored = "{$saltHex}\${$hash}";

        $this->assertSame(1, LegacyPasswordVerifier::verify($plain, $stored));
    }

    #[Test]
    public function 新形式は不一致なら0を返す(): void
    {
        $saltHex = bin2hex(random_bytes(8));
        $hash = bin2hex(hash_pbkdf2('md5', 'correct-password', hex2bin($saltHex), 250, 16, true));
        $stored = "{$saltHex}\${$hash}";

        $this->assertSame(0, LegacyPasswordVerifier::verify('wrong-password', $stored));
    }

    #[Test]
    public function 空または未設定のパスワードは0を返す(): void
    {
        $this->assertSame(0, LegacyPasswordVerifier::verify('anything', null));
        $this->assertSame(0, LegacyPasswordVerifier::verify('anything', ''));
    }
}
