<x-filament-panels::page>
    @if ($total === 0)
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                このサイトの組織階層はまだ登録されていません。
                <a href="{{ \App\Filament\Resources\LevelResource::getUrl('create') }}" class="text-primary-600 underline dark:text-primary-400">
                    レベルを追加
                </a>
                すると、ここに体制図として表示されます（親子関係は「fatherlevel」に親の「level」を入れます。最上位は 0）。
            </p>
        </x-filament::section>
    @else
        <x-filament::section>
            <x-slot name="heading">体制図</x-slot>

            <ul class="fi-org-tree space-y-1 text-sm">
                @foreach ($roots as $node)
                    @include('filament.pages.partials.org-node', ['node' => $node, 'depth' => 0])
                @endforeach
            </ul>
        </x-filament::section>
    @endif

    <style>
        .fi-org-tree ul { position: relative; margin-left: 1.25rem; padding-left: 1rem; }
        .fi-org-tree ul::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0.9rem;
            border-left: 1px solid rgb(212 212 216);
        }
        .fi-org-tree li { position: relative; }
        .fi-org-tree ul > li::before {
            content: ''; position: absolute; left: -1rem; top: 0.9rem; width: 0.85rem;
            border-top: 1px solid rgb(212 212 216);
        }
        :is(.dark .fi-org-tree) ul::before,
        :is(.dark .fi-org-tree) ul > li::before { border-color: rgb(63 63 70); }
    </style>
</x-filament-panels::page>
