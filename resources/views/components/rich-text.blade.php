@props([
    'name',
    'value' => null,
    'placeholder' => '本文を入力…',
    'minHeight' => '8rem',
])

@php($fieldId = 'rt_'.\Illuminate\Support\Str::of($name)->replace(['[', ']', '.'], '_').'_'.\Illuminate\Support\Str::random(6))

<div class="rich-text-field">
    <input type="hidden" id="{{ $fieldId }}" name="{{ $name }}" value="{{ $value }}">
    <trix-editor
        input="{{ $fieldId }}"
        placeholder="{{ $placeholder }}"
        style="min-height: {{ $minHeight }}"
        {{ $attributes->merge(['class' => 'trix-content']) }}></trix-editor>
</div>
