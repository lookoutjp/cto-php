<div>
    @if ($sites->count() > 1)
        <label class="flex items-center gap-2 text-sm">
            <span class="hidden font-medium text-gray-500 sm:inline dark:text-gray-400">
                サイト
            </span>
            <select
                wire:model.live="siteId"
                class="rounded-lg border-gray-300 bg-white py-1.5 pe-8 ps-3 text-sm text-gray-950 shadow-sm outline-none transition focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
            >
                @foreach ($sites as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
    @endif
</div>
