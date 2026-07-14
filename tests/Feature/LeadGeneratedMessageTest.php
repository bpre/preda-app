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
}
