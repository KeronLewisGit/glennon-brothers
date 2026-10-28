@props(['tone' => 'dark', 'small' => false])
{{--
    Redrawn from the group's own logo (glennonbrothers.ie/images/logo.png): GLENNON in slate
    capitals, the red twin-tree mark, BROTHERS set small beneath it. The wordmark is set in
    Instrument Sans until the group supplies the vector artwork.
--}}
<span {{ $attributes->merge(['class' => 'inline-flex items-end gap-2']) }}>
    <span @class([
        'font-sans font-semibold uppercase leading-[0.8] tracking-[0.02em]',
        'text-[1.5rem]' => $small,
        'text-[1.75rem] min-[400px]:text-[2rem]' => ! $small,
        'text-white' => $tone === 'light',
        'text-ink' => $tone !== 'light',
    ])>Glennon</span>
    <span class="flex flex-col items-stretch">
        <x-tree @class(['text-mark', 'h-[1.15rem] w-[2.1rem]' => $small, 'h-[1.35rem] w-[2.5rem]' => ! $small]) />
        <span @class(['mt-[3px] text-center font-sans font-medium uppercase leading-none tracking-[0.12em]', 'text-[0.5rem]' => $small, 'text-[0.5625rem]' => ! $small, 'text-white/75' => $tone === 'light', 'text-ink-2' => $tone !== 'light'])>Brothers</span>
    </span>
</span>
