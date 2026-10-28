{{--
    Line drawings for the "from log to frame" story on the home page, one scene per step. Purely
    decorative: the steps beside it carry the words. Without JavaScript the first scene shows.
--}}
<svg viewBox="0 0 480 360" aria-hidden="true" focusable="false" {{ $attributes->merge(['class' => 'process-scene']) }}>
    <line class="s-ground" x1="20" y1="300" x2="460" y2="300" />

    {{-- 1 · Logs in the yard --}}
    <g class="scene" data-scene="1">
        @foreach ([[150, 272], [204, 272], [258, 272], [312, 272], [177, 226], [231, 226], [285, 226], [204, 180], [258, 180]] as $i => [$cx, $cy])
            <g class="log-end" style="--d: {{ $i * 60 }}ms">
                <circle class="s-fill s-line" cx="{{ $cx }}" cy="{{ $cy }}" r="26" />
                <circle class="s-ring" cx="{{ $cx }}" cy="{{ $cy }}" r="17" />
                <circle class="s-ring" cx="{{ $cx }}" cy="{{ $cy }}" r="8" />
                <circle class="s-red-fill" cx="{{ $cx }}" cy="{{ $cy }}" r="2.5" />
            </g>
        @endforeach
        <path class="s-line s-thin" d="M98 300 L124 246 M382 300 L356 246" />
    </g>

    {{-- 2 · Sawn --}}
    <g class="scene" data-scene="2">
        <g class="log-feed">
            <rect class="s-fill s-line" x="40" y="214" width="230" height="64" rx="10" />
            <path class="s-ring" d="M70 230 H240 M60 246 H250 M76 262 H230" />
        </g>
        <g class="blade-wrap">
            <g class="saw-blade">
                <circle class="s-red-fill" cx="300" cy="246" r="52" />
                <path class="s-teeth" d="M300 186 l7 12 h-14 z M352 246 l-12 7 v-14 z M300 306 l-7 -12 h14 z M248 246 l12 -7 v14 z M337 209 l-4 13 -9 -9 z M337 283 l-13 -4 9 -9 z M263 283 l4 -13 9 9 z M263 209 l13 4 -9 9 z" />
                <circle class="s-hub" cx="300" cy="246" r="12" />
            </g>
        </g>
        <g class="boards-out">
            <rect class="s-fill s-line" x="360" y="218" width="96" height="12" rx="2" />
            <rect class="s-fill s-line" x="360" y="236" width="96" height="12" rx="2" style="--d: 120ms" />
            <rect class="s-fill s-line" x="360" y="254" width="96" height="12" rx="2" style="--d: 240ms" />
            <rect class="s-fill s-line" x="360" y="272" width="96" height="12" rx="2" style="--d: 360ms" />
        </g>
    </g>

    {{-- 3 · Dried and graded --}}
    <g class="scene" data-scene="3">
        <path class="s-line s-panel" d="M110 300 V150 Q240 96 370 150 V300" />
        @foreach ([0, 1, 2, 3] as $row)
            <rect class="s-fill s-line" x="140" y="{{ 270 - $row * 30 }}" width="200" height="20" rx="2" />
            <rect class="s-sticker" x="150" y="{{ 290 - $row * 30 }}" width="10" height="10" />
            <rect class="s-sticker" x="235" y="{{ 290 - $row * 30 }}" width="10" height="10" />
            <rect class="s-sticker" x="320" y="{{ 290 - $row * 30 }}" width="10" height="10" />
        @endforeach
        <path class="heat" style="--d: 0ms" d="M190 140 q8 -10 0 -20 q-8 -10 0 -20" />
        <path class="heat" style="--d: 300ms" d="M240 128 q8 -10 0 -20 q-8 -10 0 -20" />
        <path class="heat" style="--d: 600ms" d="M290 140 q8 -10 0 -20 q-8 -10 0 -20" />
        <g class="grade-stamp"><rect class="s-red-fill" x="356" y="196" width="78" height="30" rx="4" /><text x="395" y="216" text-anchor="middle">C16 · KD</text></g>
    </g>

    {{-- 4 · Packed and sent --}}
    <g class="scene" data-scene="4">
        @foreach ([[60, 196, 120, 104], [196, 160, 132, 140], [344, 216, 92, 84]] as $i => [$x, $y, $w, $h])
            <g class="pack" style="--d: {{ $i * 120 }}ms">
                <rect class="s-fill s-line" x="{{ $x }}" y="{{ $y }}" width="{{ $w }}" height="{{ $h }}" rx="3" />
                @for ($gx = $x + 14; $gx < $x + $w - 4; $gx += 18)
                    <line class="s-ring" x1="{{ $gx }}" y1="{{ $y + 6 }}" x2="{{ $gx }}" y2="{{ $y + $h - 6 }}" />
                @endfor
                <rect class="s-red-fill" x="{{ $x + $w * 0.28 }}" y="{{ $y - 2 }}" width="5" height="{{ $h + 4 }}" />
                <rect class="s-red-fill" x="{{ $x + $w * 0.68 }}" y="{{ $y - 2 }}" width="5" height="{{ $h + 4 }}" />
            </g>
        @endforeach
    </g>

    {{-- 5 · Timber frame (DTE) --}}
    <g class="scene" data-scene="5">
        <path class="frame-line" pathLength="1" d="M110 300 V180 H370 V300 M110 180 L240 96 L370 180 M175 180 V300 M240 180 V300 M305 180 V300 M110 240 H370 M240 96 V180 M175 180 L240 128 L305 180" />
        <rect class="s-red-fill frame-door" x="222" y="250" width="36" height="50" />
    </g>
</svg>
