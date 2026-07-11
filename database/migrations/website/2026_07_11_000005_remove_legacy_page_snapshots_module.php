<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $permissionIds = DB::table('permissions')
            ->where('name', 'like', '%PageSnapshot%')
            ->orWhere('name', 'like', '%page::snapshots%')
            ->orWhere('name', 'like', '%page_snapshot%')
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

        Schema::dropIfExists('website_page_snapshots');
    }

    public function down(): void
    {
        // Intentionally irreversible: the legacy Page Snapshots module was removed.
    }
};
