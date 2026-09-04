@php
    $site = \App\Models\Room::find(app(\App\Support\CurrentSite::class)->idOrNull());
@endphp
<x-site-logo :site="$site" class="h-8 w-auto" />
