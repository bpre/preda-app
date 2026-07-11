<?php

namespace Tests\Feature;

use App\Models\Website\Lead;
use App\Notifications\LeadGeneratedMessage;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeadGeneratedMessageTest extends TestCase
{
    public function test_it_sends_generated_message_with_currently_edited_subject_and_body(): void
    {
        Notification::fake();

        $lead = new Lead([
            'email' => 'klient@example.test',
        ]);

        Notification::route('mail', $lead->email)
            ->notify(new LeadGeneratedMessage(
                'Edytowany temat',
                '<p>Edytowana treść</p><p><strong>Drugi akapit</strong></p>',
            ));

        Notification::assertSentOnDemand(
            LeadGeneratedMessage::class,
            fn (LeadGeneratedMessage $notification, array $channels, object $notifiable): bool => $channels === ['mail']
                && $notifiable->routes['mail'] === 'klient@example.test'
                && $notification->subject === 'Edytowany temat'
                && $notification->body === '<p>Edytowana treść</p><p><strong>Drugi akapit</strong></p>',
        );
    }
}
