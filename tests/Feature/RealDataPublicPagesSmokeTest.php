<?php

namespace Tests\Feature;

use App\Support\Website\PracticeContext;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RealDataPublicPagesSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! filter_var(env('RUN_REAL_DATA_SMOKE', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Set RUN_REAL_DATA_SMOKE=1 to run checks against the local imported MySQL data.');
        }

        if (DB::connection()->getDatabaseName() !== 'preda_app_local_fresh') {
            $this->markTestSkipped('Real data smoke tests are scoped to preda_app_local_fresh.');
        }
    }

    public function test_public_static_pages_render_on_real_data(): void
    {
        foreach ($this->publicPaths() as $path) {
            $this->get('http://preda-app.test'.$path)
                ->assertOk();
        }
    }

    public function test_public_dynamic_pages_render_on_real_data(): void
    {
        $blogPost = DB::table('website_posts')
            ->where('is_published', true)
            ->where('category', 'blog')
            ->whereNotNull('slug')
            ->orderBy('id')
            ->first();

        $caseLawPost = DB::table('website_posts')
            ->where('is_published', true)
            ->where('category', 'orzecznictwo')
            ->whereNotNull('slug')
            ->orderBy('id')
            ->first();

        $sentence = DB::table('website_sentences')
            ->where('is_published', true)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->first();

        $bank = DB::table('website_banks')
            ->where('is_published', true)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->first();

        $city = DB::table('website_cities')
            ->where('is_published', true)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->first();

        $this->assertNotNull($blogPost, 'Missing a published website blog post in the imported real data.');
        $this->assertNotNull($caseLawPost, 'Missing a published website case-law post in the imported real data.');
        $this->assertNotNull($sentence, 'Missing a published website sentence in the imported real data.');
        $this->assertNotNull($bank, 'Missing a published website bank in the imported real data.');
        $this->assertNotNull($city, 'Missing a published website city in the imported real data.');

        foreach ([
            '/blog/'.$blogPost->slug,
            '/orzecznictwo/'.$caseLawPost->slug,
            '/wyrok/'.$sentence->slug,
            '/bank/'.$bank->slug,
            '/kredyty-frankowe-'.$city->slug,
            '/kredyt-euro-kancelaria-'.$city->slug,
        ] as $path) {
            $this->get('http://preda-app.test'.$path)
                ->assertOk();
        }
    }

    public function test_public_legacy_redirects_still_work(): void
    {
        $this->get('http://preda-app.test/wyroki/euro')
            ->assertRedirect('http://preda-app.test/wyroki/kredyty-euro');

        $this->get('http://preda-app.test/klauzule-niedozwolone/mbank')
            ->assertRedirect('http://preda-app.test/bank/mbank');

        $this->get('http://preda-app.test/uniewaznienie-umowy')
            ->assertRedirect('http://preda-app.test/blog/uniewaznienie-umowy');

        $this->get('http://preda-app.test/zwrot-splacony-kredyt')
            ->assertRedirect('http://preda-app.test/blog/zwrot-splacony-kredyt');

        $this->get('http://preda-app.test/ugoda-z-bankiem')
            ->assertRedirect('http://preda-app.test/blog/ugoda-z-bankiem');

        $this->get('http://preda-app.test/konsultacje')
            ->assertRedirect('https://calendar.app.google/8wZMGof5vFbqMhND8');

        $this->get('http://preda-app.test/konsultacje/wiktoria-rajzynger')
            ->assertRedirect('https://calendar.app.google/CduSKq9VRVB6yG9c7');
    }

    public function test_public_family_law_pages_follow_active_context_config(): void
    {
        $expectedStatus = PracticeContext::isActive(PracticeContext::FAMILY_LAW) ? 200 : 404;

        foreach ([
            '/rozwod',
            '/podzial-majatku',
        ] as $path) {
            $this->get('http://preda-app.test'.$path)
                ->assertStatus($expectedStatus);
        }
    }

    private function publicPaths(): array
    {
        return [
            '/',
            '/wyroki',
            '/wyroki/kredyty-euro',
            '/wyroki/kredyty-frankowe',
            '/wyroki/splacone',
            '/zawieszenie-rat',
            '/kancelaria',
            '/kontakt',
            '/analiza',
            '/faq',
            '/blog',
            '/orzecznictwo',
            '/polityka-prywatnosci',
            '/gdzie-dzialamy',
            '/opinie',
            '/mapa-strony',
            '/klauzule-niedozwolone',
            '/kredyty-frankowe',
            '/kredyty-euro',
            '/splacony-kredyt-frankowy',
            '/kancelaria/glogow',
            '/kancelaria/zielona-gora',
        ];
    }
}
