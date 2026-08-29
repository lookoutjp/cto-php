<x-layouts.public title="お問い合わせを受け付けました">
    <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
        <p class="text-lg font-semibold text-gray-900">お問い合わせを受け付けました。</p>

        <p class="mt-4 text-sm text-gray-600">
            お問い合わせ番号は
            <span class="font-bold text-red-600">{{ $ticket }}</span>
            です。
        </p>

        <hr class="my-6 border-gray-100">

        <p class="text-sm text-gray-600">
            確認メールを
            <span class="font-medium text-gray-900">{{ $email }}</span>
            宛に送信しました。<br>
            メールのプロバイダによっては迷惑メールフォルダに振り分けられる場合があります。
        </p>

        <p class="mt-4 text-sm text-gray-500">
            時間が経っても連絡がない場合は、お問い合わせ番号を添えてご連絡ください。
        </p>

        <div class="mt-8">
            <a href="{{ route('home') }}" class="text-sm text-gray-600 hover:text-gray-900 hover:underline">&larr; ホームへ</a>
        </div>
    </div>
</x-layouts.public>
