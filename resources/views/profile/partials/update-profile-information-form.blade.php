<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="nameread" value="ふりがな" />
            <x-text-input id="nameread" name="nameread" type="text" class="mt-1 block w-full" :value="old('nameread', $user->nameread)" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('nameread')" />
        </div>

        <div>
            <x-input-label for="appeal" value="ニックネーム（メンバー一覧・個人ページに表示）" />
            <x-text-input id="appeal" name="appeal" type="text" class="mt-1 block w-full" :value="old('appeal', $user->appeal)" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('appeal')" />
        </div>

        <div>
            <x-input-label for="phone" value="電話番号" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="hp" value="ホームページ" />
            <x-text-input id="hp" name="hp" type="text" class="mt-1 block w-full" :value="old('hp', $user->hp)" placeholder="example.com" autocomplete="url" />
            <x-input-error class="mt-2" :messages="$errors->get('hp')" />
        </div>

        <div>
            <x-input-label for="sex" value="性別" />
            <select id="sex" name="sex" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand text-sm">
                <option value="" @selected(old('sex', (string) $user->sex) === '')>未回答</option>
                <option value="1" @selected(old('sex', (string) $user->sex) === '1')>男性</option>
                <option value="0" @selected(old('sex', (string) $user->sex) === '0')>女性</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('sex')" />
        </div>

        <div>
            <x-input-label for="introduce" value="自己紹介（メンバーに公開されます）" />
            <textarea id="introduce" name="introduce" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand text-sm">{{ old('introduce', trim(strip_tags((string) $user->introduce))) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('introduce')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
