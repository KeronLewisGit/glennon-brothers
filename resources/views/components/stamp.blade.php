@props(['code' => null])
{{-- Section label printed like the ink stamp on a pack wrapper: [tree + code | LABEL]. --}}
<span {{ $attributes->merge(['class' => 'stamp']) }}>
    <span class="code"><x-tree />@if($code){{ $code }}@endif</span>
    <span class="label">{{ $slot }}</span>
</span>
