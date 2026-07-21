<?php

namespace Tests\Feature;

use App\Mail\LetterNotificationMail;
use App\Models\Letter;
use App\Models\LetterNotification;
use App\Models\Website\Lead;
use App\Notifications\LeadGeneratedMessage;
use App\Notifications\NewLeadToClient;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
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

    public function test_it_uses_plain_human_mail_views_instead_of_laravel_markdown_layout(): void
    {
        $message = (new LeadGeneratedMessage(
            'Edytowany temat',
            '<p>Edytowana treść</p><p><strong>Drugi akapit</strong></p>',
        ))->toMail((object) []);

        $this->assertNull($message->markdown);
        $this->assertSame([
            'html' => 'emails.lead-generated-message',
            'text' => 'emails.lead-generated-message-text',
        ], $message->view);

        $html = (string) $message->render();

        $this->assertStringContainsString('Edytowana treść', $html);
        $this->assertStringContainsString('Drugi akapit', $html);
        $this->assertStringNotContainsString('<x-mail::message>', $html);
        $this->assertStringNotContainsString('class="wrapper"', $html);
    }

    public function test_it_sets_reply_to_when_sender_details_are_provided(): void
    {
        $message = (new LeadGeneratedMessage(
            subject: 'Edytowany temat',
            body: '<p>Edytowana treść</p>',
            replyToEmail: 'jan.prawnik@example.test',
            replyToName: 'Jan Prawnik',
        ))->toMail((object) []);

        $this->assertSame([
            ['jan.prawnik@example.test', 'Jan Prawnik'],
        ], $message->replyTo);
    }

    public function test_it_adds_mailgun_tags_and_metadata_when_provided(): void
    {
        $message = (new LeadGeneratedMessage(
            subject: 'Edytowany temat',
            body: '<p>Edytowana treść</p>',
            mailTags: [
                'crm-client-message',
                'crm-action-send_offer',
            ],
            mailMetadata: [
                'crm_client_message_id' => 'message-uuid',
                'matter_id' => 'matter-uuid',
                'crm_action' => 'send_offer',
            ],
        ))->toMail((object) []);

        $this->assertSame([
            'crm-client-message',
            'crm-action-send_offer',
        ], $message->tags);

        $this->assertSame([
            'crm_client_message_id' => 'message-uuid',
            'matter_id' => 'matter-uuid',
            'crm_action' => 'send_offer',
        ], $message->metadata);
    }

    public function test_new_lead_confirmation_adds_mailgun_metadata(): void
    {
        $matterId = (string) Str::uuid();

        $lead = new Lead([
            'email' => 'klient@example.test',
            'potential_matter_id' => $matterId,
        ]);
        $lead->id = 123;

        $message = (new NewLeadToClient($lead))->toMail((object) []);

        $this->assertSame(['website-lead-confirmation'], $message->tags);
        $this->assertSame([
            'website_lead_id' => '123',
            'recipient_email' => 'klient@example.test',
            'matter_id' => $matterId,
        ], $message->metadata);
    }

    public function test_letter_notification_mail_adds_mailgun_metadata(): void
    {
        $letterId = (string) Str::uuid();
        $matterId = (string) Str::uuid();
        $contactId = (string) Str::uuid();
        $notificationId = (string) Str::uuid();

        $letter = new Letter([
            'matter_id' => $matterId,
            'files' => [],
        ]);
        $letter->id = $letterId;

        $notification = new LetterNotification([
            'contact_id' => $contactId,
            'recipient_email' => 'klient@example.test',
            'subject' => 'Powiadomienie o piśmie',
            'message' => 'Treść wiadomości.',
        ]);
        $notification->id = $notificationId;
        $notification->setRelation('letter', $letter);

        $mail = (new LetterNotificationMail($notification))->build();

        $this->assertTrue($mail->hasTag('letter-notification'));
        $this->assertTrue($mail->hasMetadata('letter_notification_id', $notificationId));
        $this->assertTrue($mail->hasMetadata('letter_id', $letterId));
        $this->assertTrue($mail->hasMetadata('matter_id', $matterId));
        $this->assertTrue($mail->hasMetadata('contact_id', $contactId));
        $this->assertTrue($mail->hasMetadata('recipient_email', 'klient@example.test'));
    }
}
