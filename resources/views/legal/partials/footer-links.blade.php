<nav class="mt-8 flex flex-wrap gap-x-4 gap-y-1 border-t border-gray-100 pt-4 text-xs text-gray-500">
    <a href="{{ route('legal.terms') }}" @class(['hover:text-gray-900', 'font-semibold text-gray-900' => request()->routeIs('legal.terms')])>利用規約</a>
    <a href="{{ route('legal.privacy') }}" @class(['hover:text-gray-900', 'font-semibold text-gray-900' => request()->routeIs('legal.privacy')])>プライバシーポリシー</a>
    <a href="{{ route('legal.tokushoho') }}" @class(['hover:text-gray-900', 'font-semibold text-gray-900' => request()->routeIs('legal.tokushoho')])>特定商取引法に基づく表記</a>
</nav>
