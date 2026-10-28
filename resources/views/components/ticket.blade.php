@props(['fields' => [], 'title' => 'Glennon Brothers', 'foot' => null, 'footValue' => null])
{{-- Tabbed info card: a white box with a red tab, as on the current site. Fields are label => value. --}}
<div {{ $attributes->merge(['class' => 'ticket']) }}>
    <p class="ticket-head"><x-tree />{{ $title }}</p>
    <dl>
        @foreach ($fields as $label => $value)
            <dt>{{ $label }}</dt><dd>{{ $value }}</dd>
        @endforeach
    </dl>
    @if ($foot)
        <p class="tear"><span>{{ $foot }}</span><b>{{ $footValue }}</b></p>
    @endif
</div>
