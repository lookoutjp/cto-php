@props([
    'name' => 'to',
    'members',          // Collection|array of objects/arrays with member_id & name
    'value' => null,    // 事前選択する member_id
    'placeholder' => '名前で検索…',
])

@php
    $options = collect($members)->map(fn ($m) => [
        'id' => is_array($m) ? $m['member_id'] : $m->member_id,
        'label' => trim((string) (is_array($m) ? ($m['name'] ?? '') : ($m->name ?? ''))) ?: (is_array($m) ? $m['member_id'] : $m->member_id),
    ])->values();
    $selected = $options->firstWhere('id', $value);
@endphp

<div
    x-data="{
        open: false,
        q: @js($selected['label'] ?? ''),
        selectedId: @js($value ?? ''),
        options: @js($options),
        get filtered() {
            const q = this.q.trim().toLowerCase();
            if (!q || this.justPicked) return this.options;
            return this.options.filter(o =>
                o.label.toLowerCase().includes(q) || o.id.toLowerCase().includes(q));
        },
        justPicked: {{ $selected ? 'true' : 'false' }},
        pick(o) {
            this.selectedId = o.id;
            this.q = o.label;
            this.justPicked = true;
            this.open = false;
        },
        onInput() {
            this.justPicked = false;
            this.selectedId = '';
            this.open = true;
        },
    }"
    @click.outside="open = false"
    class="relative"
>
    <input type="hidden" name="{{ $name }}" :value="selectedId">
    <input
        type="text"
        x-model="q"
        @focus="open = true"
        @input="onInput()"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand"
    >
    <ul
        x-show="open && filtered.length"
        x-cloak
        x-transition.opacity
        class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 text-sm shadow-lg"
    >
        <template x-for="o in filtered" :key="o.id">
            <li>
                <button
                    type="button"
                    @click="pick(o)"
                    class="block w-full px-3 py-1.5 text-left hover:bg-brand-bg"
                    :class="{ 'bg-brand-bg font-medium': o.id === selectedId }"
                >
                    <span x-text="o.label"></span>
                    <span class="ml-1 text-xs text-gray-400" x-text="'（' + o.id + '）'"></span>
                </button>
            </li>
        </template>
    </ul>
    <p x-show="open && q.trim() && !filtered.length" x-cloak
       class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-400 shadow-lg">
        該当する会員がいません
    </p>
</div>
