<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\BookingPurpose;
use App\Enums\BookingSlot;
use App\Enums\BookingStatus;
use App\Enums\Country;
use App\Enums\CustomerType;
use App\Enums\EmploymentType;
use App\Enums\EnquiryStatus;
use App\Enums\EnquiryTopic;
use App\Enums\Experience;
use App\Enums\FrameClientType;
use App\Enums\FrameProjectType;
use App\Enums\FrameStatus;
use App\Enums\JobCategory;
use App\Enums\JobStatus;
use App\Enums\PlanningStatus;
use App\Enums\Plant;
use App\Enums\QuoteStatus;
use App\Enums\QuoteUnit;
use App\Enums\Region;
use App\Enums\Role;
use App\Enums\Treatment;
use App\Enums\VehicleType;
use App\Models\DeliveryBooking;
use App\Models\Enquiry;
use App\Models\FrameEnquiry;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demo data for the pitch: every company, person, quote and application here is fictional and
 * labelled "(sample)" or "Sample". Job titles follow the kinds of roles the group advertises.
 * Logins: admin|sales|sales.uk|hr|frame@glennonbrothers.test / password. Never run in production.
 */
class DemoSeeder extends Seeder
{
    private const array FIRST = ['Seán', 'Aoife', 'Ciarán', 'Niamh', 'Darragh', 'Siobhán', 'Pádraig', 'Gráinne', 'Eoin', 'Orla', 'Conor', 'Róisín', 'Declan', 'Áine', 'Callum', 'Eilidh', 'Fraser', 'Isla', 'Liam', 'Ciara'];

    private const array LAST = ['Murphy', 'Kelly', 'O\'Brien', 'Byrne', 'Ryan', 'Walsh', 'Doyle', 'McCarthy', 'Gallagher', 'Quinn', 'Nolan', 'Farrell', 'Reilly', 'Kavanagh', 'Campbell', 'MacLeod', 'Stewart', 'Fraser'];

    public function run(): void
    {
        // Fictional data and "password" logins: never on the live site. A pitch preview runs
        // APP_ENV=production with GB_PREVIEW=true, so it is allowed there.
        if (app()->isProduction() && ! gb('preview')) {
            $this->command?->error('DemoSeeder does not run in production unless GB_PREVIEW=true (a pitch preview).');

            return;
        }
        mt_srand(1913);
        $staff = $this->users();
        $this->quotes($staff);
        $this->frame($staff['frame']);
        $this->bookings($staff['sales']);
        $this->careers($staff['hr']);
        $this->enquiries();
    }

    /** @return array<string, User> */
    private function users(): array
    {
        $out = [];
        foreach ([
            'admin' => ['Demo Administrator', Role::Admin, null],
            'sales' => ['Demo Sales (Longford)', Role::Sales, Region::Ireland],
            'sales.uk' => ['Demo Sales (Troon)', Role::Sales, Region::Uk],
            'hr' => ['Demo HR Officer', Role::Hr, null],
            'frame' => ['Demo DTE Designer', Role::Frame, null],
        ] as $key => [$name, $role, $region]) {
            $out[$key] = User::updateOrCreate(['email' => $key.'@glennonbrothers.test'], ['name' => $name, 'password' => Hash::make('password'), 'role' => $role, 'region' => $region, 'active' => true]);
        }

        return $out;
    }

    private function name(): string
    {
        return self::FIRST[mt_rand(0, count(self::FIRST) - 1)].' '.self::LAST[mt_rand(0, count(self::LAST) - 1)];
    }

    private function phone(): string
    {
        // Irish mobile prefixes; 555-style numbers are not reserved in Ireland, so the last digits are 0s-heavy and clearly samples.
        return '08'.[3, 5, 6, 7][mt_rand(0, 3)].' 000 '.str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /** A tiny valid PDF on the private disk, so "open" works in the demo. */
    private function pdf(string $path, string $title): void
    {
        $text = str_replace(['(', ')', '\\'], '', 'SAMPLE DOCUMENT - '.$title);
        $stream = "BT /F1 18 Tf 72 720 Td ({$text}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$obj}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
        Storage::disk('local')->put($path, $pdf);
    }

    private function at(Carbon $time, callable $create): mixed
    {
        Carbon::setTestNow($time);
        try {
            return $create();
        } finally {
            Carbon::setTestNow();
        }
    }

    /** @param array<string, User> $staff */
    private function quotes(array $staff): void
    {
        $rows = [
            // company, type, country, delivery, postcode, hours ago, status, lines [product, desc, t, w, l, treatment, qty, unit]
            ['Midlands Building Supplies (sample)', CustomerType::Merchant, Country::Ireland, 'Unit 3, Clonminch Business Park, Tullamore, Co. Offaly', 'R35 X2Y4', 54, QuoteStatus::New, [['carcassing', 'C16', 47, 150, 4.8, Treatment::KilnDried, 6, QuoteUnit::Packs], ['carcassing', 'C24', 47, 225, 6.0, Treatment::KilnDried, 2, QuoteUnit::Packs], ['cls', 'C16', 38, 89, 2.4, Treatment::Treated, 4, QuoteUnit::Packs]]],
            ['Shannonside Fencing (sample)', CustomerType::Fencing, Country::Ireland, 'Ballymahon Road, Athlone, Co. Westmeath', 'N37 F6K2', 31, QuoteStatus::New, [['fence_post', 'Pointed', 100, 100, 2.4, Treatment::Treated, 400, QuoteUnit::Pieces], ['fence_rail', 'D rail', 35, 100, 3.6, Treatment::Treated, 600, QuoteUnit::Pieces]]],
            ['Ayrshire Pallets (sample)', CustomerType::Pallet, Country::Scotland, 'Block 2, Riverside Industrial Estate, Irvine', 'KA11 5DH', 40, QuoteStatus::Pricing, [['pallet', 'Cross-cut 1.2 m', 22, 100, 1.2, Treatment::HeatTreated, 60, QuoteUnit::CubicMetres], ['pallet', 'Bearers', 47, 100, 1.2, Treatment::KilnDried, 25, QuoteUnit::CubicMetres]]],
            ['Northern Frame Systems (sample)', CustomerType::FrameMaker, Country::NorthernIreland, 'Mallusk Road, Newtownabbey', 'BT36 4PX', 6, QuoteStatus::New, [['cls', 'C16, precision end-trimmed', 38, 140, 4.8, Treatment::KilnDried, 120, QuoteUnit::CubicMetres]]],
            ['Lothian Landscapes (sample)', CustomerType::Fencing, Country::Scotland, '14 Station Road, Haddington', 'EH41 3BT', 3, QuoteStatus::New, [['fence_board', 'Weather edge', 22, 150, 1.8, Treatment::Treated, 1200, QuoteUnit::Pieces], ['glenfence', 'Closed panel 1.8 × 1.8 m', null, null, null, Treatment::Treated, 80, QuoteUnit::Pieces]]],
            ['Cork Harbour Builders Providers (sample)', CustomerType::Merchant, Country::Ireland, 'Carrigtwohill, Co. Cork', 'T45 HX20', 75, QuoteStatus::Quoted, [['carcassing', 'C16', 47, 100, 4.8, Treatment::KilnDried, 8, QuoteUnit::Packs], ['glendeck', '150 × 35 boards', 35, 150, 3.6, Treatment::Treated, 300, QuoteUnit::Pieces]], 18450.00],
            ['Dumfries Timber Merchants (sample)', CustomerType::Merchant, Country::Scotland, 'Heathhall Industrial Estate, Dumfries', 'DG1 3PH', 120, QuoteStatus::Quoted, [['carcassing', 'C16', 47, 175, 4.8, Treatment::KilnDried, 5, QuoteUnit::Packs]], 7920.00],
            ['Wicklow Self-Build Supplies (sample)', CustomerType::Merchant, Country::Ireland, 'Wicklow town', 'A67 E2Y5', 240, QuoteStatus::Won, [['carcassing', 'C16', 47, 200, 5.4, Treatment::KilnDried, 3, QuoteUnit::Packs]], 6210.50],
            ['Borders Packaging (sample)', CustomerType::Pallet, Country::England, 'Kingmoor Park, Carlisle', 'CA6 4SJ', 300, QuoteStatus::Lost, [['pallet', null, 18, 95, 1.0, Treatment::Green, 40, QuoteUnit::CubicMetres]], 5100.00],
            ['Galway Groundworks (sample)', CustomerType::Contractor, Country::Ireland, 'Ballybrit Business Park, Galway', 'H91 R2W3', 20, QuoteStatus::Pricing, [['carcassing', 'C24', 47, 225, 6.0, Treatment::KilnDried, 40, QuoteUnit::Pieces], ['fence_post', 'Unpointed', 75, 75, 1.8, Treatment::Treated, 150, QuoteUnit::Pieces]]],
            ['Stable Bedding Co. (sample)', CustomerType::Other, Country::Ireland, 'Naas, Co. Kildare', 'W91 K8C2', 100, QuoteStatus::Declined, [['residues', 'Shavings, bagged', null, null, null, Treatment::Any, 20, QuoteUnit::Tonnes]]],
            ['Fife Garden Buildings (sample)', CustomerType::Other, Country::Scotland, 'Glenrothes', 'KY6 2RU', 28, QuoteStatus::New, [['whitewood', 'Machined, for sheds', 22, 125, 2.4, Treatment::KilnDried, 30, QuoteUnit::CubicMetres]]],
        ];
        foreach ($rows as $i => $row) {
            [$company, $type, $country, $address, $postcode, $hours, $status, $lines] = $row;
            $total = $row[8] ?? null;
            $quote = $this->at(now()->subHours($hours)->subMinutes(mt_rand(0, 50)), function () use ($company, $type, $country, $address, $postcode, $lines, $i) {
                $q = QuoteRequest::create([
                    'company' => $company, 'customer_type' => $type, 'contact_name' => $this->name(), 'email' => 'buyer'.($i + 1).'@example.com', 'phone' => $this->phone(),
                    'country' => $country, 'delivery_address' => $address, 'postcode' => $postcode, 'required_by' => today()->addDays(mt_rand(7, 30)),
                    'remarks' => $i % 3 === 0 ? 'Sample: forklift on site, no deliveries before 8am.' : null,
                ]);
                foreach ($lines as $n => [$product, $desc, $t, $w, $l, $treat, $qty, $unit]) {
                    $q->lines()->create(['position' => $n + 1, 'product' => $product, 'description' => $desc, 'thickness_mm' => $t, 'width_mm' => $w, 'length_m' => $l, 'treatment' => $treat, 'quantity' => $qty, 'unit' => $unit]);
                }
                $q->log('Received from the website.');
                $q->routeToRegion();

                return $q;
            });
            if ($total !== null) {
                $quote->forceFill(['quoted_total' => $total])->save();
            }
            if ($status !== QuoteStatus::New) {
                $who = $quote->assignee ?? $staff['admin'];
                $this->at(now()->subHours(max(1, $hours - mt_rand(4, 20))), function () use ($quote, $status, $who) {
                    if ($quote->claimStatus($status)) {
                        $quote->log('Status changed to '.$status->label().'.', 'status', $who);
                    }
                });
            }
        }
        $first = QuoteRequest::where('status', QuoteStatus::Pricing)->oldest()->first();
        $first?->log('Sample: waiting on kiln space for the 47 × 100 before confirming the delivery week.', 'note', $first->assignee);
    }

    private function frame(User $designer): void
    {
        $rows = [
            ['Self-build, two-storey house (sample)', FrameClientType::SelfBuilder, FrameProjectType::House, 'Site at Ballinalee Road', 'Longford', 'N39 E4P2', PlanningStatus::Granted, 4, 215, null, 4, FrameStatus::New, 3],
            [null, FrameClientType::Developer, FrameProjectType::Scheme, 'Phase 2, lands at Moate Road', 'Westmeath', null, PlanningStatus::Applied, 9, 1850, 18, 5, FrameStatus::DesignReview, 2],
            [null, FrameClientType::SelfBuilder, FrameProjectType::House, 'Townland of Drumlish', 'Longford', null, PlanningStatus::NotApplied, 12, 180, null, 1, FrameStatus::New, 0],
            ['Rear extension (sample)', FrameClientType::Contractor, FrameProjectType::Extension, '22 Main Street', 'Roscommon', 'F42 X3N8', PlanningStatus::Granted, 2, 45, null, 6, FrameStatus::Quoted, 2],
            [null, FrameClientType::Architect, FrameProjectType::Commercial, 'Creche building, Ennis Road', 'Clare', null, PlanningStatus::Granted, 6, 420, null, 12, FrameStatus::Won, 3],
            [null, FrameClientType::Contractor, FrameProjectType::Trusses, 'Agricultural shed roof, Mullingar', 'Westmeath', null, PlanningStatus::Commenced, 1, null, null, 2, FrameStatus::New, 1],
            [null, FrameClientType::SelfBuilder, FrameProjectType::House, 'Site near Carrick-on-Shannon', 'Leitrim', null, PlanningStatus::Granted, 7, 240, null, 20, FrameStatus::Lost, 2],
        ];
        foreach ($rows as $i => [$company, $client, $project, $site, $county, $eircode, $planning, $months, $area, $units, $days, $status, $drawings]) {
            $e = $this->at(now()->subDays($days)->subHours(mt_rand(1, 8)), function () use ($company, $client, $project, $site, $county, $eircode, $planning, $months, $area, $units, $drawings, $i) {
                $e = FrameEnquiry::create([
                    'client_type' => $client, 'project_type' => $project, 'name' => $this->name(), 'company' => $client === FrameClientType::SelfBuilder ? null : ($company ?? 'Sample '.$client->label().' Ltd'),
                    'email' => 'frame'.($i + 1).'@example.com', 'phone' => $this->phone(), 'site_address' => $site.' (sample)', 'county' => $county, 'eircode' => $eircode,
                    'planning_status' => $planning, 'start_month' => now()->addMonths($months)->format('Y-m'), 'floor_area_m2' => $area, 'units' => $units,
                    'message' => $i % 2 === 0 ? 'Sample: we would like an airtight, A-rated build with a vaulted ceiling over the kitchen.' : null,
                ]);
                for ($d = 1; $d <= $drawings; $d++) {
                    $name = ['Ground floor plan', 'First floor plan', 'Elevations', 'Sections'][min($d, 4) - 1];
                    $path = 'frame/'.$e->id.'/demo-'.$d.'.pdf';
                    $this->pdf($path, $name.' - '.$e->reference);
                    $e->drawings()->create(['path' => $path, 'original_name' => $name.'.pdf', 'mime' => 'application/pdf', 'size' => 1400, 'added_by' => 'customer']);
                }
                $e->log('Received from the website'.($drawings ? " with {$drawings} ".Str::plural('drawing', $drawings) : '').'.');

                return $e;
            });
            if ($status !== FrameStatus::New) {
                $this->at(now()->subDays(max(0, $days - 1)), function () use ($e, $status, $designer) {
                    $e->forceFill(['assigned_to' => $designer->id])->save();
                    if ($e->claimStatus($status)) {
                        $e->log('Status changed to '.$status->label().'.', 'status', $designer);
                    }
                });
                if (in_array($status, [FrameStatus::Quoted, FrameStatus::Won, FrameStatus::Lost], true)) {
                    $e->forceFill(['quoted_total' => (string) (($e->floor_area_m2 ?? 100) * mt_rand(260, 340))])->save();
                }
            }
        }
    }

    private function bookings(User $sales): void
    {
        $hauliers = ['Midland Haulage (sample)', 'Shannon Timber Transport (sample)', 'Ayr Road Freight (sample)', 'Lothian Logistics (sample)', 'Cork Forestry Haulage (sample)'];
        $regs = ['241-LD-1234', '231-C-5678', 'SK24 ABC', 'SN73 XYZ', '222-WH-901', '242-D-4455', 'SV22 TRN', '191-LS-777', 'SA21 KLM', '232-CW-310'];
        foreach (range(0, 9) as $i) {
            $plant = Plant::mills()[$i % 4];
            $days = [-6, -3, -1, 1, 1, 2, 3, 5, 7, 9][$i];
            $status = match (true) {
                $days < 0 => BookingStatus::Completed,
                $i === 4 => BookingStatus::Declined,
                $i % 2 === 1 => BookingStatus::Confirmed,
                default => BookingStatus::Requested,
            };
            $b = $this->at(now()->addDays(min($days, 0) - 2)->setTime(9 + $i % 6, 10 * ($i % 5)), fn () => DeliveryBooking::create([
                'plant' => $plant, 'purpose' => [BookingPurpose::Collect, BookingPurpose::Logs, BookingPurpose::Collect, BookingPurpose::Residues][$i % 4],
                'slot_date' => today()->addDays($days), 'slot' => BookingSlot::cases()[$i % 4], 'haulier' => $hauliers[$i % 5], 'driver_name' => $i % 3 ? $this->name() : null,
                'email' => 'dispatch'.($i % 5 + 1).'@example.com', 'phone' => $this->phone(), 'vehicle_reg' => $regs[$i], 'vehicle_type' => VehicleType::cases()[$i % 5],
                'order_reference' => $i % 2 ? 'SO-'.(48200 + $i * 17) : null, 'remarks' => $i === 2 ? 'Sample: driver needs a copy of the delivery docket at the gate.' : null,
            ]));
            $this->at($b->created_at, fn () => $b->log('Received from the website.'));
            if ($status !== BookingStatus::Requested && $b->claimStatus($status)) {
                $b->log('Status changed to '.$status->label().'.', 'status', $sales);
            }
        }
    }

    private function careers(User $hr): void
    {
        $jobs = [
            ['Shift supervisor (sample)', Plant::Longford, JobCategory::Operative, EmploymentType::Shifts, 'Rotating days and lates', 1, 'Run a production shift on the sawline and grading line, keeping output, quality and safety on target.', "Plan the shift with the production manager\nLead toolbox talks and keep the shift log\nCover line positions when needed", "Supervisory experience in manufacturing or a sawmill\nGood with people and with numbers\nManual handling and first aid training an advantage"],
            ['14 tonne forklift driver (sample)', Plant::Fermoy, JobCategory::Operative, EmploymentType::Shifts, 'Days, Monday to Friday', 2, 'Load lorries and move timber packs around the yard and kilns with a 14 tonne forklift.', "Load and unload timber packs\nFeed the kilns and planing line\nDaily pre-use checks", "Forklift certificate for the class of truck\nYard experience preferred\nCareful and tidy"],
            ['360 log handler (sample)', Plant::Fermoy, JobCategory::Operative, EmploymentType::Shifts, 'Early shift', 1, 'Feed the log deck and keep the log yard in order with a 360 material handler.', "Sort and stack logs by grade\nFeed the infeed deck\nKeep the yard clear and safe", "Experience on a 360 or similar machine\nCPCS, CSCS or Safe Pass ticket an advantage"],
            ['General operative (sample)', Plant::Windymains, JobCategory::Operative, EmploymentType::FullTime, 'Days, 39 hours', 3, 'Work on the fencing and treatment lines: feeding, stacking, strapping and labelling packs.', "Feed and off-bear on the production lines\nStrap and label packs\nKeep work areas clean", "No experience needed: full training given\nHappy working outdoors and on your feet"],
            ['Maintenance fitter (sample)', Plant::Troon, JobCategory::Maintenance, EmploymentType::Shifts, 'Rotating shifts, call-out rota', 1, 'Keep the sawline, conveyors and the biomass CHP plant running through planned and breakdown maintenance.', "Planned maintenance and breakdown repairs\nHydraulics, pneumatics, gearboxes and conveyors\nRecord work in the maintenance system", "Time-served mechanical fitter or equivalent\nSawmill or heavy industry experience\nWelding and fabrication skills"],
            ['Electrician (sample)', Plant::Longford, JobCategory::Maintenance, EmploymentType::FullTime, 'Days, with call-out rota', 1, 'Electrical maintenance of drives, PLCs and controls on the sawline and kilns.', "Fault-find on motors, drives and controls\nSupport improvements and installations", "Qualified electrician\nPLC experience an advantage"],
            ['HGV driver, Class C+E (sample)', Plant::Longford, JobCategory::Driver, EmploymentType::FullTime, 'Days, overnights possible', 2, 'Deliver timber to merchants and manufacturers across Ireland in a curtainsider or flatbed with a crane.', "Deliver loads safely and on time\nSecure loads and check paperwork", "C+E licence and Driver CPC\nHIAB / lorry-mounted crane experience an advantage"],
            ['Timber frame erector (sample)', Plant::Dte, JobCategory::Frame, EmploymentType::FullTime, 'Days, site-based', 4, 'Erect DTE timber frame homes and roof trusses on sites across Ireland.', "Erect wall panels, floors and roof trusses\nWork from drawings with the site foreman", "Carpentry or timber frame experience\nSafe Pass\nFull driving licence"],
            ['Sales administrator (sample)', Plant::Troon, JobCategory::Office, EmploymentType::FullTime, 'Office hours', 1, 'Process orders and arrange deliveries for UK customers.', "Enter and confirm orders\nBook transport\nAnswer customer calls", "Office or sales support experience\nConfident on the phone and with Excel"],
        ];
        $posts = [];
        foreach ($jobs as [$title, $plant, $cat, $type, $hours, $openings, $summary, $duties, $req]) {
            $posts[] = JobPosting::create(['slug' => Str::slug(str_replace(' (sample)', '', $title).' '.$plant->value), 'title' => $title, 'plant' => $plant, 'category' => $cat, 'employment_type' => $type, 'status' => JobStatus::Open,
                'hours' => $hours, 'openings' => $openings, 'summary' => $summary, 'duties' => $duties, 'requirements' => $req, 'closes_on' => today()->addDays(mt_rand(14, 45)), 'is_demo' => true]);
        }
        JobPosting::create(['slug' => 'production-manager-closed', 'title' => 'Production manager (sample, closed)', 'plant' => Plant::Fermoy, 'category' => JobCategory::Office, 'employment_type' => EmploymentType::FullTime,
            'status' => JobStatus::Closed, 'openings' => 1, 'summary' => 'Closed sample posting.', 'is_demo' => true]);

        $towns = ['Longford', 'Edgeworthstown', 'Ballymahon', 'Fermoy', 'Mitchelstown', 'Mallow', 'Haddington', 'Dalkeith', 'Troon', 'Kilmarnock', 'Ayr', 'Granard'];
        $statuses = [ApplicationStatus::New, ApplicationStatus::New, ApplicationStatus::Screening, ApplicationStatus::ManagerReview, ApplicationStatus::New, ApplicationStatus::Interview, ApplicationStatus::Offer, ApplicationStatus::Hired, ApplicationStatus::Declined];
        for ($i = 0; $i < 24; $i++) {
            $job = $i % 6 === 5 ? null : $posts[$i % count($posts)];
            $cat = $job->category ?? JobCategory::Operative;
            [$first, $last] = explode(' ', $this->name(), 2);
            $status = $statuses[$i % count($statuses)];
            $app = $this->at(now()->subDays(mt_rand(0, 40))->subHours(mt_rand(0, 10)), fn () => JobApplication::create([
                'job_posting_id' => $job?->id, 'plant' => $job ? $job->plant : ($i % 2 ? Plant::Fermoy : null), 'category' => $cat, 'status' => ApplicationStatus::New,
                'first_name' => $first, 'last_name' => $last, 'phone' => $this->phone(), 'email' => $cat->needsCv() || $i % 2 ? Str::ascii(strtolower($first.'.'.str_replace("'", '', $last).$i)).'@example.com' : null,
                'home_area' => $towns[$i % count($towns)], 'right_to_work' => $i % 7 !== 3, 'experience' => Experience::cases()[mt_rand(0, 4)],
                'licences' => $cat->asksLicence() ? 'C+E, Driver CPC (sample)' : null,
                'qualifications' => $cat === JobCategory::Maintenance ? 'Sample: time-served fitter, 6 years in a quarry.' : ($i % 3 ? 'Sample: Safe Pass, forklift ticket.' : null),
                'available_from' => today()->addDays(mt_rand(3, 30)), 'consented_at' => now(),
            ]));
            if ($cat->needsCv() || $i % 3 === 0) {
                $path = 'cvs/demo-'.$app->id.'.pdf';
                $this->pdf($path, 'CV - '.$app->full_name);
                $app->forceFill(['cv_path' => $path, 'cv_name' => 'CV.pdf', 'cv_mime' => 'application/pdf', 'cv_size' => 1400])->save();
            }
            $this->at($app->created_at, fn () => $app->log('Received from the website.'));
            if ($status !== ApplicationStatus::New) {
                $this->at($app->created_at->copy()->addDays(mt_rand(1, 3)), function () use ($app, $status, $hr) {
                    $app->forceFill(['reviewer_id' => $hr->id])->save();
                    if ($app->claimStatus($status)) {
                        $app->log('Moved to '.$status->label().'.', 'status', $hr);
                    }
                });
            }
        }
    }

    private function enquiries(): void
    {
        $rows = [
            [EnquiryTopic::Stockist, 'Sample Hardware & Farm Supplies', null, 'We are a hardware store in Co. Mayo and would like to stock Glenfence panels. Who should we speak to?', EnquiryStatus::New],
            [EnquiryTopic::Logs, 'Sample Forestry Owners Group', Plant::Fermoy, 'We have 30 hectares of Sitka spruce ready for clearfell next year and would like a price for the logs.', EnquiryStatus::New],
            [EnquiryTopic::Sales, null, Plant::Troon, 'Can you send me the current price list for fencing posts delivered to Ayr?', EnquiryStatus::Answered],
            [EnquiryTopic::Media, 'Sample Community Radio', Plant::Longford, 'We would like to interview someone about the Longford Ladies sponsorship.', EnquiryStatus::Answered],
            [EnquiryTopic::Other, null, null, 'Do you offer site visits for a secondary school class studying forestry?', EnquiryStatus::Closed],
        ];
        foreach ($rows as $i => [$topic, $org, $plant, $message, $status]) {
            $e = $this->at(now()->subDays($i * 3 + 1), fn () => Enquiry::create(['topic' => $topic, 'plant' => $plant, 'name' => $this->name(), 'organisation' => $org, 'email' => 'enquiry'.($i + 1).'@example.com', 'phone' => $this->phone(), 'message' => $message.' (sample)', 'status' => $status]));
            $this->at($e->created_at, fn () => $e->log('Received from the website.'));
        }
    }
}
