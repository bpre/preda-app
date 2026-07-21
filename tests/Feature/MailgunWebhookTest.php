<?php

namespace Tests\Feature;

use App\Models\MailgunEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MailgunWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_signed_mailgun_event(): void
    {
        config([
            'services.mailgun.domain' => 'mail.preda.info',
            'services.mailgun.webhook_signing_key' => 'webhook-secret',
        ]);

        $crmClientMessageId = (string) Str::uuid();
        $matterId = (string) Str::uuid();
        $letterNotificationId = (string) Str::uuid();
        $letterId = (string) Str::uuid();
        $contactId = (string) Str::uuid();

        $payload = $this->signedPayload([
            'id' => 'event-1',
            'event' => 'opened',
            'timestamp' => 1784628366.5,
            'recipient' => 'bartosz.preda@gmail.com',
            'ip' => '203.0.113.10',
            'url' => 'https://preda.info/?mailgun-test=1',
            'message' => [
                'id' => 'mailgun-message-1',
                'headers' => [
                    'message-id' => '<message-1@mail.preda.info>',
                    'from' => 'PRĘDA Kancelaria Adwokacka <kancelaria@preda.info>',
                    'subject' => 'Test Mailgun tracking z preda-app',
                ],
            ],
            'client-info' => [
                'client-name' => 'Gmail',
                'user-agent' => 'Mozilla/5.0 Test Browser',
            ],
            'tags' => ['crm-follow-up'],
            'user-variables' => [
                'crm_client_message_id' => $crmClientMessageId,
                'matter_id' => $matterId,
                'website_lead_id' => '123',
                'letter_notification_id' => $letterNotificationId,
                'letter_id' => $letterId,
                'contact_id' => $contactId,
            ],
        ]);

        $response = $this->postJson(route('webhooks.mailgun.events'), $payload);

        $response
            ->assertCreated()
            ->assertJson(['status' => 'stored']);

        $event = MailgunEvent::query()->firstOrFail();

        $this->assertSame('event-1', $event->mailgun_event_id);
        $this->assertSame('opened', $event->event);
        $this->assertSame('mail.preda.info', $event->domain);
        $this->assertSame('bartosz.preda@gmail.com', $event->recipient_email);
        $this->assertSame('kancelaria@preda.info', $event->sender_email);
        $this->assertSame('Test Mailgun tracking z preda-app', $event->subject);
        $this->assertSame('<message-1@mail.preda.info>', $event->message_id);
        $this->assertSame('mailgun-message-1', $event->mailgun_message_id);
        $this->assertSame('https://preda.info/?mailgun-test=1', $event->url);
        $this->assertSame('203.0.113.10', $event->ip_address);
        $this->assertSame('Mozilla/5.0 Test Browser', $event->user_agent);
        $this->assertSame(['client-name' => 'Gmail', 'user-agent' => 'Mozilla/5.0 Test Browser'], $event->client_info);
        $this->assertSame(['crm-follow-up'], $event->tags);
        $this->assertSame($crmClientMessageId, $event->crm_client_message_id);
        $this->assertSame($matterId, $event->matter_id);
        $this->assertSame(123, $event->website_lead_id);
        $this->assertSame($letterNotificationId, $event->letter_notification_id);
        $this->assertSame($letterId, $event->letter_id);
        $this->assertSame($contactId, $event->contact_id);
    }

    public function test_it_rejects_invalid_signature(): void
    {
        config([
            'services.mailgun.webhook_signing_key' => 'webhook-secret',
        ]);

        $payload = $this->signedPayload([
            'id' => 'event-1',
            'event' => 'delivered',
        ]);

        $payload['signature']['signature'] = 'invalid';

        $this->postJson(route('webhooks.mailgun.events'), $payload)
            ->assertUnauthorized();

        $this->assertDatabaseCount(MailgunEvent::class, 0);
    }

    public function test_it_handles_duplicate_events_idempotently(): void
    {
        config([
            'services.mailgun.domain' => 'mail.preda.info',
            'services.mailgun.webhook_signing_key' => 'webhook-secret',
        ]);

        $payload = $this->signedPayload([
            'id' => 'event-duplicate',
            'event' => 'clicked',
            'recipient' => 'bartosz.preda@gmail.com',
        ]);

        $this->postJson(route('webhooks.mailgun.events'), $payload)
            ->assertCreated()
            ->assertJson(['status' => 'stored']);

        $this->postJson(route('webhooks.mailgun.events'), $payload)
            ->assertOk()
            ->assertJson(['status' => 'duplicate']);

        $this->assertDatabaseCount(MailgunEvent::class, 1);
    }

    /**
     * @param  array<string, mixed>  $eventData
     * @return array<string, mixed>
     */
    private function signedPayload(array $eventData): array
    {
        $timestamp = '1784628000';
        $token = 'test-token';

        return [
            'signature' => [
                'timestamp' => $timestamp,
                'token' => $token,
                'signature' => hash_hmac('sha256', $timestamp.$token, 'webhook-secret'),
            ],
            'event-data' => $eventData,
        ];
    }
}
