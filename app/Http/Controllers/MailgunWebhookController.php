<?php

namespace App\Http\Controllers;

use App\Models\MailgunEvent;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MailgunWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        abort_unless($this->hasValidSignature($request), 401);

        $eventData = $payload['event-data'] ?? null;

        abort_unless(is_array($eventData), 422, 'Missing Mailgun event data.');

        $eventId = $this->nullableString(data_get($eventData, 'id'));
        $payloadHash = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $existing = MailgunEvent::query()
            ->when($eventId, fn ($query) => $query->where('mailgun_event_id', $eventId))
            ->when(! $eventId, fn ($query) => $query->where('payload_hash', $payloadHash))
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'duplicate',
                'id' => $existing->getKey(),
            ]);
        }

        try {
            $event = MailgunEvent::query()->create([
                'mailgun_event_id' => $eventId,
                'payload_hash' => $payloadHash,
                'event' => $this->nullableString(data_get($eventData, 'event')) ?? 'unknown',
                'domain' => $this->nullableString(config('services.mailgun.domain')),
                'recipient_email' => $this->nullableString(data_get($eventData, 'recipient')),
                'sender_email' => $this->extractEmailAddress(
                    $this->nullableString(data_get($eventData, 'message.headers.from'))
                        ?? $this->nullableString(data_get($eventData, 'envelope.sender')),
                ),
                'subject' => $this->nullableString(data_get($eventData, 'message.headers.subject')),
                'message_id' => $this->nullableString(data_get($eventData, 'message.headers.message-id')),
                'mailgun_message_id' => $this->nullableString(data_get($eventData, 'message.id')),
                'url' => $this->nullableString(data_get($eventData, 'url')),
                'ip_address' => $this->nullableString(data_get($eventData, 'ip')),
                'user_agent' => $this->nullableString(data_get($eventData, 'client-info.user-agent')),
                'client_info' => $this->arrayOrNull(data_get($eventData, 'client-info')),
                'tags' => $this->arrayOrNull(data_get($eventData, 'tags')),
                'user_variables' => $this->arrayOrNull(data_get($eventData, 'user-variables')),
                'crm_client_message_id' => $this->uuidFromUserVariables($eventData, 'crm_client_message_id'),
                'matter_id' => $this->uuidFromUserVariables($eventData, 'matter_id'),
                'website_lead_id' => $this->integerFromUserVariables($eventData, 'website_lead_id'),
                'letter_notification_id' => $this->uuidFromUserVariables($eventData, 'letter_notification_id'),
                'letter_id' => $this->uuidFromUserVariables($eventData, 'letter_id'),
                'contact_id' => $this->uuidFromUserVariables($eventData, 'contact_id'),
                'payload' => $payload,
                'occurred_at' => $this->occurredAt($eventData),
            ]);
        } catch (QueryException) {
            $event = MailgunEvent::query()
                ->where('payload_hash', $payloadHash)
                ->when($eventId, fn ($query) => $query->orWhere('mailgun_event_id', $eventId))
                ->first();

            return response()->json([
                'status' => 'duplicate',
                'id' => $event?->getKey(),
            ]);
        }

        return response()->json([
            'status' => 'stored',
            'id' => $event->getKey(),
        ], 201);
    }

    private function hasValidSignature(Request $request): bool
    {
        $signingKey = config('services.mailgun.webhook_signing_key');

        abort_unless(filled($signingKey), 503, 'Mailgun webhook signing key is not configured.');

        $timestamp = $request->input('signature.timestamp');
        $token = $request->input('signature.token');
        $signature = $request->input('signature.signature');

        if (! is_string($timestamp) || ! is_string($token) || ! is_string($signature)) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.$token, (string) $signingKey);

        return hash_equals($expected, $signature);
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function arrayOrNull(mixed $value): ?array
    {
        return is_array($value) ? $value : null;
    }

    private function extractEmailAddress(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (preg_match('/<([^>]+)>/', $value, $matches) === 1) {
            return strtolower(trim($matches[1]));
        }

        return strtolower($value);
    }

    private function uuidFromUserVariables(array $eventData, string $key): ?string
    {
        $value = $this->nullableString(data_get($eventData, "user-variables.{$key}"));

        return $value && Str::isUuid($value) ? $value : null;
    }

    private function integerFromUserVariables(array $eventData, string $key): ?int
    {
        $value = data_get($eventData, "user-variables.{$key}");

        return is_numeric($value) ? (int) $value : null;
    }

    private function occurredAt(array $eventData): ?Carbon
    {
        $timestamp = data_get($eventData, 'timestamp');

        if (! is_numeric($timestamp)) {
            return null;
        }

        return Carbon::createFromTimestampUTC((float) $timestamp);
    }
}
