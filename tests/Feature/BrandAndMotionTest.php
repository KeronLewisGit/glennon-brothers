<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The brand match (logo, red tab navigation, red footer bar) and the motion layer. Motion is
 * progressive: the server sends finished content, and only script adds a pre-animation state.
 */
class BrandAndMotionTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private const PAGES = ['/', '/plants', '/plants/troon', '/products', '/timber-frame', '/about', '/careers', '/contact', '/trade-quote'];

    public function test_every_page_carries_the_groups_logo_tabs_and_footer_bar(): void
    {
        foreach (self::PAGES as $url) {
            $html = (string) $this->get($url)->getContent();
            $this->assertStringContainsString('role="img" aria-label="Glennon Brothers"', $html, "{$url} has no logo");
            $this->assertStringContainsString('class="logo-trees" fill="#de0a2a"', $html, "{$url} logo trees are not the brand red");
            $this->assertMatchesRegularExpression('/<nav[^>]*class="tabs/', $html, "{$url} has no tab navigation");
            $this->assertStringContainsString('class="footer-bar', $html, "{$url} has no red footer bar");
            $this->assertStringContainsString('data-theme-toggle', $html, "{$url} has no theme toggle");
        }
        // The current section's tab is marked for assistive tech, not just by colour.
        $this->assertMatchesRegularExpression('/class="tab"\s+aria-current="page"\s*>Products</', (string) $this->get('/products')->getContent());
    }

    public function test_home_has_the_map_story_marquee_and_counters(): void
    {
        $res = $this->get('/');
        $res->assertSee('class="plant-map', false)
            ->assertSee('aria-label="Map of the four Glennon Brothers mills"', false)
            ->assertSee('data-process', false)
            ->assertSee('class="marquee', false)
            ->assertSee('data-split', false)
            ->assertSee('data-count', false);

        // Every mill has a pin on the map that links to its page.
        foreach (array_keys((array) config('glennon.plants')) as $key) {
            $res->assertSee('data-plant="'.$key.'" style', false)->assertSee(route('plants.show', $key), false);
        }
        // The marquee's second copy is decorative only.
        $this->assertSame(2, substr_count((string) $res->getContent(), '<ul class="marquee-list"'));
        $this->assertSame(1, preg_match_all('/<ul class="marquee-list"\s+aria-hidden="true"/', (string) $res->getContent()));
    }

    public function test_content_is_complete_without_javascript(): void
    {
        $html = (string) $this->get('/')->getContent();

        // Headline, figures and the story text are in the HTML, not built by script.
        $this->assertStringContainsString('Irish and Scottish timber, sawn by one family since 1913.', $html);
        foreach ((array) config('glennon.figures') as $f) {
            $this->assertStringContainsString('data-count>'.$f['value'].'</b>', $html);
        }
        foreach (['Logs in', 'Sawn', 'Dried and graded', 'Packed and sent', 'Built into homes'] as $step) {
            $this->assertStringContainsString($step, $html);
        }
        // The first scene is the one shown before script runs.
        $this->assertStringContainsString('data-process data-step="1"', $html);

        // Nothing starts hidden in the markup: hidden states only exist under .motion, which
        // the head script sets (and drops again if the app script never starts).
        foreach (self::PAGES as $url) {
            $page = (string) $this->get($url)->getContent();
            $this->assertDoesNotMatchRegularExpression('/style="[^"]*(opacity:\s*0|visibility:\s*hidden)/', $page, "{$url} hides content inline");
            $this->assertStringContainsString("classList.add('motion')", $page);
            $this->assertStringContainsString('prefers-reduced-motion: reduce', $page);
        }
    }

    public function test_motion_css_is_gated_and_respects_reduced_motion(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('@media (prefers-reduced-motion: no-preference)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        // The marquee pauses on hover and keyboard focus.
        $this->assertStringContainsString('.motion .marquee:hover .marquee-track, .motion .marquee:focus-within .marquee-track { animation-play-state: paused; }', $css);
        // Every rule that sets a hidden starting state is scoped to .motion.
        foreach (preg_split('/\R/', $css) ?: [] as $line) {
            if (preg_match('/opacity:\s*0[;\s}]/', $line) && ! str_contains($line, '.motion') && ! str_contains($line, '@keyframes') && ! str_contains($line, 'from {') && ! str_contains($line, '%')) {
                $this->assertMatchesRegularExpression('/\.scene|map-pulse|celebrate-chip|\[aria-hidden/', $line, 'Ungated hidden state: '.trim($line));
            }
        }
    }

    public function test_about_timeline_and_thanks_celebration_render(): void
    {
        $this->get('/about')->assertSee('data-timeline', false)->assertSee('class="timeline-item"', false);
        $this->withSession(['last_submission' => ['reference' => 'EN-26-00001', 'kind' => 'Enquiry']])
            ->get('/thanks')->assertOk()->assertSee('class="celebrate', false)->assertSee('role="status"', false);
    }
}
