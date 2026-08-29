<x-layouts.public title="お問い合わせ">
    <p class="mb-6 text-sm text-gray-600">
        ご意見・ご感想・ご質問などを、お気軽にご記入ください。
        <span class="text-red-600">*</span> は必須項目です。
    </p>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('contact.store') }}" class="space-y-5 rounded-lg border border-gray-200 bg-white p-6">
        @csrf

        @php($field = fn ($name) => old($name, $prefill[$name] ?? ''))

        <div>
            <label for="customer_name" class="block text-sm font-medium text-gray-700">お名前 <span class="text-red-600">*</span></label>
            <input type="text" id="customer_name" name="customer_name" value="{{ $field('customer_name') }}" maxlength="100" required
                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス <span class="text-red-600">*</span></label>
            <input type="email" id="email" name="email" value="{{ $field('email') }}" maxlength="100" required
                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
        </div>

        <div>
            <label for="customer_nameread" class="block text-sm font-medium text-gray-700">お名前ふりがな</label>
            <input type="text" id="customer_nameread" name="customer_nameread" value="{{ $field('customer_nameread') }}" maxlength="300"
                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">郵便番号</label>
                <input type="text" id="code" name="code" value="{{ $field('code') }}" maxlength="16"
                       class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
            </div>
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">ご住所</label>
                <input type="text" id="address" name="address" value="{{ $field('address') }}" maxlength="200"
                       class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">お電話番号</label>
                <input type="text" id="phone" name="phone" value="{{ $field('phone') }}" maxlength="100"
                       class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
            </div>
            <div>
                <label for="dayphone" class="block text-sm font-medium text-gray-700">昼間お電話番号</label>
                <input type="text" id="dayphone" name="dayphone" value="{{ $field('dayphone') }}" maxlength="100"
                       class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
            </div>
        </div>

        <div>
            <label for="remark" class="block text-sm font-medium text-gray-700">お問い合わせ内容 <span class="text-red-600">*</span></label>
            <textarea id="remark" name="remark" rows="10" maxlength="10000" required
                      class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $field('remark') }}</textarea>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-brand-fg transition hover:bg-brand-dark">
                送信する
            </button>
        </div>
    </form>
</x-layouts.public>
