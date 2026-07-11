<?php

namespace App\Http\Controllers\Website\Pages;

use App\Models\Website\Post;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;
use Illuminate\Support\Str;
use Illuminate\View\Component as BladeComponent;
use Illuminate\Contracts\View\View as ViewContract;

class PostController extends Controller
{
    public function __invoke(string $slug)
    {
        $post = Post::with('author')
            ->where('is_published', true)
            ->where('slug', $slug)
            ->firstOrFail();

        Seo::title($post->metatitle);
        Seo::description($post->metadescription);

        // Zamień dyrektywy LoadComponent#... na zrenderowane fragmenty
        $postHtml = $this->replaceDirectivesWithRenderedFragments((string) $post->content);

        return view('pages.post', [
            'post'     => $post,
            'posts'    => Post::with('author')
                              ->where('is_published', true)
                              ->where('category', $post->category)
                              ->where('id', '!=', $post->id)
                              ->orderBy('date', 'desc')
                              ->limit(3)
                              ->get(),
            'postHtml' => $postHtml,
        ]);
    }

    /**
     * Zastępuje wszystkie wystąpienia LoadComponent#... zrenderowanym HTML-em.
     * 1) Próbuje uruchomić klasę komponentu (App\View\Components\Section\...).
     * 2) Jeśli brak klasy → include widoku website/section/... .
     */
    private function replaceDirectivesWithRenderedFragments(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        // Dekodujemy encje (&amp; → &), żeby query string był poprawny
        $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Wariant: content to dokładnie pojedynczy <p>LoadComponent#...</p>
        $singlePattern = '/^\s*<p[^>]*>\s*LoadComponent#(?P<name>[A-Za-z0-9\/\\\\\-\_\.]+)(?:\?(?P<query>[^\s<>"\']*))?\s*<\/p>\s*$/u';
        if (preg_match($singlePattern, $decoded, $mm)) {
            return $this->renderDirectiveToString($mm['name'] ?? '', $mm['query'] ?? '') ?? $html;
        }

        // Ogólna ścieżka: zamieniaj wszystkie wystąpienia (również wewnątrz akapitów)
        $pattern = '/(?:<p[^>]*>\s*)?'
                 . 'LoadComponent#(?P<name>[A-Za-z0-9\/\\\\\-\_\.]+)'
                 . '(?:\?(?P<query>[^\s<>"\']*))?'
                 . '(?:\s*<\/p>)?/u';

        $replaced = preg_replace_callback($pattern, function (array $m) {
            $name  = $m['name']  ?? '';
            $query = $m['query'] ?? '';
            return $this->renderDirectiveToString($name, $query) ?? $m[0];
        }, $decoded);

        return $replaced !== null ? $replaced : $html;
    }

    /**
     * Renderuje dyrektywę do stringa:
     * - jeśli istnieje klasowy komponent → instancjuje go i renderuje,
     * - inaczej próbuje zwykły widok section.* .
     */
    private function renderDirectiveToString(string $name, string $query): ?string
    {
        // Normalizacja nazwy i propsów
        $name = trim($name, "\\/ \t\n\r\0\x0B");
        $props = [];
        if ($query !== '') {
            parse_str($query, $props);
        }

        // 1) Spróbuj klasowego komponentu: App\View\Components\Section\{Path...}
        if ($fqcn = $this->guessComponentClassFqcn($name)) {
            $rendered = $this->renderClassComponent($fqcn, $props);
            if ($rendered !== null) {
                return $rendered;
            }
        }

        // 2) Fallback: zwykły widok section.* z aktywnego theme.
        if ($view = $this->guessWebsiteSectionView($name)) {
            return view($view, $props)->render();
        }

        // nic nie dopasowano
        return null;
    }

    /**
     * Buduje FQCN dla komponentu klasowego na podstawie nazwy dyrektywy.
     * Usuwa wiodący "Section" i konwertuje segmenty do StudlyCase.
     * Zwraca FQCN jeśli klasa istnieje i jest komponentem Blade, inaczej null.
     */
    private function guessComponentClassFqcn(string $name): ?string
    {
        $path = str_replace('\\', '/', $name);
        $segments = array_values(array_filter(explode('/', $path)));

        // Zdejmij początkowy "Section"
        if (!empty($segments) && strtolower($segments[0]) === 'section') {
            array_shift($segments);
        }
        if (empty($segments)) {
            return null;
        }

        $classPath = collect($segments)
            ->map(fn ($seg) => Str::studly(str_replace('-', ' ', $seg)))
            ->implode('\\');

        $fqcn = 'App\\View\\Components\\Section\\' . $classPath;

        return (class_exists($fqcn) && is_subclass_of($fqcn, BladeComponent::class))
            ? $fqcn
            : null;
    }

    /**
     * Renderuje klasowy komponent Blade do stringa.
     * - instancjuje komponent z przekazanymi propsami (konstruktor),
     * - wywołuje render(); obsługuje View, Closure i string,
     * - dokleja dane komponentu (publiczne właściwości) do widoku.
     */
    private function renderClassComponent(string $fqcn, array $props): ?string
    {
        // Utwórz instancję z wstrzykniętymi atrybutami (props)
        $component = app()->make($fqcn, $props);

        if (! $component instanceof BladeComponent) {
            return null;
        }

        // Uruchom logikę komponentu i pobierz „view”/„closure”/„string”
        $rendered = $component->render();

        // Dane komponentu (publiczne właściwości + ewent. data())
        // Używamy publicznej metody data() (dostępna w Illuminate\View\Component)
        $data = method_exists($component, 'data') ? $component->data() : get_object_vars($component);

        if ($rendered instanceof ViewContract) {
            // Dołącz dane komponentu do widoku
            return $rendered->with($data)->render();
        }

        if ($rendered instanceof \Closure) {
            // Z closure zwykle zwracany jest View po podaniu danych
            $view = $rendered($data);
            if ($view instanceof ViewContract) {
                return $view->with($data)->render();
            }
            // jeżeli closure zwróci string
            return (string) $view;
        }

        if (is_string($rendered)) {
            // Komponent zwrócił „goły” string
            return $rendered;
        }

        return null;
    }

    /**
     * Zwraca nazwę widoku w section.* jeśli istnieje (po zdjęciu "Section/").
     */
    private function guessWebsiteSectionView(string $name): ?string
    {
        $path = str_replace('\\', '/', $name);
        $segments = array_values(array_filter(explode('/', $path)));

        // Zdejmij początkowy "Section"
        if (!empty($segments) && strtolower($segments[0]) === 'section') {
            array_shift($segments);
        }
        if (empty($segments)) {
            return null;
        }

        $base = 'section';
        $kebab = array_map(fn ($s) => Str::kebab($s), $segments);
        $lower = array_map(fn ($s) => Str::lower($s), $segments);

        $candidates = [
            $base . '.' . implode('.', $kebab),
            $base . '.' . implode('.', $lower),
        ];

        // Ostatni segment „as-is” (np. Motivation.blade.php)
        $tmp = $kebab;
        $tmp[count($tmp) - 1] = $segments[count($segments) - 1];
        $candidates[] = $base . '.' . implode('.', $tmp);

        foreach ($candidates as $v) {
            if (view()->exists($v)) {
                return $v;
            }
        }

        return null;
    }
}
