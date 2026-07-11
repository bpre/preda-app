<?php

namespace Tests\Feature;

use App\Filament\Website\Resources\Offers\Pages\EditOffers;
use App\Mail\LetterNotificationMail;
use App\Models\LetterNotification;
use App\Models\User;
use App\Models\Website\Lead as WebsiteLead;
use App\Models\Website\Offer as WebsiteOffer;
use App\Notifications\OfferToClient;
use App\Services\LetterNotificationSender;
use App\Support\Website\LeadStatuses;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RealDataSideEffectSmokeTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_real_data_website_lead_status_change_updates_history_inside_transaction(): void
    {
        $user = $this->superAdmin();
        $this->actingAs($user);

        $lead = WebsiteLead::query()->firstOrFail();
        $originalHistoryCount = $lead->statusChanges()->count();
        $targetStatus = $this->differentLeadStatus($lead);

        $lead->changeStatus(
            $targetStatus,
            '2026-07-11 12:00:00',
            $user->id,
            'Real-data smoke test rolled back automatically.',
        );

        $lead->refresh();

        $this->assertSame($targetStatus, $lead->status);
        $this->assertSame('2026-07-11 12:00:00', $lead->status_changed_at->format('Y-m-d H:i:s'));
        $this->assertSame($originalHistoryCount + 1, $lead->statusChanges()->count());
        $this->assertDatabaseHas('website_lead_status_changes', [
            'lead_id' => $lead->id,
            'status' => $targetStatus,
            'changed_by' => $user->id,
            'note' => 'Real-data smoke test rolled back automatically.',
        ]);
    }

    public function test_real_data_letter_notification_mail_renders_with_existing_attachment(): void
    {
        [$notification, $attachmentPath] = $this->firstNotificationWithExistingLetterFile();

        $notification->forceFill([
            'subject' => $notification->subject ?: 'Powiadomienie o piśmie',
            'message' => "Real-data smoke test message.\n\nThis change is rolled back automatically.",
            'with_attachments' => true,
            'selected_attachments' => [$attachmentPath],
        ])->save();

        $mail = (new LetterNotificationMail($notification->fresh(['letter', 'preparedBy'])))->build();
        $html = $mail->render();

        $this->assertSame($notification->subject, $mail->subject);
        $this->assertStringContainsString('Real-data smoke test message.', $html);
        $this->assertTrue(Storage::disk('local')->exists($attachmentPath));
    }

    public function test_real_data_letter_notification_sender_marks_queued_notification_as_sent_inside_transaction(): void
    {
        Mail::fake();

        $user = $this->superAdmin();
        [$notification, $attachmentPath] = $this->firstNotificationWithExistingLetterFile();

        $notification->forceFill([
            'status' => LetterNotification::STATUS_QUEUED,
            'recipient_email' => 'real-data-smoke@example.test',
            'subject' => 'Powiadomienie o piśmie',
            'message' => "Real-data sender smoke test.\n\nThis change is rolled back automatically.",
            'prepared_by' => $user->id,
            'sent_at' => null,
            'sent_by' => null,
            'error_message' => null,
            'with_attachments' => true,
            'selected_attachments' => [$attachmentPath],
        ])->save();

        $sent = app(LetterNotificationSender::class)->send($notification->fresh(), $user->id);

        $this->assertTrue($sent);

        $notification->refresh();

        $this->assertSame(LetterNotification::STATUS_SENT, $notification->status);
        $this->assertSame($user->id, $notification->sent_by);
        $this->assertNotNull($notification->sent_at);
        $this->assertNull($notification->error_message);

        Mail::assertSent(LetterNotificationMail::class);
    }

    public function test_real_data_offer_notification_can_be_prepared_with_pdf_attachment(): void
    {
        $offer = WebsiteOffer::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->firstOrFail();

        $path = "offers/{$offer->id}/offer.pdf";
        $createdTemporaryPdf = ! Storage::disk('local')->exists($path);

        if ($createdTemporaryPdf) {
            Storage::disk('local')->put($path, "%PDF-1.4\n% real-data smoke test\n");
        }

        try {
            $mail = (new OfferToClient($offer))->toMail((object) []);

            $this->assertSame('Oferta', $mail->subject);
            $this->assertNotEmpty($mail->introLines);
            $this->assertTrue(Storage::disk('local')->exists($path));
        } finally {
            if ($createdTemporaryPdf) {
                Storage::disk('local')->delete($path);
            }
        }
    }

    public function test_real_data_offer_edit_page_send_offer_generates_pdf_and_marks_offer_as_sent_inside_transaction(): void
    {
        Filament::setCurrentPanel('crm');
        Notification::fake();

        $this->actingAs($this->superAdmin());

        $offer = WebsiteOffer::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->firstOrFail();

        $offer->forceFill([
            'sex' => 'male',
            'name' => $offer->name ?: 'Real Data Smoke Test',
            'email' => 'real-data-smoke@example.test',
            'phone' => $offer->phone ?: '000000000',
            'bank' => $offer->bank ?: 'Test Bank',
            'year' => $offer->year ?: '2020',
            'amount' => $offer->amount ?: 200000,
            'start_wstepna' => $offer->start_wstepna ?: 999,
            'start_premia' => $offer->start_premia ?: 25000,
            'start_procent_limit' => $offer->start_procent_limit ?: 35,
            'start_rozprawa' => $offer->start_rozprawa ?: 0,
            'start_razem_max' => $offer->start_razem_max ?: 25999,
            'max_wstepna' => $offer->max_wstepna ?: 12000,
            'max_druga_instancja' => $offer->max_druga_instancja ?: 6000,
            'max_rozprawa' => $offer->max_rozprawa ?: 500,
            'max_rozprawy_limit' => $offer->max_rozprawy_limit ?: 1999,
            'max_razem_max' => $offer->max_razem_max ?: 19999,
            'offer_confirmed_at' => now(),
            'offer_sent_at' => null,
        ])->save();

        $path = "offers/{$offer->id}/offer.pdf";
        $createdTemporaryPdf = ! Storage::disk('local')->exists($path);

        try {
            Livewire::test(EditOffers::class, ['record' => $offer->getRouteKey()])
                ->call('sendOffer')
                ->assertHasNoErrors();

            $offer->refresh();

            $this->assertNotNull($offer->offer_sent_at);
            $this->assertTrue(Storage::disk('local')->exists($path));

            Notification::assertSentOnDemand(OfferToClient::class);
        } finally {
            if ($createdTemporaryPdf) {
                Storage::disk('local')->delete($path);
            }
        }
    }

    private function differentLeadStatus(WebsiteLead $lead): string
    {
        $currentStatus = LeadStatuses::normalize($lead->status);

        foreach (array_keys(LeadStatuses::options()) as $status) {
            if ($status !== $currentStatus) {
                return $status;
            }
        }

        return LeadStatuses::NEW;
    }

    private function firstNotificationWithExistingLetterFile(): array
    {
        $notifications = LetterNotification::query()
            ->with('letter')
            ->whereHas('letter', fn ($query) => $query
                ->whereNotNull('files')
                ->whereRaw('JSON_LENGTH(files) > 0'))
            ->cursor();

        foreach ($notifications as $notification) {
            foreach ((array) $notification->letter?->files as $path) {
                if (is_string($path) && Storage::disk('local')->exists($path)) {
                    return [$notification, $path];
                }
            }
        }

        $this->fail('Missing a letter notification with an existing local attachment in the imported real data.');
    }

    private function superAdmin(): User
    {
        $role = Role::query()
            ->where('name', config('filament-shield.super_admin.name'))
            ->where('guard_name', 'web')
            ->firstOrFail();

        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereKey($role->id))
            ->firstOrFail();
    }
}
