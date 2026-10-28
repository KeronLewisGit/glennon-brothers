<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PageController extends Controller
{
    /** Public routes listed in sitemap.xml (plus each plant and open job). */
    public const array SITEMAP_ROUTES = [
        'home', 'plants.index', 'products', 'frame.index', 'frame.create', 'sustainability', 'about', 'news', 'quotes.create',
        'bookings.create', 'careers.index', 'careers.apply', 'contact', 'privacy', 'credits',
    ];

    public function home(): View
    {
        return view('pages.home', ['openJobs' => JobPosting::open()->count()]);
    }

    public function plants(): View
    {
        return view('pages.plants.index');
    }

    public function plant(string $plant): View
    {
        $data = gb('plants.'.$plant);
        abort_unless(is_array($data), 404);

        return view('pages.plants.show', [
            'key' => $plant,
            'plant' => $data,
            'jobs' => JobPosting::open()->where('plant', $plant)->orderBy('title')->get(),
        ]);
    }

    public function products(): View
    {
        return view('pages.products');
    }

    public function timberFrame(): View
    {
        return view('pages.frame.index');
    }

    public function sustainability(): View
    {
        return view('pages.sustainability');
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function news(): View
    {
        return view('pages.news');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function credits(): View
    {
        return view('pages.credits');
    }

    public function sitemapPage(): View
    {
        return view('pages.sitemap', ['jobs' => JobPosting::open()->orderBy('title')->get(['slug', 'title'])]);
    }

    public function sitemap(): Response
    {
        $urls = collect(self::SITEMAP_ROUTES)
            ->map(fn (string $r) => ['loc' => route($r), 'lastmod' => null])
            ->concat(collect(array_keys((array) gb('plants')))->map(fn (string $p) => ['loc' => route('plants.show', $p), 'lastmod' => null]))
            ->concat(JobPosting::open()->get(['slug', 'updated_at'])->map(fn (JobPosting $j) => ['loc' => route('careers.show', $j), 'lastmod' => $j->updated_at->toAtomString()]));

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
