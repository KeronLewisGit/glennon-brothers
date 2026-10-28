<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\QuoteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string}> */
    public static function pages(): array
    {
        return [
            'home' => ['/'], 'plants' => ['/plants'], 'longford' => ['/plants/longford'], 'fermoy' => ['/plants/fermoy'],
            'windymains' => ['/plants/windymains'], 'troon' => ['/plants/troon'], 'products' => ['/products'],
            'frame' => ['/timber-frame'], 'frame-enquire' => ['/timber-frame/enquire'], 'sustainability' => ['/sustainability'],
            'about' => ['/about'], 'news' => ['/news'], 'contact' => ['/contact'], 'quote' => ['/trade-quote'],
            'booking' => ['/haulier-booking'], 'careers' => ['/careers'], 'apply' => ['/careers/apply'],
            'privacy' => ['/privacy'], 'credits' => ['/photo-credits'], 'sitemap' => ['/sitemap'], 'login' => ['/login'],
        ];
    }

    #[DataProvider('pages')]
    public function test_public_pages_render(string $url): void
    {
        $this->get($url)->assertOk()->assertSee('Glennon Brothers', false)->assertSee('lang="en-IE"', false);
    }

    public function test_every_inner_page_opens_with_the_shared_header(): void
    {
        foreach (self::pages() as [$url]) {
            if (in_array($url, ['/', '/login'], true)) {
                continue;
            }
            $html = (string) $this->get($url)->getContent();
            $this->assertStringContainsString('class="page-head rings relative isolate', $html, "{$url} has no shared header");
            $this->assertStringContainsString('class="bale"', $html, "{$url} header has no bale band");
            $this->assertStringContainsString('class="stamp"', $html, "{$url} header has no stamp kicker");
        }
        // The home hero is a variant of the same band.
        $this->get('/')->assertSee('class="home-hero rings relative isolate', false)->assertSee('class="ticket', false);
    }

    public function test_unknown_plants_404(): void
    {
        $this->get('/plants/enniskillen')->assertNotFound();
    }

    public function test_every_plant_page_shows_its_published_address_and_phone(): void
    {
        foreach ((array) config('glennon.plants') as $key => $p) {
            $res = $this->get('/plants/'.$key)->assertOk();
            $res->assertSee($p['address'][0])->assertSee($p['address'][2])->assertSee($p['phone']);
            $this->get('/contact')->assertSee($p['phone']);
        }
    }

    public function test_every_photo_is_credited_and_exists(): void
    {
        $credits = config('photo-credits');
        foreach (array_keys((array) config('glennon.photos')) as $key) {
            $this->assertArrayHasKey($key, $credits, "{$key} has no credit");
            $this->assertNotEmpty(photo_widths($key));
            foreach (photo_widths($key) as $w) {
                $this->assertFileExists(public_path("img/{$key}-{$w}.webp"));
            }
        }
        $this->get('/photo-credits')->assertSee('Wikimedia Commons')->assertSee('rights to confirm');
    }

    public function test_every_public_page_has_a_photo_and_stock_photos_are_labelled(): void
    {
        foreach (self::pages() as [$url]) {
            if ($url === '/login') {
                continue;
            }
            $html = (string) $this->get($url)->getContent();
            $this->assertMatchesRegularExpression('/<img [^>]*src="[^"]+\/img\//', $html, "{$url} has no photograph");
        }
        $this->get('/thanks')->assertRedirect('/'); // no submission: covered by the form tests
        foreach (['/timber-frame', '/timber-frame/enquire', '/sustainability', '/'] as $url) {
            $this->get($url)->assertSee('Illustrative photo');
        }
        // Headers with the group's own photos carry no label.
        $this->assertStringNotContainsString('Illustrative photo', (string) $this->get('/plants/fermoy')->getContent());
    }

    public function test_figures_awards_and_certificates_come_from_config_only(): void
    {
        $home = $this->get('/');
        foreach ((array) config('glennon.figures') as $f) {
            $home->assertSee($f['value']);
        }
        $about = $this->get('/about');
        foreach ((array) config('glennon.awards') as $a) {
            $about->assertSee($a['title'])->assertSee($a['url'], false);
        }
        $sus = $this->get('/sustainability');
        foreach ((array) config('glennon.certifications') as $c) {
            $sus->assertSee($c['title']);
        }
        // Nothing invented: no testimonials, no client logos, no staff headcounts.
        foreach (['/', '/about', '/plants', '/products', '/timber-frame', '/careers'] as $url) {
            $html = strtolower(strip_tags((string) $this->get($url)->getContent()));
            foreach (['testimonial', 'what our customers say', 'trusted by', 'employees', 'staff of'] as $phrase) {
                $this->assertStringNotContainsString($phrase, $html, "{$url} says '{$phrase}'");
            }
        }
    }

    public function test_contact_details_are_the_groups_published_ones(): void
    {
        $this->get('/')->assertSee('+353 43 3350800')->assertSee('info@glennonbrothers.ie')->assertSee('tel:+353433350800', false);
        $this->get('/contact')->assertSee('tr.info@glennonbrothers.co.uk')->assertSee('tel:+441292800600', false);
    }

    public function test_privacy_notice_is_written_for_ireland_and_the_uk(): void
    {
        $this->get('/privacy')->assertSee('Data Protection Acts 1988 to 2018')->assertSee('Data Protection Commission')->assertSee('UK GDPR');
    }

    public function test_submit_buttons_read_correctly_without_javascript(): void
    {
        $html = (string) $this->get('/contact')->getContent();
        $this->assertMatchesRegularExpression('/<span hidden[^>]*>Sending…<\/span>/u', $html);
    }

    public function test_quote_form_shows_three_lines_without_javascript(): void
    {
        $html = (string) $this->get('/trade-quote')->getContent();
        $this->assertSame(QuoteRequest::MAX_LINES, substr_count($html, 'class="quote-line'));
        $this->assertSame(QuoteRequest::MAX_LINES - 3, preg_match_all('/class="quote-line[^"]*"[^>]*\shidden\s/', $html));
    }

    public function test_legacy_urls_redirect(): void
    {
        foreach ((array) config('glennon.legacy_redirects') as $from => $to) {
            $this->get($from)->assertRedirect($to)->assertStatus(301);
        }
    }

    public function test_open_jobs_list_and_closed_jobs_404(): void
    {
        $open = JobPosting::factory()->create(['title' => 'Grading line operative']);
        $closed = JobPosting::factory()->create(['title' => 'Old role', 'closes_on' => today()->subDay()]);

        $this->get('/careers')->assertSee('Grading line operative')->assertDontSee('Old role');
        $this->get('/careers?plant=troon')->assertDontSee('Grading line operative');
        $this->get('/careers?plant=longford&family=operative')->assertSee('Grading line operative');
        $this->get('/careers?plant[]=x&family[]=y')->assertOk();
        $this->get('/careers/'.$open->slug)->assertOk()->assertSee('JobPosting', false)->assertSee('"addressCountry":"IE"', false);
        $this->get('/careers/'.$closed->slug)->assertNotFound();
        $this->get('/plants/longford')->assertSee('Grading line operative');
    }

    public function test_sitemap_lists_plants_and_jobs(): void
    {
        $job = JobPosting::factory()->create();
        $xml = (string) $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')->getContent();
        foreach (array_keys((array) config('glennon.plants')) as $key) {
            $this->assertStringContainsString(route('plants.show', $key), $xml);
        }
        $this->assertStringContainsString(route('careers.show', $job), $xml);
    }

    public function test_preview_mode_adds_banner_and_noindex(): void
    {
        config(['glennon.preview' => true]);
        $this->get('/')->assertSee('Concept preview')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_security_headers(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_no_text_from_the_starter_build_is_left(): void
    {
        foreach (array_merge(array_column(self::pages(), 0), ['/no-such-page']) as $url) {
            $html = (string) $this->get($url)->getContent();
            foreach (['Seaton', 'Jamaica', 'Kingston', 'parish', 'tender', 'JMD', 'J$'] as $word) {
                $this->assertStringNotContainsString($word, $html, "{$url} still says {$word}");
            }
        }
    }
}
