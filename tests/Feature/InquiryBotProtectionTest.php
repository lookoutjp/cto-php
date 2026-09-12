<?php

namespace Tests\Feature;

use App\Mail\InquiryAdminNotice;
use App\Mail\InquiryConfirmation;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * /contact のボット対策（おとり欄・早すぎる送信・Cloudflare Turnstile）。
 */
class InquiryBotProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1,
            'function_list' => 'otoiawasefunction', 'site_mail' => 'admin@example.test',
        ]);
        app(CurrentSite::class)->set('www');
    }

    private function baseData(): array
    {
        return [
            'customer_name' => '山田太郎',
            'email' => 'taro@example.test',
            'remark' => 'お問い合わせ内容です。',
        ];
    }

    /** テストの実行速度自体が「早すぎる送信」判定に引っかからないよう、表示時刻を過去にずらす。 */
    private function backdateFormShownAt(): void
    {
        session(['inquiry_form_shown_at' => now()->subSeconds(10)->timestamp]);
    }

    public function test_valid_turnstile_token_submits_successfully(): void
    {
        Mail::fake();
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

        $this->get(route('contact.create'));
        $this->backdateFormShownAt();

        $this->post(route('contact.store'), $this->baseData() + ['cf-turnstile-response' => 'valid-token'])
            ->assertRedirect(route('contact.thanks'));

        $this->assertDatabaseHas('inquiries', ['email' => 'taro@example.test']);
        Mail::assertSent(InquiryConfirmation::class);
        Mail::assertSent(InquiryAdminNotice::class);
    }

    public function test_invalid_turnstile_token_is_rejected(): void
    {
        Mail::fake();
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false])]);

        $this->get(route('contact.create'));
        $this->backdateFormShownAt();

        $this->post(route('contact.store'), $this->baseData() + ['cf-turnstile-response' => 'bad-token'])
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('inquiries', ['email' => 'taro@example.test']);
        Mail::assertNothingSent();
    }

    public function test_missing_turnstile_token_is_rejected_when_configured(): void
    {
        Mail::fake();
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

        $this->get(route('contact.create'));
        $this->backdateFormShownAt();

        $this->post(route('contact.store'), $this->baseData())
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('inquiries', ['email' => 'taro@example.test']);
    }

    public function test_turnstile_verification_is_skipped_when_not_configured(): void
    {
        Mail::fake();
        config(['services.turnstile.secret_key' => null]);

        $this->get(route('contact.create'));
        $this->backdateFormShownAt();

        // シークレットキー未設定（ローカル開発など）ならトークンが無くても通す。
        $this->post(route('contact.store'), $this->baseData())
            ->assertRedirect(route('contact.thanks'));

        $this->assertDatabaseHas('inquiries', ['email' => 'taro@example.test']);
    }

    public function test_honeypot_field_silently_drops_the_submission(): void
    {
        Mail::fake();
        config(['services.turnstile.secret_key' => null]);

        $this->get(route('contact.create'));
        $this->backdateFormShownAt();

        // ボットは正しいトークンを持っていなくても、おとり欄を埋めていれば弾かれる。
        $this->post(route('contact.store'), $this->baseData() + [
            'website' => 'https://spam.example.com',
        ])->assertRedirect(route('contact.thanks'));

        $this->assertDatabaseMissing('inquiries', ['email' => 'taro@example.test']);
        Mail::assertNothingSent();
    }

    public function test_submitting_too_fast_is_treated_as_a_bot(): void
    {
        Mail::fake();
        config(['services.turnstile.secret_key' => null]);

        $this->get(route('contact.create'));
        // フォーム表示直後（3秒未満）の送信を装う。backdate しない。

        $this->post(route('contact.store'), $this->baseData())
            ->assertRedirect(route('contact.thanks'));

        $this->assertDatabaseMissing('inquiries', ['email' => 'taro@example.test']);
        Mail::assertNothingSent();
    }
}
