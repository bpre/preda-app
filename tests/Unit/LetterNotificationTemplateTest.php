<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Letter;
use App\Models\LetterNotification;
use App\Models\LetterNotificationTemplate;
use App\Models\Matter;
use PHPUnit\Framework\TestCase;

class LetterNotificationTemplateTest extends TestCase
{
    public function test_it_renders_current_placeholders_for_notification(): void
    {
        $template = new LetterNotificationTemplate([
            'subject' => '{{pani_pana}} - {{nazwa_pisma}}',
            'message' => '{{data_doreczenia_pisma}} / {{nazwa_sprawy}}',
        ]);

        $matter = new Matter([
            'label' => 'ABC/123/2026',
        ]);

        $letter = new Letter([
            'label' => 'Wezwanie do zapłaty',
            'date' => '2026-03-28',
        ]);
        $letter->setRelation('matter', $matter);

        $contact = new Contact([
            'sex' => 'K',
        ]);

        $notification = new LetterNotification();
        $notification->setRelation('letter', $letter);
        $notification->setRelation('contact', $contact);

        $rendered = $template->renderForNotification($notification);

        $this->assertSame('Pani - Wezwanie do zapłaty', $rendered['subject']);
        $this->assertSame('28.03.2026 / ABC/123/2026', $rendered['message']);
    }

    public function test_it_supports_legacy_placeholder_aliases(): void
    {
        $template = new LetterNotificationTemplate([
            'subject' => '{{case_phrase}} {{letter_label}}',
            'message' => '{{letter_date}} {{matter_label}}',
        ]);

        $matter = new Matter([
            'label' => 'XYZ/77/2026',
        ]);

        $letter = new Letter([
            'label' => 'Odpowiedź na pozew',
            'date' => '2026-04-02',
        ]);
        $letter->setRelation('matter', $matter);

        $contact = new Contact([
            'sex' => 'M',
        ]);

        $notification = new LetterNotification();
        $notification->setRelation('letter', $letter);
        $notification->setRelation('contact', $contact);

        $rendered = $template->renderForNotification($notification);

        $this->assertSame('Pana Odpowiedź na pozew', $rendered['subject']);
        $this->assertSame('02.04.2026 XYZ/77/2026', $rendered['message']);
    }
}
