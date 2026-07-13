<?php

namespace Tests\Feature;

use App\Models\Website\Lead;
use App\Support\Website\PostalCodeLookup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeadPostalCodeLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_postal_region_is_filled_when_lead_is_saved(): void
    {
        $lead = Lead::create([
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.test',
            'phone' => '500 600 700',
            'postal_code' => '67200',
            'message' => 'Zgłoszenie testowe.',
        ]);

        $this->assertSame('67-200', $lead->postal_code);
        $this->assertSame('dolnośląskie', $lead->postal_voivodeship);
        $this->assertSame('głogowski', $lead->postal_county);
    }

    public function test_missing_postal_region_can_be_backfilled_for_existing_lead(): void
    {
        $lead = Lead::create([
            'name' => 'Anna Nowak',
            'email' => 'anna@example.test',
            'phone' => '700 800 900',
            'postal_code' => '59-100',
            'message' => 'Zgłoszenie testowe.',
        ]);

        DB::table('website_leads')
            ->where('id', $lead->getKey())
            ->update([
                'postal_voivodeship' => null,
                'postal_county' => null,
            ]);

        app(PostalCodeLookup::class)->fillMissingLeadRegion($lead->refresh());

        $this->assertDatabaseHas('website_leads', [
            'id' => $lead->getKey(),
            'postal_code' => '59-100',
            'postal_voivodeship' => 'dolnośląskie',
            'postal_county' => 'polkowicki',
        ]);
    }
}
