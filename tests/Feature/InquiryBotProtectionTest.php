<?php

namespace Tests\Feature;

use App\Mail\InquiryAdminNotice;
use App\Mail\InquiryConfirmation;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * /contact のボット対策（おとり欄・早すぎる送信・簡単な計算式）。
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
    private function backdateCaptchaShownAt(): void
    {
        session(['inquiry_captcha_shown_at' => now()->subSeconds(10)->timestamp]);
    }

    public function test_correct_captcha_submits_successfully(): void
    {
        Mail::fake();

        $this->get(route('contact.create'));
        $answer = (int) session('inquiry_captcha_answer');
        $this->backdateCaptchaShownAt();

        $this->post(route('contact.store'), $this->baseData() + ['captcha_answer' => $answer])
            ->assertRedirect(route('contact.thanks'));

        $this->assertDatabaseHas('inquiries', ['email' => 'taro@example.test']);
        Mail::assertSent(InquiryConfirmation::class);
        Mail::assertSent(InquiryAdminNotice::class);
    }

    public function test_wrong_captcha_answer_is_rejected(): void
    {
        Mail::fake();

        $this->get(route('contact.create'));
        $this->backdateCaptchaShownAt();

        $this->post(route('contact.store'), $this->baseData() + ['captcha_answer' => -1])
            ->assertSessionHasErrors('captcha_answer');

        $this->assertDatabaseMissing('inquiries', ['email' => 'taro@example.test']);
        Mail::assertNothingSent();
    }

    public function test_honeypot_field_silently_drops_the_submission(): void
    {
        Mail::fake();

        $this->get(route('contact.create'));
        $answer = (int) session('inquiry_captcha_answer');
        $this->backdateCaptchaShownAt();

        // ボットは正しい答えを入れて来ても、おとり欄を埋めていれば弾かれる。
        $this->post(route('contact.store'), $this->baseData() + [
            'captcha_answer' => $answer,
            'website' => 'https://spam.example.com',
        ])->assertRedirect(route('contact.thanks'));

        $this->assertDatabaseMissing('inquiries', ['email' => 'taro@example.test']);
        Mail::assertNothingSent();
    }

    public function test_submitting_too_fast_is_treated_as_a_bot(): void
    {
        Mail::fake();

        $this->get(route('contact.create'));
        $answer = (int) session('inquiry_captcha_answer');
        // フォーム表示直後（3秒未満）の送信を装う。setUp の shown_at はそのまま。

        $this->post(route('contact.store'), $this->baseData() + ['captcha_answer' => $answer])
            ->assertRedirect(route('contact.thanks'));

        $this->assertDatabaseMissing('inquiries', ['email' => 'taro@example.test']);
        Mail::assertNothingSent();
    }
}
