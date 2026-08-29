<?php

namespace App\Http\Requests\Auth;

use App\Auth\LegacyPasswordVerifier;
use App\Models\Member;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $member = Member::where('email', $this->string('email'))->first();

        // Laravel 12は Hash::check() に bcrypt以外の文字列を渡すと例外を投げる
        // (verifyAlgorithmチェック)。旧ASPから移行した会員はbcrypt形式ではないため、
        // Auth::attempt()を呼ぶ前にbcrypt形式かどうかで処理を分ける。
        if ($member && str_starts_with((string) $member->password, '$2y$')) {
            if (Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
                RateLimiter::clear($this->throttleKey());

                return;
            }
        } elseif ($member) {
            // 旧ASPシステムから移行した会員は、bcryptではなく旧パスワード形式
            // (PBKDF2-HMAC-MD5、または無ソルトMD5切り詰め)のまま保存されている。
            // ここで旧形式を検証し、一致すればログインさせた上でbcryptへ静かに移行する
            // (旧ASP側のVerifySecret()と同じ「初回ログイン成功時に新形式へ移行」方式)。
            if (LegacyPasswordVerifier::verify($this->string('password'), $member->password) > 0) {
                $member->forceFill(['password' => Hash::make($this->string('password'))])->save();
                Auth::login($member, $this->boolean('remember'));
                RateLimiter::clear($this->throttleKey());

                return;
            }
        }

        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
