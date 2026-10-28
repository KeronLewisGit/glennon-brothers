@props(['code' => null])
{{-- Red tab label, as the current site hangs over its boxes: [trees + code | LABEL]. --}}
<span {{ $attributes->merge(['class' => 'stamp']) }}>
    <span class="code"><x-tree />@if($code){{ $code }}@endif</span>
    <span class="label">{{ $slot }}</span>
</span>
