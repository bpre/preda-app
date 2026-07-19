<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserImpersonationLog;
use App\Support\PanelRegistry;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserImpersonationService
{
    public const PERMISSION = 'impersonate_users';

    public const SESSION_KEY = 'user_impersonation';

    private const HANDOFF_TTL_MINUTES = 5;

    public function canStart(?User $actor, User $target): bool
    {
        if (! $actor instanceof User) {
            return false;
        }

        if ($this->isActive()) {
            return false;
        }

        if ($actor->is($target)) {
            return false;
        }

        if (! $actor->can(self::PERMISSION)) {
            return false;
        }

        if (! $target->is_active || ! $target->is_employee) {
            return false;
        }

        if ($this->isSuperAdmin($target) && ! $this->isSuperAdmin($actor)) {
            return false;
        }

        return $this->availablePanelIdsFor($target) !== [];
    }

    public function start(User $actor, User $target, ?string $returnUrl = null, ?string $preferredPanelId = null): mixed
    {
        abort_unless($this->canStart($actor, $target), 403);

        $targetPanelId = $this->targetPanelIdFor($target, $preferredPanelId ?? Filament::getCurrentPanel()?->getId());
        $token = $targetPanelId === $preferredPanelId ? null : Str::random(64);

        $log = UserImpersonationLog::create([
            'impersonator_id' => $actor->getKey(),
            'impersonated_user_id' => $target->getKey(),
            'started_at' => now(),
            'start_url' => request()->fullUrl(),
            'return_url' => $this->safeReturnUrl($returnUrl),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'handoff_token_hash' => $token ? $this->hashToken($token) : null,
            'handoff_expires_at' => $token ? now()->addMinutes(self::HANDOFF_TTL_MINUTES) : null,
        ]);

        if ($token === null) {
            $this->loginAsTarget($actor, $target, $log);

            return redirect()->to(PanelRegistry::urlFor($targetPanelId));
        }

        return redirect()->to($this->handoffUrlFor($targetPanelId, $token));
    }

    public function consumeHandoff(string $token, ?string $panelId = null): RedirectResponse
    {
        $log = UserImpersonationLog::query()
            ->where('handoff_token_hash', $this->hashToken($token))
            ->whereNull('handoff_consumed_at')
            ->first();

        abort_unless($log instanceof UserImpersonationLog, 403);
        abort_if($log->handoff_expires_at?->isPast() ?? true, 403);

        $target = $log->impersonatedUser;
        $actor = $log->impersonator;

        abort_unless($target instanceof User && $actor instanceof User, 403);
        abort_unless($target->is_active && $target->is_employee, 403);

        $targetPanelId = $this->targetPanelIdFor($target, $panelId);

        $log->forceFill([
            'handoff_consumed_at' => now(),
        ])->save();

        $this->loginAsTarget($actor, $target, $log);

        return redirect()->to(PanelRegistry::urlFor($targetPanelId));
    }

    public function stop(?string $fallbackUrl = null): RedirectResponse
    {
        $state = $this->state();

        if ($state === null) {
            return redirect()->to($fallbackUrl ?: PanelRegistry::urlFor('kancelaria'));
        }

        $this->markCurrentLogEnded();

        $impersonator = User::query()->find($state['impersonator_id'] ?? null);
        $returnUrl = $this->safeReturnUrl($state['return_url'] ?? null)
            ?: $fallbackUrl
            ?: PanelRegistry::urlFor('kancelaria');

        session()->forget(self::SESSION_KEY);

        if (! $impersonator instanceof User || ! $impersonator->is_active) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->to(PanelRegistry::urlFor('kancelaria'));
        }

        Auth::login($impersonator);
        $this->syncAuthenticateSessionPasswordHash($impersonator);
        session()->regenerateToken();

        return redirect()->to($returnUrl);
    }

    public function markCurrentLogEnded(?string $stopUrl = null): void
    {
        $state = $this->state();
        $logId = $state['log_id'] ?? null;

        if (! $logId) {
            return;
        }

        UserImpersonationLog::query()
            ->whereKey($logId)
            ->whereNull('ended_at')
            ->update([
                'ended_at' => now(),
                'stop_url' => $stopUrl ?: request()->fullUrl(),
                'updated_at' => now(),
            ]);
    }

    public function isActive(): bool
    {
        return $this->state() !== null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function state(): ?array
    {
        $state = session(self::SESSION_KEY);

        return is_array($state) ? $state : null;
    }

    /**
     * @return array<int, string>
     */
    private function availablePanelIdsFor(User $user): array
    {
        return collect(PanelRegistry::definitions())
            ->keys()
            ->filter(fn (string $panelId): bool => $user->canAccessPredaPanel($panelId))
            ->values()
            ->all();
    }

    private function targetPanelIdFor(User $user, ?string $preferredPanelId = null): string
    {
        $panelIds = $this->availablePanelIdsFor($user);

        if ($preferredPanelId && in_array($preferredPanelId, $panelIds, true)) {
            return $preferredPanelId;
        }

        return $panelIds[0] ?? 'kancelaria';
    }

    private function handoffUrlFor(string $panelId, string $token): string
    {
        return rtrim(PanelRegistry::urlFor($panelId), '/').'/impersonacja/przejmij/'.$token;
    }

    private function loginAsTarget(User $actor, User $target, UserImpersonationLog $log): void
    {
        Auth::login($target);
        $this->syncAuthenticateSessionPasswordHash($target);
        session()->put(self::SESSION_KEY, [
            'impersonator_id' => $actor->getKey(),
            'impersonator_name' => $actor->name,
            'impersonated_user_id' => $target->getKey(),
            'impersonated_user_name' => $target->name,
            'log_id' => $log->getKey(),
            'return_url' => $this->safeReturnUrl($log->return_url),
            'started_at' => $log->started_at?->toIso8601String() ?? now()->toIso8601String(),
        ]);
        session()->regenerateToken();
    }

    private function syncAuthenticateSessionPasswordHash(User $user): void
    {
        $passwordHash = $user->getAuthPassword();

        if (! is_string($passwordHash) || blank($passwordHash)) {
            return;
        }

        $guard = Auth::guard();

        if (method_exists($guard, 'hashPasswordForCookie')) {
            $passwordHash = $guard->hashPasswordForCookie($passwordHash);
        }

        session()->put('password_hash_'.Auth::getDefaultDriver(), $passwordHash);
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasRole(config('filament-shield.super_admin.name', 'super_admin'));
    }

    private function safeReturnUrl(mixed $url): ?string
    {
        if (! is_string($url) || blank($url)) {
            return null;
        }

        if (str_contains($url, '/livewire/')) {
            return null;
        }

        return $url;
    }
}
