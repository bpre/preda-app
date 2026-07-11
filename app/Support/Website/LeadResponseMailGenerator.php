<?php

namespace App\Support\Website;

use App\Models\Website\Lead;
use Illuminate\Support\Str;

class LeadResponseMailGenerator
{
    private const MEETING_URL = 'https://preda.info/konsultacje';

    private const FEMALE_FIRST_NAMES = [
        'agnieszka', 'aleksandra', 'alicja', 'amelia', 'anna', 'barbara', 'beata', 'bozena',
        'dagmara', 'danuta', 'dorota', 'edyta', 'ewa', 'gabriela', 'grazyna', 'hanna',
        'helena', 'iwona', 'izabela', 'jadwiga', 'janina', 'joanna', 'jolanta', 'julia',
        'justyna', 'kamila', 'karolina', 'katarzyna', 'kinga', 'krystyna', 'laura',
        'lidia', 'lucyna', 'magdalena', 'malgorzata', 'maria', 'marta', 'monika',
        'natalia', 'patrycja', 'paulina', 'renata', 'sylwia', 'teresa', 'urszula',
        'weronika', 'wiktoria', 'zofia',
    ];

    private const MALE_A_ENDING_FIRST_NAMES = [
        'barnaba', 'bonawentura', 'jarema', 'kuba', 'kosma',
    ];

    public static function generate(Lead $lead): array
    {
        return [
            'initial_subject' => self::initialSubject($lead),
            'initial_body' => self::initialBody($lead),
            'follow_up_subject' => self::followUpSubject($lead),
            'follow_up_body' => self::followUpBody($lead),
        ];
    }

    private static function initialSubject(Lead $lead): string
    {
        return 'Wstępna kwalifikacja sprawy - kredyt udzielony przez '.self::bankName($lead);
    }

    private static function followUpSubject(Lead $lead): string
    {
        return 'Przypomnienie: wstępna ocena umowy kredytowej ('.(self::clean($lead->bank) ?: 'bank').')';
    }

    private static function initialBody(Lead $lead): string
    {
        $gender = self::genderPhrases($lead);
        $contractYearRange = self::contractYearRange($lead);

        return self::joinHtmlParagraphs([
            'Dzień dobry,',
            'przeanalizowałem przesłane przez '.e($gender['accusative']).' informacje dotyczące '.e(self::creditDescription($lead)).' powiązanego z '.e(self::currencyPhrase($lead)).', udzielonego przez '.e(self::bankName($lead)).' (umowa z okresu '.e($contractYearRange).').',
            'Umowy tego banku zawierane w latach '.e($contractYearRange).' są nam znane. '.e($gender['possessive']).' umowa najprawdopodobniej odpowiada wzorcowi, w przypadku którego istnieją podstawy do dochodzenia roszczeń związanych z nieważnością umowy. <strong>Wstępnie kwalifikuję więc sprawę pozytywnie.</strong>',
            'Kolejnym krokiem jest bezpłatna konsultacja (w kancelarii lub on-line). Bezpośrednia rozmowa umożliwia omówienie wszystkich istotnych w sprawie kwestii (okoliczności zawierania umowy, statusu konsumenta, skutków unieważnienia umowy, potencjalnych korzyści z wygrania sprawy, ewentualnych ryzyk oraz dalszych kroków w sprawie).',
            'Termin konsultacji można zarezerwować tutaj:<br><a href="'.e(self::MEETING_URL).'">'.e(self::MEETING_URL).'</a>',
            'Przed spotkaniem proszę o przesłanie skanu umowy oraz ewentualnych aneksów (odpowiadając na tę wiadomość). Analiza dokumentów jest konieczna, aby potwierdzić, że umowa rzeczywiście odpowiada wskazanemu wzorcowi.',
            'Z wyrazami szacunku<br><br>Bartosz Pręda<br>adwokat',
        ]);
    }

    private static function followUpBody(Lead $lead): string
    {
        return self::joinHtmlParagraphs([
            'Dzień dobry,',
            'wracam do sprawy '.e(self::creditDescription($lead)).' powiązanego z '.e(self::currencyPhrase($lead)).', udzielonego przez '.e(self::bankName($lead)).' (umowa z okresu '.e(self::contractYearRange($lead)).').',
            'Wstępnie zakwalifikowałem sprawę pozytywnie, ponieważ podane informacje wskazują, że umowa może odpowiadać wzorcowi, w przypadku którego istnieją podstawy do dochodzenia roszczeń związanych z nieważnością umowy.',
            'Termin bezpłatnej konsultacji online można zarezerwować tutaj:<br><a href="'.e(self::MEETING_URL).'">'.e(self::MEETING_URL).'</a>',
            'Przed spotkaniem proszę o przesłanie skanu umowy oraz ewentualnych aneksów w odpowiedzi na tę wiadomość, najlepiej najpóźniej jeden dzień roboczy przed konsultacją. Pozwoli to potwierdzić wstępną ocenę i lepiej przygotować rozmowę.',
            'Z wyrazami szacunku<br><br>Bartosz Pręda<br>adwokat',
        ]);
    }

    private static function genderPhrases(Lead $lead): array
    {
        return match (self::gender($lead)) {
            'female' => [
                'accusative' => 'Panią',
                'from' => 'Pani',
                'possessive' => 'Pani',
                'would_like' => 'chciałaby Pani',
            ],
            'male' => [
                'accusative' => 'Pana',
                'from' => 'Pana',
                'possessive' => 'Pana',
                'would_like' => 'chciałby Pan',
            ],
            default => [
                'accusative' => 'Pana/Panią',
                'from' => 'Pana/Pani',
                'possessive' => 'Pana/Pani',
                'would_like' => 'chciałaby/chciałby Pani/Pan',
            ],
        };
    }

    private static function gender(Lead $lead): string
    {
        $firstName = self::firstName($lead);

        if (! $firstName) {
            return 'unknown';
        }

        if (in_array($firstName, self::FEMALE_FIRST_NAMES, true)) {
            return 'female';
        }

        if (in_array($firstName, self::MALE_A_ENDING_FIRST_NAMES, true)) {
            return 'male';
        }

        return str_ends_with($firstName, 'a') ? 'female' : 'male';
    }

    private static function firstName(Lead $lead): ?string
    {
        $name = Str::of((string) self::clean($lead->name))
            ->replaceMatches('/\s+/', ' ')
            ->trim();

        if ($name->isEmpty()) {
            return null;
        }

        $firstName = $name
            ->explode(' ')
            ->first(fn (string $part): bool => ! in_array(Str::lower($part), ['pan', 'pani', 'mecenas', 'adwokat'], true));

        if (! is_string($firstName) || trim($firstName) === '') {
            return null;
        }

        return Str::of($firstName)->lower()->ascii()->toString();
    }

    private static function creditDescription(Lead $lead): string
    {
        $status = self::lower($lead->credit_status);

        if (str_contains($status, 'spłacony')) {
            return 'spłaconego kredytu';
        }

        return 'kredytu';
    }

    private static function currencyPhrase(Lead $lead): string
    {
        return match (self::currency($lead)) {
            'CHF' => 'CHF',
            'EUR' => 'EUR',
            'USD' => 'USD',
            default => 'waluty obcej',
        };
    }

    private static function bankName(Lead $lead): string
    {
        return self::clean($lead->bank) ?: 'bank';
    }

    private static function contractYearRange(Lead $lead): string
    {
        $range = self::clean($lead->contract_year_range);

        return $range ?: 'wskazanym w formularzu';
    }

    private static function currency(Lead $lead): ?string
    {
        $currency = self::clean($lead->credit_currency);

        if (! $currency) {
            return null;
        }

        $currency = mb_strtoupper($currency);

        return in_array($currency, ['CHF', 'EUR', 'USD'], true) ? $currency : null;
    }

    private static function clean(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function lower(mixed $value): string
    {
        return mb_strtolower((string) self::clean($value));
    }

    private static function joinHtmlParagraphs(array $paragraphs): string
    {
        return collect($paragraphs)
            ->filter(fn (string $paragraph): bool => filled($paragraph))
            ->map(fn (string $paragraph): string => '<p>'.self::keepSingleLetterWordsTogether($paragraph).'</p>')
            ->implode('');
    }

    private static function keepSingleLetterWordsTogether(string $html): string
    {
        $parts = preg_split('/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return $html;
        }

        return implode('', array_map(
            fn (string $part): string => str_starts_with($part, '<')
                ? $part
                : preg_replace('/(^|[\s(])([aAiIoOuUwWzZ])\s+/u', '$1$2&nbsp;', $part),
            $parts,
        ));
    }
}
