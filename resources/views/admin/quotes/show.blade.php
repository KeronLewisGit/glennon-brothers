<x-layouts.admin :title="$quote->company">
    <x-slot:actions>
        <a href="{{ route('admin.quotes.index') }}" class="a-btn a-btn-ghost"><x-icon name="arrow-left" class="h-4 w-4" />Trade quotes</a>
        <a href="mailto:{{ $quote->email }}?subject={{ rawurlencode('Your quote request '.$quote->reference) }}" class="a-btn a-btn-ghost"><x-icon name="mail" class="h-4 w-4" />Email</a>
        <a href="{{ tel_href($quote->phone) }}" class="a-btn a-btn-primary"><x-icon name="phone" class="h-4 w-4" />Call</a>
    </x-slot:actions>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <div class="min-w-0 space-y-6">
            <section class="a-card min-w-0 p-5 md:p-6">
                <div class="flex flex-wrap items-center gap-2"><x-status :status="$quote->status" />@if($quote->isWaiting())<x-chip tone="attention" icon="clock">{{ $quote->ageLabel() }}</x-chip>@endif</div>
                <dl class="dl-grid mt-5">
                    <dt>Reference</dt><dd class="num">{{ $quote->reference }}</dd>
                    <dt>Business</dt><dd>{{ $quote->customer_type->label() }}@if($quote->account_number) · account {{ $quote->account_number }}@endif</dd>
                    <dt>Contact</dt><dd>{{ $quote->contact_name }}</dd>
                    <dt>Phone</dt><dd><a class="text-link underline" href="{{ tel_href($quote->phone) }}">{{ format_phone($quote->phone) }}</a></dd>
                    <dt>Email</dt><dd><a class="text-link underline" href="mailto:{{ $quote->email }}">{{ $quote->email }}</a></dd>
                    <dt>Deliver to</dt><dd class="whitespace-pre-line">{{ implode("\n", array_filter([$quote->delivery_address, $quote->postcode, $quote->country->label()])) }}</dd>
                    <dt>Sales team</dt><dd>{{ $quote->region->team() }}, priced in {{ $quote->currency }}</dd>
                    <dt>Needed by</dt><dd>{{ $quote->required_by?->format('j F Y') ?? 'Not given' }}</dd>
                    <dt>Received</dt><dd>{{ $quote->created_at->format('j M Y, H:i') }}</dd>
                </dl>
                @if ($quote->remarks)<h3 class="mt-6 font-semibold">Customer's notes</h3><p class="measure mt-1 whitespace-pre-line text-ink-2">{{ $quote->remarks }}</p>@endif
            </section>

            <section class="a-card min-w-0" aria-labelledby="lines-title">
                <h2 id="lines-title" class="px-5 pt-5 font-semibold">{{ $quote->lines->count() }} {{ str('line')->plural($quote->lines->count()) }}</h2>
                <div class="table-box mt-3">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Product</th><th>Size (mm × m)</th><th>Treatment</th><th class="r">Quantity</th><th>Delivery site</th></tr></thead>
                        <tbody>
                            @foreach ($quote->lines as $line)
                                <tr>
                                    <td class="num text-ink-3">{{ $loop->iteration }}</td>
                                    <td class="min-w-[12rem]"><span class="font-semibold">{{ $line->productLabel() }}</span>@if($line->description)<p class="text-xs text-ink-3">{{ $line->description }}</p>@endif</td>
                                    <td class="num whitespace-nowrap">{{ $line->size() ?: '—' }}</td>
                                    <td class="whitespace-nowrap">{{ $line->treatment->short() }}</td>
                                    <td class="r whitespace-nowrap">{{ $line->quantityLabel() }}</td>
                                    <td>{{ $line->delivery_site ?: 'Main address' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @can('openSchedule', $quote)
                    @if ($quote->schedule_path)
                        <div class="border-t border-line p-5"><a href="{{ route('admin.quotes.schedule', $quote) }}" class="a-btn a-btn-ghost"><x-icon name="download" class="h-4 w-4" />Cutting list: {{ $quote->schedule_name }} ({{ file_size($quote->schedule_size) }})</a></div>
                    @endif
                @endcan
            </section>
        </div>

        <div class="min-w-0 space-y-6">
            <section class="a-card p-5">
                <h2 class="font-semibold">Owner and status</h2>
                <form method="POST" action="{{ route('admin.quotes.update', $quote) }}" class="mt-3 space-y-3">
                    @csrf @method('PATCH')
                    <div><label for="q-owner" class="mb-1 block text-sm text-ink-2">Sales owner</label>
                        <select id="q-owner" name="assigned_to" class="a-input w-full"><option value="">Unassigned</option>@foreach ($staff as $u)<option value="{{ $u->id }}" @selected($quote->assigned_to === $u->id)>{{ $u->name }}{{ $u->region ? ' · '.$u->region->label() : '' }}</option>@endforeach</select></div>
                    <div><label for="q-status" class="mb-1 block text-sm text-ink-2">Status</label>
                        <select id="q-status" name="status" class="a-input w-full">@foreach (\App\Enums\QuoteStatus::cases() as $s)<option value="{{ $s->value }}" @selected($quote->status === $s)>{{ $s->label() }}</option>@endforeach</select></div>
                    <div><label for="q-total" class="mb-1 block text-sm text-ink-2">Quoted total ({{ $quote->currency }}, ex VAT)</label>
                        <input id="q-total" name="quoted_total" type="number" min="0" step="0.01" inputmode="decimal" value="{{ $quote->quoted_total }}" class="a-input w-full"></div>
                    <button class="a-btn a-btn-primary">Save</button>
                </form>
            </section>
            @include('admin.partials.timeline', ['notes' => $quote->notes, 'action' => route('admin.quotes.note', $quote)])
        </div>
    </div>
</x-layouts.admin>
