<x-layouts.admin :title="$enquiry->name">
    <x-slot:actions>
        <a href="{{ route('admin.frame.index') }}" class="a-btn a-btn-ghost"><x-icon name="arrow-left" class="h-4 w-4" />Timber frame</a>
        <a href="mailto:{{ $enquiry->email }}?subject={{ rawurlencode('Your timber frame enquiry '.$enquiry->reference) }}" class="a-btn a-btn-ghost"><x-icon name="mail" class="h-4 w-4" />Email</a>
        <a href="{{ tel_href($enquiry->phone) }}" class="a-btn a-btn-primary"><x-icon name="phone" class="h-4 w-4" />Call</a>
    </x-slot:actions>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <div class="min-w-0 space-y-6">
            <section class="a-card min-w-0 p-5 md:p-6">
                <div class="flex flex-wrap items-center gap-2"><x-status :status="$enquiry->status" />@if($enquiry->isWaiting())<x-chip tone="attention" icon="clock">Waiting for design review</x-chip>@endif</div>
                <h2 class="mt-3 text-xl font-bold">{{ $enquiry->project_type->label() }}@if($enquiry->units), {{ $enquiry->units }} homes @endif</h2>
                <dl class="dl-grid mt-5">
                    <dt>Reference</dt><dd class="num">{{ $enquiry->reference }}</dd>
                    <dt>Client</dt><dd>{{ $enquiry->client_type->label() }}@if($enquiry->company) · {{ $enquiry->company }}@endif</dd>
                    <dt>Phone</dt><dd><a class="text-link underline" href="{{ tel_href($enquiry->phone) }}">{{ format_phone($enquiry->phone) }}</a></dd>
                    <dt>Email</dt><dd><a class="text-link underline" href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a></dd>
                    <dt>Site</dt><dd class="whitespace-pre-line">{{ $enquiry->site_address."\nCo. ".$enquiry->county.($enquiry->eircode ? ' · '.$enquiry->eircode : '') }}</dd>
                    <dt>Planning</dt><dd>{{ $enquiry->planning_status->label() }}</dd>
                    <dt>Planned start</dt><dd>{{ $enquiry->startLabel() }}</dd>
                    <dt>Floor area</dt><dd>{{ $enquiry->floor_area_m2 ? number_format($enquiry->floor_area_m2).' m²' : 'Not given' }}</dd>
                    <dt>Received</dt><dd>{{ $enquiry->created_at->format('j M Y, H:i') }}</dd>
                </dl>
                @if ($enquiry->message)<h3 class="mt-6 font-semibold">Message</h3><p class="measure mt-1 whitespace-pre-line text-ink-2">{{ $enquiry->message }}</p>@endif
            </section>
            <section class="a-card min-w-0 p-5 md:p-6" aria-labelledby="drawings-title">
                <h2 id="drawings-title" class="font-semibold">Drawings ({{ $enquiry->drawings->count() }})</h2>
                <ul class="mt-3 divide-y divide-line">
                    @forelse ($enquiry->drawings as $d)
                        <li class="flex flex-wrap items-center gap-3 py-2.5">
                            <x-icon name="file" class="h-4 w-4 flex-none text-ink-3" />
                            @can('openDrawing', [$enquiry, $d])<a href="{{ route('admin.frame.drawing', [$enquiry, $d]) }}" class="min-w-0 break-words font-semibold text-link underline-offset-2 hover:underline">{{ $d->original_name }}</a>@else<span>{{ $d->original_name }}</span>@endcan
                            <span class="ml-auto text-sm text-ink-3">{{ file_size($d->size) }} · {{ $d->added_by === 'customer' ? 'from the customer' : 'added by staff' }} · {{ $d->created_at->format('j M') }}</span>
                        </li>
                    @empty
                        <li class="py-2.5 text-ink-3">No drawings yet. The customer can add them through their link.</li>
                    @endforelse
                </ul>
                <p class="mt-3 text-xs text-ink-3"><x-icon name="lock" class="inline h-3.5 w-3.5" /> DTE and administrators only; each download is logged.</p>
            </section>
        </div>
        <div class="min-w-0 space-y-6">
            <section class="a-card p-5">
                <h2 class="font-semibold">Status and owner</h2>
                <form method="POST" action="{{ route('admin.frame.update', $enquiry) }}" class="mt-3 space-y-3">
                    @csrf @method('PATCH')
                    <div><label for="e-status" class="mb-1 block text-sm text-ink-2">Status</label>
                        <select id="e-status" name="status" class="a-input w-full">@foreach (\App\Enums\FrameStatus::cases() as $s)<option value="{{ $s->value }}" @selected($enquiry->status === $s)>{{ $s->label() }}</option>@endforeach</select></div>
                    <div><label for="e-owner" class="mb-1 block text-sm text-ink-2">Designer</label>
                        <select id="e-owner" name="assigned_to" class="a-input w-full"><option value="">Unassigned</option>@foreach ($staff as $u)<option value="{{ $u->id }}" @selected($enquiry->assigned_to === $u->id)>{{ $u->name }}</option>@endforeach</select></div>
                    <div><label for="e-total" class="mb-1 block text-sm text-ink-2">Quoted total (EUR, ex VAT)</label>
                        <input id="e-total" name="quoted_total" type="number" min="0" step="0.01" inputmode="decimal" value="{{ $enquiry->quoted_total }}" class="a-input w-full"></div>
                    <button class="a-btn a-btn-primary">Save</button>
                </form>
            </section>
            <section class="a-card p-5" x-data="{ copied: false }">
                <h2 class="font-semibold">Customer's drawings link</h2>
                @if ($enquiry->status->acceptsDrawings())
                    @php($link = $enquiry->drawingsUrl())
                    <p class="mt-1 text-sm text-ink-2">Private and signed; expires after {{ \App\Models\FrameEnquiry::LINK_DAYS }} days. It is in the customer's confirmation email.</p>
                    <label for="e-link" class="sr-only">Link</label>
                    <input id="e-link" readonly value="{{ $link }}" class="a-input mt-3 w-full text-xs" @focus="$el.select()">
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" class="a-btn a-btn-ghost" @click="navigator.clipboard.writeText(@js($link)); copied = true"><x-icon name="link" class="h-4 w-4" /><span x-text="copied ? 'Copied' : 'Copy link'">Copy link</span></button>
                        <form method="POST" action="{{ route('admin.frame.revoke', $enquiry) }}">@csrf<button class="a-btn a-btn-ghost text-[color:var(--critical-text)]"><x-icon name="x" class="h-4 w-4" />Revoke old links</button></form>
                    </div>
                @else
                    <p class="mt-1 text-sm text-ink-2">Closed: the enquiry is {{ strtolower($enquiry->status->label()) }}, so the link no longer accepts drawings.</p>
                @endif
            </section>
            @include('admin.partials.timeline', ['notes' => $enquiry->notes, 'action' => route('admin.frame.note', $enquiry)])
        </div>
    </div>
</x-layouts.admin>
