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
        $this->assertDatabaseHas('lead_status_changes', [
            'lead_id' => $lead->id,
            'status' => LeadStatuses::NEW,
            'note' => 'Status początkowy.',
        ]);
        $this->assertSame(1, $lead->statusChanges()->count());
    }

    public function test_status_change_updates_current_status_and_preserves_history(): void
    {
        $user = User::factory()->create();
        $lead = $this->createLead();

        $lead->changeStatus(
            'Wysłano analizę umowy',
            '2026-07-10 12:30:00',
            $user->id,
            'Wysłano analizę po weryfikacji dokumentów.',
        );

        $lead->refresh();

        $this->assertSame('Wysłano analizę umowy', $lead->status);
        $this->assertSame('2026-07-10 12:30:00', $lead->status_changed_at->format('Y-m-d H:i:s'));
        $this->assertSame(2, $lead->statusChanges()->count());
        $this->assertDatabaseHas('lead_status_changes', [
            'lead_id' => $lead->id,
            'status' => 'Wysłano analizę umowy',
            'changed_by' => $user->id,
            'note' => 'Wysłano analizę po weryfikacji dokumentów.',
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
