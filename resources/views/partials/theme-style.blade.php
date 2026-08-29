{{-- サイトごとのテーマカラー（App\Support\ThemePalette）。$site は View Composer が共有。 --}}
<style>:root{ {!! \App\Support\ThemePalette::forSite($site ?? null)->cssVars() !!} }</style>
