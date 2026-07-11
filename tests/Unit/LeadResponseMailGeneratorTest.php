<?php

namespace Tests\Unit;

use App\Models\Website\Lead;
use App\Support\Website\LeadResponseMailGenerator;
use PHPUnit\Framework\TestCase;

class LeadResponseMailGeneratorTest extends TestCase
{
    public function test_it_generates_requested_initial_email_for_paid_off_chf_lead(): void
    {
        $lead = new Lead([
            'name' => 'Anna Kowalska',
            'bank' => 'Dominet Bank',
            'contract_year_range' => '2007-2009',
            'credit_currency' => 'CHF',
            'credit_status' => 'kredyt spłacony',
        ]);

        $message = LeadResponseMailGenerator::generate($lead);

        $this->assertSame('Wstępna kwalifikacja sprawy - kredyt udzielony przez Dominet Bank', $message['initial_subject']);
        $this->assertSame('Przypomnienie: wstępna ocena umowy kredytowej (Dominet Bank)', $message['follow_up_subject']);
        $this->assertStringContainsString('przeanalizowałem przesłane przez Panią informacje dotyczące spłaconego kredytu powiązanego z&nbsp;CHF', $message['initial_body']);
        $this->assertStringContainsString('udzielonego przez Dominet Bank (umowa z&nbsp;okresu 2007-2009)', $message['initial_body']);
        $this->assertStringContainsString('Umowy tego banku zawierane w&nbsp;latach 2007-2009 są nam znane.', $message['initial_body']);
        $this->assertStringContainsString('Pani umowa najprawdopodobniej odpowiada wzorcowi', $message['initial_body']);
        $this->assertStringContainsString('z&nbsp;nieważnością umowy', $message['initial_body']);
        $this->assertStringContainsString('z&nbsp;wygrania sprawy', $message['initial_body']);
        $this->assertStringContainsString('w&nbsp;sprawie', $message['initial_body']);
        $this->assertStringContainsString('w&nbsp;kancelarii lub on-line', $message['initial_body']);
        $this->assertStringContainsString('<strong>Wstępnie kwalifikuję więc sprawę pozytywnie.</strong>', $message['initial_body']);
        $this->assertStringContainsString('https://preda.info/konsultacje', $message['initial_body']);
        $this->assertStringContainsString('proszę o&nbsp;przesłanie skanu umowy', $message['initial_body']);
        $this->assertStringContainsString('ewentualnych aneksów (odpowiadając na tę wiadomość).', $message['initial_body']);
    }

    public function test_it_uses_male_form_and_neutral_credit_description_for_unpaid_credit(): void
    {
        $lead = new Lead([
            'name' => 'Jan Nowak',
            'bank' => 'Bank Testowy',
            'contract_year_range' => 'po 2012',
            'credit_currency' => 'EUR',
            'credit_status' => 'nadal spłacam',
        ]);

        $message = LeadResponseMailGenerator::generate($lead);

        $this->assertStringContainsString('przesłane przez Pana informacje dotyczące kredytu powiązanego z&nbsp;EUR', $message['initial_body']);
        $this->assertStringNotContainsString('spłacanego kredytu', $message['initial_body']);
        $this->assertStringContainsString('Pana umowa najprawdopodobniej odpowiada wzorcowi', $message['initial_body']);
        $this->assertStringContainsString('Termin konsultacji można zarezerwować tutaj', $message['initial_body']);
    }

    public function test_it_treats_common_male_names_ending_with_a_as_male(): void
    {
        $lead = new Lead([
            'name' => 'Kuba Nowak',
            'bank' => 'mBank',
            'credit_currency' => 'CHF',
            'credit_status' => 'kredyt spłacony',
        ]);

        $message = LeadResponseMailGenerator::generate($lead);

        $this->assertStringContainsString('Pana umowa najprawdopodobniej odpowiada wzorcowi', $message['initial_body']);
    }
}
