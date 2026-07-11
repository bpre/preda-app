<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\FilamentContentLayout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilamentLayoutPreferenceController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'record_list_pages_full_width' => ['required', 'boolean'],
        ]);

        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $preferences = FilamentContentLayout::saveRecordListPagesFullWidth(
            $user,
            $request->boolean('record_list_pages_full_width'),
        );

        return response()->json([
            'mode' => $preferences['record_list_pages_full_width'] ? 'full' : 'contained',
            'record_list_pages_full_width' => $preferences['record_list_pages_full_width'],
            'storage_key' => $preferences['record_list_pages_full_width_storage_key'],
        ]);
    }
}
