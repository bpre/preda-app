<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserImpersonationService;
use App\Support\PanelRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserImpersonationController extends Controller
{
    public function start(Request $request, UserImpersonationService $impersonation, User $user): RedirectResponse
    {
        $actor = $request->user();

        abort_unless($actor instanceof User, 403);

        return $impersonation->start(
            $actor,
            $user,
            $request->headers->get('referer'),
            $this->panelId($request),
        );
    }

    public function consume(Request $request, UserImpersonationService $impersonation, string $token): RedirectResponse
    {
        return $impersonation->consumeHandoff(
            $token,
            $this->panelId($request),
        );
    }

    public function stop(Request $request, UserImpersonationService $impersonation): RedirectResponse
    {
        $panelId = $this->panelId($request);

        return $impersonation->stop(
            $panelId ? PanelRegistry::urlFor($panelId) : $request->headers->get('referer'),
        );
    }

    private function panelId(Request $request): ?string
    {
        $panelId = $request->route('preda_panel_id');

        return is_string($panelId) ? $panelId : null;
    }
}
