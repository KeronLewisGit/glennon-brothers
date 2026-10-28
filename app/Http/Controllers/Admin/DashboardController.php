<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\Area;
use App\Enums\BookingStatus;
use App\Enums\EnquiryStatus;
use App\Enums\FrameStatus;
use App\Enums\QuoteStatus;
use App\Enums\Region;
use App\Http\Controllers\Controller;
use App\Models\DeliveryBooking;
use App\Models\Enquiry;
use App\Models\FrameEnquiry;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Support\Notify;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Leads with the one number the signed-in role must act on: quote requests with no answer after
 * RESPONSE_HOURS (sales, for their own region; admin, both), timber frame enquiries untouched
 * for REVIEW_DAYS (DTE) or applications stuck in the first two stages (HR). Tiles follow.
 */
class DashboardController extends Controller
{
    use Concerns;

    public function __invoke(Request $request): View
    {
        $user = $this->user($request);
        $region = $user->region;
        $headline = match (true) {
            $user->works(Area::Quotes) => $this->quoteHeadline($region),
            $user->works(Area::Frame) => $this->frameHeadline(),
            default => $this->careersHeadline(),
        };

        $tiles = [];
        if ($user->works(Area::Quotes)) {
            $mine = QuoteRequest::query()->when($region, fn ($q, Region $r) => $q->where('region', $r));
            $tiles[] = ['label' => 'Open quote requests'.($region ? ', '.$region->label() : ''), 'value' => (clone $mine)->open()->count(), 'note' => (clone $mine)->where('assigned_to', $user->id)->open()->count().' assigned to you', 'href' => route('admin.quotes.index', ['status' => 'open', 'region' => $region->value ?? 'all'])];
            $tiles[] = ['label' => 'Requests, last 30 days', 'value' => (clone $mine)->where('created_at', '>=', now()->subDays(30))->count(),
                'previous' => (clone $mine)->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count(), 'period' => 'vs previous 30 days', 'href' => route('admin.quotes.index', ['region' => $region->value ?? 'all'])];
            $decided = (clone $mine)->whereIn('status', [QuoteStatus::Won, QuoteStatus::Lost])->where('status_changed_at', '>=', now()->subDays(90))->pluck('status');
            $tiles[] = ['label' => 'Quotes decided, last 90 days', 'value' => $decided->count(), 'note' => $decided->filter(fn ($s) => $s === QuoteStatus::Won)->count().' won', 'href' => route('admin.quotes.index', ['status' => 'won', 'region' => $region->value ?? 'all'])];
        }
        if ($user->works(Area::Deliveries)) {
            $tiles[] = ['label' => 'Haulier slots to confirm', 'value' => DeliveryBooking::where('status', BookingStatus::Requested)->count(), 'note' => DeliveryBooking::needsAnswer()->count().' within 2 days', 'href' => route('admin.bookings.index', ['status' => 'requested'])];
        }
        if ($user->works(Area::Frame)) {
            $tiles[] = ['label' => 'Timber frame in design review', 'value' => FrameEnquiry::where('status', FrameStatus::DesignReview)->count(), 'note' => FrameEnquiry::where('status', FrameStatus::New)->count().' new', 'href' => route('admin.frame.index', ['status' => 'design_review'])];
            $tiles[] = ['label' => 'Frame quotes out', 'value' => FrameEnquiry::where('status', FrameStatus::Quoted)->count(), 'note' => FrameEnquiry::where('status', FrameStatus::Won)->where('status_changed_at', '>=', now()->subYear())->count().' signed this year', 'href' => route('admin.frame.index', ['status' => 'quoted'])];
        }
        if ($user->works(Area::Careers)) {
            $tiles[] = ['label' => 'Applications, last 7 days', 'value' => JobApplication::where('created_at', '>=', now()->subDays(7))->count(),
                'previous' => JobApplication::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count(), 'period' => 'vs previous 7 days', 'href' => route('admin.applications.index')];
            $tiles[] = ['label' => 'Open job postings', 'value' => JobPosting::open()->count(), 'note' => 'across '.JobPosting::open()->distinct()->count('plant').' plants', 'href' => route('admin.jobs.index')];
            $tiles[] = ['label' => 'With the plant managers', 'value' => JobApplication::where('status', ApplicationStatus::ManagerReview)->count(), 'note' => JobApplication::where('status', ApplicationStatus::Interview)->count().' at interview', 'href' => route('admin.applications.index', ['status' => 'manager_review'])];
        }
        if ($user->works(Area::Enquiries)) {
            $tiles[] = ['label' => 'New enquiries', 'value' => Enquiry::where('status', EnquiryStatus::New)->count(), 'note' => 'from the contact page', 'href' => route('admin.enquiries.index', ['status' => 'new'])];
        }

        return view('admin.dashboard', [
            'headline' => $headline,
            'tiles' => $tiles,
            'pipeline' => $user->works(Area::Quotes)
                ? collect(QuoteStatus::cases())->map(fn (QuoteStatus $s) => ['label' => $s->label(), 'count' => QuoteRequest::where('status', $s)->when($region, fn ($q, Region $r) => $q->where('region', $r))->count(), 'href' => route('admin.quotes.index', ['status' => $s->value, 'region' => $region->value ?? 'all'])])
                : ($user->works(Area::Careers)
                    ? collect(ApplicationStatus::cases())->map(fn (ApplicationStatus $s) => ['label' => $s->label(), 'count' => JobApplication::where('status', $s)->count(), 'href' => route('admin.applications.index', ['status' => $s->value])])
                    : collect(FrameStatus::cases())->map(fn (FrameStatus $s) => ['label' => $s->label(), 'count' => FrameEnquiry::where('status', $s)->count(), 'href' => route('admin.frame.index', ['status' => $s->value])])),
            'pipelineTitle' => $user->works(Area::Quotes) ? 'Quote pipeline'.($region ? ' · '.$region->team() : '') : ($user->works(Area::Careers) ? 'Applications by stage' : 'Timber frame pipeline'),
            'chart' => $user->works(Area::Quotes) ? $this->quoteChart($region) : ($user->works(Area::Frame) ? $this->frameChart() : $this->applicationChart()),
            'notifyMissing' => $user->isAdmin() && Notify::recipients() === [],
            'notifyInvalid' => $user->isAdmin() ? Notify::invalid() : [],
            'unrouted' => $user->isAdmin() ? QuoteRequest::open()->whereNull('assigned_to')->count() : 0,
            'teams' => $user->isAdmin() ? collect(Region::cases())->mapWithKeys(fn (Region $r) => [$r->value => User::active()->where('role', 'sales')->where('region', $r)->count()]) : collect(),
        ]);
    }

    /** @return array<string, mixed> */
    private function quoteHeadline(?Region $region): array
    {
        $waiting = QuoteRequest::waiting()->with(['assignee', 'lines'])->when($region, fn ($q, Region $r) => $q->where('region', $r))->oldest()->get();
        $team = $region ? $region->team() : 'both sales teams';

        return [
            'area' => 'quotes',
            'count' => $waiting->count(),
            'title' => ($waiting->count() === 1 ? 'quote request has' : 'quote requests have').' waited over '.QuoteRequest::RESPONSE_HOURS.' hours for an answer',
            'body' => $waiting->isEmpty()
                ? 'Every request for '.$team.' has had a first answer within a working day.'
                : 'Oldest first, for '.$team.'. Send prices or a holding reply, then move the request on.',
            'cta' => ['label' => $waiting->isEmpty() ? 'Open the quote register' : 'Answer these first', 'href' => route('admin.quotes.index', $waiting->isEmpty() ? ['status' => 'open'] : ['waiting' => 1, 'region' => $region->value ?? 'all'])],
            'items' => $waiting->take(6)->map(fn (QuoteRequest $q) => [
                'href' => route('admin.quotes.show', $q), 'title' => $q->company, 'meta' => $q->lines->count().' '.str('line')->plural($q->lines->count()).' · '.$q->country->label().' · '.($q->assignee->name ?? 'Unassigned'),
                'chip' => $q->ageLabel(), 'tone' => $q->created_at->lt(now()->subHours(QuoteRequest::RESPONSE_HOURS * 2)) ? 'critical' : 'attention', 'icon' => 'clock',
            ])->all(),
            'list' => 'Waiting longest',
            'empty' => 'Nothing is overdue.',
        ];
    }

    /** @return array<string, mixed> */
    private function frameHeadline(): array
    {
        $waiting = FrameEnquiry::waiting()->withCount('drawings')->oldest()->get();

        return [
            'area' => 'frame',
            'count' => $waiting->count(),
            'title' => ($waiting->count() === 1 ? 'timber frame enquiry has' : 'timber frame enquiries have').' waited over '.FrameEnquiry::REVIEW_DAYS.' days for a design review',
            'body' => $waiting->isEmpty() ? FrameEnquiry::where('status', FrameStatus::New)->count().' new enquiries, all inside the window.' : 'The oldest arrived '.$waiting->first()->created_at->diffForHumans().'. Open the drawings and move it to design review.',
            'cta' => ['label' => 'Open timber frame enquiries', 'href' => route('admin.frame.index', $waiting->isEmpty() ? ['status' => 'new'] : ['waiting' => 1])],
            'items' => $waiting->take(6)->map(fn (FrameEnquiry $e) => [
                'href' => route('admin.frame.show', $e), 'title' => $e->name, 'meta' => $e->project_type->label().' · Co. '.$e->county.' · '.$e->drawings_count.' drawings',
                'chip' => $e->created_at->diffForHumans(['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'short' => true]), 'tone' => 'attention', 'icon' => 'clock',
            ])->all(),
            'list' => 'Waiting longest',
            'empty' => 'Nobody is waiting.',
        ];
    }

    /** @return array<string, mixed> */
    private function careersHeadline(): array
    {
        $waiting = JobApplication::waiting()->with('jobPosting')->oldest()->get();

        return [
            'area' => 'careers',
            'count' => $waiting->count(),
            'title' => ($waiting->count() === 1 ? 'application has' : 'applications have').' sat in new or HR screening for over '.JobApplication::REVIEW_DAYS.' days',
            'body' => $waiting->isEmpty() ? JobApplication::where('status', ApplicationStatus::New)->count().' new applications, all inside the window.' : 'The oldest arrived '.$waiting->first()->created_at->diffForHumans().'. Screen it, then pass it to the plant manager or decline it.',
            'cta' => ['label' => 'Review applications', 'href' => route('admin.applications.index', $waiting->isEmpty() ? ['status' => 'new'] : ['waiting' => 1])],
            'items' => $waiting->take(6)->map(fn (JobApplication $a) => [
                'href' => route('admin.applications.show', $a), 'title' => $a->full_name, 'meta' => $a->position().' · '.$a->plantLabel(),
                'chip' => $a->created_at->diffForHumans(['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'short' => true]), 'tone' => 'attention', 'icon' => 'clock',
            ])->all(),
            'list' => 'Waiting longest',
            'empty' => 'Nobody is waiting.',
        ];
    }

    /** @return array{title: string, note: string, bars: list<array{label: string, value: int, hint: string}>} */
    private function quoteChart(?Region $region): array
    {
        $start = today()->startOfWeek()->subWeeks(7);
        $dates = QuoteRequest::where('created_at', '>=', $start)->when($region, fn ($q, Region $r) => $q->where('region', $r))->pluck('created_at');

        return ['title' => 'Quote requests by week', 'note' => ($region ? $region->label().', ' : '').'last 8 weeks', 'bars' => $this->weeks($start, $dates)];
    }

    /** @return array{title: string, note: string, bars: list<array{label: string, value: int, hint: string}>} */
    private function frameChart(): array
    {
        $start = today()->startOfMonth();
        $months = FrameEnquiry::whereIn('status', [FrameStatus::New, FrameStatus::DesignReview, FrameStatus::Quoted, FrameStatus::Won])->pluck('start_month');
        $bars = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $start->copy()->addMonths($i);
            $bars[] = ['label' => $m->format('M'), 'value' => $months->filter(fn ($v) => $v === $m->format('Y-m'))->count(), 'hint' => $m->format('F Y')];
        }

        return ['title' => 'Planned starts by month', 'note' => 'live enquiries and contracts, next 12 months', 'bars' => $bars];
    }

    /** @return array{title: string, note: string, bars: list<array{label: string, value: int, hint: string}>} */
    private function applicationChart(): array
    {
        $start = today()->startOfWeek()->subWeeks(7);

        return ['title' => 'Applications by week', 'note' => 'all plants, last 8 weeks', 'bars' => $this->weeks($start, JobApplication::where('created_at', '>=', $start)->pluck('created_at'))];
    }

    /**
     * @param  Collection<int, mixed>  $dates
     * @return list<array{label: string, value: int, hint: string}>
     */
    private function weeks(CarbonInterface $start, $dates): array
    {
        $bars = [];
        for ($i = 0; $i < 8; $i++) {
            $from = $start->copy()->addWeeks($i);
            $to = $from->copy()->addWeek();
            $bars[] = ['label' => $i === 7 ? 'This wk' : $from->format('j M'), 'value' => $dates->filter(fn ($d) => $d >= $from && $d < $to)->count(), 'hint' => 'Week of '.$from->format('j M')];
        }

        return $bars;
    }
}
