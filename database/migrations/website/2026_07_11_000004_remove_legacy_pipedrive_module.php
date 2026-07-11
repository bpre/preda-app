<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $permissionIds = DB::table('permissions')
            ->where('name', 'like', '%Pipedrive%')
            ->orWhere('name', 'like', '%pipedrive%')
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

        Schema::dropIfExists('website_pipedrives');
    }

    public function down(): void
    {
        // Intentionally irreversible: the legacy one-off Pipedrive module was removed.
    }
};
