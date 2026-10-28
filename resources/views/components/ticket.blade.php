@props(['fields' => [], 'title' => 'Glennon Brothers', 'foot' => null, 'footValue' => null])
{{-- Pack ticket: the label stapled to every timber pack. Fields are label => value. --}}
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
