<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('name', [
                'ViewAny:Offer',
                'View:Offer',
                'Create:Offer',
                'Update:Offer',
                'Delete:Offer',
                'DeleteAny:Offer',
                'ForceDelete:Offer',
                'ForceDeleteAny:Offer',
                'Restore:Offer',
                'RestoreAny:Offer',
                'Replicate:Offer',
                'Reorder:Offer',
            ])
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')
                ->whereIn('permission_id', $permissionIds)
                ->delete();

            DB::table('model_has_permissions')
                ->whereIn('permission_id', $permissionIds)
                ->delete();

            DB::table('permissions')
                ->whereIn('id', $permissionIds)
                ->delete();
        }

        Schema::dropIfExists('website_offers');
    }

    public function down(): void
    {
        // Intentionally irreversible: the legacy website offer-request module was removed.
    }
};
