<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Website\Lead;
use App\Support\Website\LeadStatuses;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_gets_default_status_and_initial_history_entry(): void
    {
        $lead = $this->createLead();

        $this->assertSame(LeadStatuses::NEW, $lead->status);
        $this->assertNotNull($lead->status_changed_at);
        $this->assertDatabaseHas('website_lead_status_changes', [
            'lead_id' => $lead->id,
            'status' => LeadStatuses::NEW,
            'note' => 'Status początkowy.',
        ]);
        $this->assertSame(1, $lead->statusChanges()->count());
    }

    public function test_qualification_updates_current_status_and_preserves_history(): void
    {
        $user = User::factory()->create();
        $lead = $this->createLead();

        $lead->qualify(
            automatic: false,
            changedAt: '2026-07-10 12:30:00',
            userId: $user->id,
            note: 'Lead zakwalifikowany po rozmowie telefonicznej.',
        );

        $lead->refresh();

        $this->assertSame(LeadStatuses::QUALIFIED, $lead->status);
        $this->assertSame('2026-07-10 12:30:00', $lead->status_changed_at->format('Y-m-d H:i:s'));
        $this->assertSame(2, $lead->statusChanges()->count());
        $this->assertDatabaseHas('website_lead_status_changes', [
            'lead_id' => $lead->id,
            'status' => LeadStatuses::QUALIFIED,
            'changed_by' => $user->id,
            'note' => 'Lead zakwalifikowany po rozmowie telefonicznej.',
        ]);
    }

    public function test_rejection_stores_reason_note_and_history(): void
    {
        $user = User::factory()->create();
        $lead = $this->createLead();

        $lead->reject(
            reason: LeadStatuses::REASON_SPAM,
            changedAt: '2026-07-10 13:00:00',
            userId: $user->id,
            note: 'Powtarzalne zgłoszenie reklamowe.',
        );

        $lead->refresh();

        $this->assertSame(LeadStatuses::REJECTED, $lead->status);
        $this->assertSame(LeadStatuses::REASON_SPAM, $lead->rejection_reason);
        $this->assertSame('Powtarzalne zgłoszenie reklamowe.', $lead->rejection_note);
        $this->assertSame($user->id, $lead->rejected_by);
        $this->assertSame('2026-07-10 13:00:00', $lead->rejected_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('website_lead_status_changes', [
            'lead_id' => $lead->id,
            'status' => LeadStatuses::REJECTED,
            'changed_by' => $user->id,
            'note' => "Powód: Spam\nPowtarzalne zgłoszenie reklamowe.",
        ]);
    }

    private function createLead(): Lead
    {
        return Lead::create([
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.test',
            'phone' => '500 600 700',
            'message' => 'Zgłoszenie testowe.',
        ]);
    }
}
