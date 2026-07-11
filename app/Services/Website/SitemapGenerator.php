<?php

namespace App\Services\Website;

use App\Models\Website\Bank;
use App\Models\Website\Contact;
use App\Models\Website\City;
use App\Models\Website\Office;
use App\Models\Website\Post;
use App\Models\Website\Sentence;
use Spatie\Sitemap\Sitemap;

class SitemapGenerator
{
    public function generate(): string
    {
        $baseUrl = rtrim((string) config('app.url'), '/');
        $domain = $baseUrl . '/';

        $sitemap = Sitemap::create()
            ->add($baseUrl)
            // ->add($domain . 'oferta')
            ->add($domain . 'zawieszenie-rat')
            ->add($domain . 'kredyty-frankowe')
            ->add($domain . 'kredyty-euro')
            ->add($domain . 'kancelaria')
            ->add($domain . 'kontakt')
            ->add($domain . 'opinie')
            ->add($domain . 'analiza')
            ->add($domain . 'faq');

        foreach (Office::query()->active()->pluck('slug') as $slug) {
            $sitemap->add($domain . 'kancelaria/' . $slug);
        }

        $sitemap->add($domain . 'gdzie-dzialamy');

        foreach (City::query()->where('is_published', 1)->pluck('slug') as $slug) {
            $sitemap->add($domain . 'kredyty-frankowe-' . $slug);
            $sitemap->add($domain . 'kredyt-euro-kancelaria-' . $slug);
        }

        $sitemap->add($domain . 'klauzule-niedozwolone');

        foreach (Bank::query()->where('is_published', 1)->pluck('slug') as $slug) {
            $sitemap->add($domain . 'bank/' . $slug);
        }

        $sitemap->add($domain . 'blog');

        foreach (Post::query()->where('is_published', 1)->where('date', '<=', now())->where('category', 'blog')->pluck('slug') as $post) {
            $sitemap->add($domain . 'blog/' . $post);
        }

        $sitemap->add($domain . 'orzecznictwo');

        foreach (Post::query()->where('is_published', 1)->where('date', '<=', now())->where('category', 'orzecznictwo')->pluck('slug') as $post) {
            $sitemap->add($domain . 'orzecznictwo/' . $post);
        }

        $sitemap->add($domain . 'wyroki');
        $sitemap->add($domain . 'wyroki/kredyty-euro');
        $sitemap->add($domain . 'wyroki/kredyty-frankowe');
        $sitemap->add($domain . 'wyroki/splacone');

        foreach (Sentence::query()->where('is_published', 1)->pluck('slug') as $sentence) {
            $sitemap->add($domain . 'wyrok/' . $sentence);
        }

        foreach (Contact::query()->where('category', 'Sędzia')->whereNot('slug', '')->orderBy('sort_name')->get() as $judge) {
            if (count($judge->judge_published_sentences)) {
                $sitemap->add($domain . 'wyroki/sedzia/' . $judge['slug']);
            }
        }

        foreach (Contact::query()->where('category', 'Sąd')->whereNot('slug', '')->orderBy('sort_name')->get() as $court) {
            if (count($court->court_published_sentences)) {
                $sitemap->add($domain . 'wyroki/sad/' . $court['slug']);
            }
        }

        foreach (Bank::query()->where('is_published', 1)->whereNot('slug', '')->orderBy('bank')->get() as $bank) {
            if (count($bank->bank_published_sentences)) {
                $sitemap->add($domain . 'wyroki/bank/' . $bank['slug']);
            }
        }

        $sitemap->add($domain . 'mapa-strony');
        $sitemap->add($domain . 'polityka-prywatnosci');

        $path = public_path('sitemap.xml');

        $sitemap->writeToFile($path);

        return $path;
    }
}
