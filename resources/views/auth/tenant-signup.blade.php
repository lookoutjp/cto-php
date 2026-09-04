<x-guest-layout>
    <h1 class="text-lg font-semibold text-gray-900">新しいワークスペースを作成</h1>
    <p class="mt-1 text-sm text-gray-500">
        会社・チームごとに独立したサイト（テナント）を作成します。作成すると、あなたがそのサイトの管理員としてログインします。
    </p>

    <form method="POST" action="{{ route('tenant-signup.store') }}" class="mt-6">
        @csrf

        <!-- Site name -->
        <div>
            <x-input-label for="sitename" value="会社名・サイト名" />
            <x-text-input id="sitename" class="block mt-1 w-full" type="text" name="sitename"
                           :value="old('sitename')" required autofocus
                           oninput="cto_syncSiteId(this.value)" />
            <x-input-error :messages="$errors->get('sitename')" class="mt-2" />
        </div>

        <!-- Site ID -->
        <div class="mt-4">
            <x-input-label for="site_id" value="サイトID" />
            <x-text-input id="site_id" class="block mt-1 w-full font-mono text-sm" type="text" name="site_id"
                           :value="old('site_id')" required maxlength="30" pattern="[a-z][a-z0-9\-]*"
                           oninput="cto_siteIdTouched = true" />
            <p class="mt-1 text-xs text-gray-400">半角英小文字・数字・ハイフンのみ、3〜30文字。あとから変更できません。</p>
            <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" value="あなたのお名前" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <p class="mt-4 text-xs text-gray-500">
            作成すると
            <a href="{{ route('legal.terms') }}" target="_blank" class="underline hover:text-gray-700">利用規約</a>
            ・
            <a href="{{ route('legal.privacy') }}" target="_blank" class="underline hover:text-gray-700">プライバシーポリシー</a>
            に同意したものとみなされます。まずは無料プランで開始します。
        </p>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand" href="{{ route('login') }}">
                すでにアカウントをお持ちですか？
            </a>

            <x-primary-button class="ms-4">
                ワークスペースを作成
            </x-primary-button>
        </div>
    </form>

    <script>
        let cto_siteIdTouched = {{ old('site_id') ? 'true' : 'false' }};
        function cto_syncSiteId(sitename) {
            if (cto_siteIdTouched) return;
            const slug = sitename
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .slice(0, 30);
            document.getElementById('site_id').value = slug.replace(/^[^a-z]+/, '');
        }
    </script>
</x-guest-layout>
