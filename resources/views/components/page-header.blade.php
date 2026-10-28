@props(['title', 'lead' => null, 'kicker' => null, 'code' => null, 'photo' => null, 'position' => 'center', 'ticket' => null, 'ticketTitle' => 'Glennon Brothers', 'caption' => null])
{{--
    The one header band every inner page uses, so titles start at the same height and left edge
    site-wide. Slate band with end-grain growth rings (or a photo behind a scrim), a pack-stamp
    kicker, and a pack ticket on the right. A bale of board ends closes it. Breadcrumbs, actions
    and chips go in the slot.
--}}
@php
    $fields = $ticket ?? [
        'Mill' => 'Longford · est. 1913',
        'Page' => $kicker ?? $title,
        'Grade' => 'Family run',
    ];
@endphp
<section {{ $attributes->merge(['class' => 'rings relative isolate overflow-hidden bg-slate-900 text-white on-dark']) }}>
    @if ($photo)
        <div class="hero-photo absolute inset-0 -z-10">
            <x-photo :key="$photo" alt="" :eager="true" sizes="100vw" class="h-full w-full" :position="$position" />
            <div class="hero-scrim absolute inset-0"></div>
        </div>
    @endif
    <div class="wrap grid gap-8 pb-12 pt-10 md:pb-16 md:pt-16 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end lg:gap-12">
        <div class="min-w-0">
            @if ($kicker)<x-stamp :code="$code">{{ $kicker }}</x-stamp>@endif
            <h1 class="display-2 mt-5 max-w-4xl">{{ $title }}</h1>
            @if ($lead)<p class="lead mt-4 text-white/85 md:mt-5">{{ $lead }}</p>@endif
            {{ $slot }}
        </div>
        <x-ticket :fields="$fields" :title="$ticketTitle" foot="Pack no." :foot-value="$code ?? 'GB-'.now()->format('y')" class="hidden rotate-[1.2deg] lg:block" />
    </div>
    @if ($photo)
        <span class="photo-note" style="bottom: 2.1rem">{{ is_illustrative($photo) ? 'Illustrative photo' : ($caption ?? 'Glennon Brothers') }}</span>
    @endif
    <div class="bale" aria-hidden="true"></div>
</section>
