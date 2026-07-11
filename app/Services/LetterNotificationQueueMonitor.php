<?php

namespace App\Services;

use App\Jobs\LetterNotificationQueueHeartbeatJob;
use App\Jobs\SendLetterNotificationJob;
use App\Models\LetterNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class LetterNotificationQueueMonitor
{
    public const HEARTBEAT_CACHE_KEY = 'letter-notifications:worker-heartbeat-at';

    public const HEARTBEAT_REQUESTED_CACHE_KEY = 'letter-notifications:worker-heartbeat-requested-at';

    protected const HEARTBEAT_FRESH_FOR_SECONDS = 60;

    protected const HEARTBEAT_REQUEST_COOLDOWN_SECONDS = 15;

    protected const STALE_SENDING_AFTER_SECONDS = 90;

    public function dispatchHeartbeatIfNeeded(): void
    {
        $lastHeartbeatAt = $this->getLastHeartbeatAt();
        $lastHeartbeatRequestedAt = $this->getHeartbeatRequestedAt();

        if ($lastHeartbeatAt instanceof Carbon
            && $lastHeartbeatAt->greaterThanOrEqualTo(now()->subSeconds((int) floor(self::HEARTBEAT_FRESH_FOR_SECONDS / 2)))) {
            return;
        }

        if ($lastHeartbeatRequestedAt instanceof Carbon
            && $lastHeartbeatRequestedAt->greaterThanOrEqualTo(now()->subSeconds(self::HEARTBEAT_REQUEST_COOLDOWN_SECONDS))) {
            return;
        }

        Cache::put(self::HEARTBEAT_REQUESTED_CACHE_KEY, now()->toIso8601String(), now()->addMinutes(2));

        LetterNotificationQueueHeartbeatJob::dispatch();
    }

    public function touchHeartbeat(): void
    {
        Cache::put(self::HEARTBEAT_CACHE_KEY, now()->toIso8601String(), now()->addMinutes(10));
        Cache::forget(self::HEARTBEAT_REQUESTED_CACHE_KEY);
    }

    public function getStatusData(): array
    {
        $lastHeartbeatAt = $this->getLastHeartbeatAt();
        $lastHeartbeatRequestedAt = $this->getHeartbeatRequestedAt();
        $outstandingJobs = $this->getOutstandingJobsData();
        $staleSendingCount = $this->getStaleSendingCount();
        $hasFreshHeartbeat = $lastHeartbeatAt instanceof Carbon
            && $lastHeartbeatAt->greaterThanOrEqualTo(now()->subSeconds(self::HEARTBEAT_FRESH_FOR_SECONDS));
        $isCheckingHeartbeat = $lastHeartbeatRequestedAt instanceof Carbon
            && $lastHeartbeatRequestedAt->greaterThanOrEqualTo(now()->subSeconds(self::HEARTBEAT_REQUEST_COOLDOWN_SECONDS));

        if ($hasFreshHeartbeat) {
            return [
                'label' => 'Worker działa',
                'color' => 'success',
                'icon' => 'heroicon-m-check-circle',
                'description' => $this->buildHealthyDescription(
                    lastHeartbeatAt: $lastHeartbeatAt,
                    outstandingJobsCount: $outstandingJobs['count'],
                ),
                'last_heartbeat_label' => $this->formatTimeAgo($lastHeartbeatAt),
                'outstanding_jobs_count' => $outstandingJobs['count'],
                'stale_sending_count' => $staleSendingCount,
            ];
        }

        if ($outstandingJobs['real_jobs_count'] > 0 || $staleSendingCount > 0) {
            return [
                'label' => 'Worker prawdopodobnie nie działa',
                'color' => 'danger',
                'icon' => 'heroicon-m-exclamation-triangle',
                'description' => $this->buildUnhealthyDescription(
                    outstandingJobsCount: $outstandingJobs['real_jobs_count'],
                    oldestOutstandingJobAgeInSeconds: $outstandingJobs['oldest_age_in_seconds'],
                    staleSendingCount: $staleSendingCount,
                ),
                'last_heartbeat_label' => $lastHeartbeatAt ? $this->formatTimeAgo($lastHeartbeatAt) : 'brak',
                'outstanding_jobs_count' => $outstandingJobs['count'],
                'stale_sending_count' => $staleSendingCount,
            ];
        }

        return [
            'label' => $isCheckingHeartbeat ? 'Sprawdzam połączenie z workerem' : 'Oczekiwanie na potwierdzenie od workera',
            'color' => 'warning',
            'icon' => 'heroicon-m-clock',
            'description' => $isCheckingHeartbeat
                ? 'Wysłano lekki heartbeat na kolejkę `letter-notifications`. Jeśli worker działa, status za chwilę zmieni się na zielony.'
                : ($lastHeartbeatAt instanceof Carbon
                    ? 'Ostatni sygnał od workera: ' . $this->formatTimeAgo($lastHeartbeatAt) . '. Brak zaległych wysyłek.'
                    : 'Brak jeszcze sygnału od workera. Jeśli proces działa, status powinien za chwilę zmienić się na zielony.'),
            'last_heartbeat_label' => $lastHeartbeatAt ? $this->formatTimeAgo($lastHeartbeatAt) : 'brak',
            'outstanding_jobs_count' => $outstandingJobs['count'],
            'stale_sending_count' => $staleSendingCount,
        ];
    }

    protected function getLastHeartbeatAt(): ?Carbon
    {
        $value = Cache::get(self::HEARTBEAT_CACHE_KEY);

        return $this->parseDateTimeValue($value);
    }

    protected function getHeartbeatRequestedAt(): ?Carbon
    {
        $value = Cache::get(self::HEARTBEAT_REQUESTED_CACHE_KEY);

        return $this->parseDateTimeValue($value);
    }

    protected function parseDateTimeValue(mixed $value): ?Carbon
    {

        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable $e) {
            return null;
        }
    }

    protected function getOutstandingJobsData(): array
    {
        try {
            $jobs = DB::table('jobs')
                ->where('queue', SendLetterNotificationJob::QUEUE);

            $count = (int) $jobs->count();

            if ($count === 0) {
                return [
                    'count' => 0,
                    'real_jobs_count' => 0,
                    'oldest_age_in_seconds' => null,
                ];
            }

            $heartbeatJobsCount = (int) DB::table('jobs')
                ->where('queue', SendLetterNotificationJob::QUEUE)
                ->where('payload', 'like', '%LetterNotificationQueueHeartbeatJob%')
                ->count();

            $oldestCreatedAt = DB::table('jobs')
                ->where('queue', SendLetterNotificationJob::QUEUE)
                ->where('payload', 'not like', '%LetterNotificationQueueHeartbeatJob%')
                ->min('created_at');

            if (! is_numeric($oldestCreatedAt)) {
                return [
                    'count' => $count,
                    'real_jobs_count' => max(0, $count - $heartbeatJobsCount),
                    'oldest_age_in_seconds' => null,
                ];
            }

            return [
                'count' => $count,
                'real_jobs_count' => max(0, $count - $heartbeatJobsCount),
                'oldest_age_in_seconds' => max(0, now()->timestamp - (int) $oldestCreatedAt),
            ];
        } catch (Throwable $e) {
            return [
                'count' => 0,
                'real_jobs_count' => 0,
                'oldest_age_in_seconds' => null,
            ];
        }
    }

    protected function getStaleSendingCount(): int
    {
        return LetterNotification::query()
            ->where('status', LetterNotification::STATUS_SENDING)
            ->where('updated_at', '<', now()->subSeconds(self::STALE_SENDING_AFTER_SECONDS))
            ->count();
    }

    protected function buildHealthyDescription(Carbon $lastHeartbeatAt, int $outstandingJobsCount): string
    {
        $description = 'Ostatni sygnał od workera: ' . $this->formatTimeAgo($lastHeartbeatAt) . '.';

        if ($outstandingJobsCount > 0) {
            return $description . ' W kolejce są teraz ' . $outstandingJobsCount . ' joby do obsłużenia.';
        }

        return $description . ' Brak zaległych jobów na kolejce natychmiastowej wysyłki.';
    }

    protected function buildUnhealthyDescription(
        int $outstandingJobsCount,
        ?int $oldestOutstandingJobAgeInSeconds,
        int $staleSendingCount,
    ): string {
        $parts = [];

        if ($outstandingJobsCount > 0) {
            $part = 'Oczekujące joby: ' . $outstandingJobsCount;

            if ($oldestOutstandingJobAgeInSeconds !== null) {
                $part .= ' (najstarszy czeka ' . $this->formatSeconds($oldestOutstandingJobAgeInSeconds) . ')';
            }

            $parts[] = $part . '.';
        }

        if ($staleSendingCount > 0) {
            $parts[] = 'Powiadomienia w stanie "Wysyłam..." bez postępu: ' . $staleSendingCount . '.';
        }

        $parts[] = 'Sprawdź, czy proces `php artisan queue:work database --queue=letter-notifications` nadal działa.';

        return implode(' ', $parts);
    }

    protected function formatTimeAgo(Carbon $dateTime): string
    {
        return $this->formatSeconds(max(0, now()->diffInSeconds($dateTime)));
    }

    protected function formatSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' sek. temu';
        }

        $minutes = intdiv($seconds, 60);

        if ($minutes < 60) {
            $remainingSeconds = $seconds % 60;

            return $remainingSeconds > 0
                ? $minutes . ' min ' . $remainingSeconds . ' sek. temu'
                : $minutes . ' min temu';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0
            ? $hours . ' godz. ' . $remainingMinutes . ' min temu'
            : $hours . ' godz. temu';
    }
}
