<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('matters')
            || ! Schema::hasTable('website_leads')
            || ! Schema::hasColumn('website_leads', 'potential_matter_id')
        ) {
            return;
        }

        DB::table('matters')
            ->join('website_leads', 'website_leads.potential_matter_id', '=', 'matters.id')
            ->whereNotNull('matters.userinfo')
            ->select(['matters.id', 'matters.userinfo'])
            ->orderBy('matters.id')
            ->chunk(200, function ($matters): void {
                foreach ($matters as $matter) {
                    $userinfo = json_decode((string) $matter->userinfo, true);

                    if ($this->isValidBuilderState($userinfo)) {
                        continue;
                    }

                    DB::table('matters')
                        ->where('id', $matter->id)
                        ->update(['userinfo' => json_encode([], JSON_UNESCAPED_UNICODE)]);
                }
            });
    }

    public function down(): void
    {
        //
    }

    private function isValidBuilderState(mixed $state): bool
    {
        if (! is_array($state)) {
            return false;
        }

        foreach ($state as $item) {
            if (
                ! is_array($item)
                || ! array_key_exists('type', $item)
                || ! array_key_exists('data', $item)
                || ! is_array($item['data'])
            ) {
                return false;
            }
        }

        return true;
    }
};
