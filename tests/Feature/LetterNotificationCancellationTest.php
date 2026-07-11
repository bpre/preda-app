<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\ContactMatter;
use App\Models\Letter;
use App\Models\LetterNotification;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterNotificationCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabling_notifications_cancels_only_unsent_notifications(): void
    {
        $user = $this->createUser();

        $matter = Matter::create([
            'label' => 'Sprawa testowa',
            'lawyer_id' => $user->id,
            'is_matter' => true,
        ]);

        $contact = Contact::create([
            'type' => 'osoba',
            'category' => 'Kredytobiorca',
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.test',
        ]);

        $contactMatter = ContactMatter::create([
            'matter_id' => $matter->id,
            'contact_id' => $contact->id,
            'receives_notifications' => true,
        ]);

        $pendingNotification = $this->createNotificationForMatterContact($matter, $contact, 'Pismo 1');
        $queuedNotification = $this->createNotificationForMatterContact($matter, $contact, 'Pismo 2');
        $sentNotification = $this->createNotificationForMatterContact($matter, $contact, 'Pismo 3');
        $sendingNotification = $this->createNotificationForMatterContact($matter, $contact, 'Pismo 4');

        $queuedNotification->update(['status' => LetterNotification::STATUS_QUEUED]);
        $sentNotification->update(['status' => LetterNotification::STATUS_SENT]);
        $sendingNotification->update(['status' => LetterNotification::STATUS_SENDING]);

        $contactMatter->update([
            'receives_notifications' => false,
        ]);

        $this->assertSame(LetterNotification::STATUS_CANCELLED, $pendingNotification->fresh()->status);
        $this->assertSame(LetterNotification::STATUS_CANCELLED, $queuedNotification->fresh()->status);
        $this->assertSame(LetterNotification::STATUS_SENT, $sentNotification->fresh()->status);
        $this->assertSame(LetterNotification::STATUS_SENDING, $sendingNotification->fresh()->status);
    }

    public function test_deleting_contact_assignment_cancels_legacy_unsent_notifications(): void
    {
        $user = $this->createUser();

        $matter = Matter::create([
            'label' => 'Sprawa testowa 2',
            'lawyer_id' => $user->id,
            'is_matter' => true,
        ]);

        $contact = Contact::create([
            'type' => 'osoba',
            'category' => 'Kredytobiorca',
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'email' => 'anna@example.test',
        ]);

        $contactMatter = ContactMatter::create([
            'matter_id' => $matter->id,
            'contact_id' => $contact->id,
            'receives_notifications' => false,
        ]);

        $letter = Letter::create([
            'label' => 'Pismo legacy',
            'date' => '2026-03-28',
            'type' => 'in',
            'matter_id' => $matter->id,
        ]);

        $notification = LetterNotification::create([
            'letter_id' => $letter->id,
            'contact_id' => $contact->id,
            'status' => LetterNotification::STATUS_FAILED,
            'recipient_email' => $contact->email,
            'subject' => 'Test',
            'message' => 'Test',
        ]);

        $contactMatter->delete();

        $this->assertSame(LetterNotification::STATUS_CANCELLED, $notification->fresh()->status);
    }

    protected function createNotificationForMatterContact(Matter $matter, Contact $contact, string $letterLabel): LetterNotification
    {
        $letter = Letter::create([
            'label' => $letterLabel,
            'date' => '2026-03-28',
            'type' => 'in',
            'matter_id' => $matter->id,
        ]);

        return LetterNotification::query()
            ->where('letter_id', $letter->id)
            ->where('contact_id', $contact->id)
            ->firstOrFail();
    }

    protected function createUser(): User
    {
        return User::query()->create([
            'name' => 'Test User',
            'email' => fake()->unique()->safeEmail(),
            'phone' => '123456789',
            'password' => 'password',
        ]);
    }
}
