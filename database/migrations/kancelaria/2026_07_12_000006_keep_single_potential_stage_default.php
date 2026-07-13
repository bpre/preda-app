<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('template_stages')) {
            return;
        }

        DB::table('template_stages')
            ->where('category', 'Potencjalna')
            ->update(['is_chf_default' => false]);

        DB::table('template_stages')
            ->where('category', 'Potencjalna')
            ->where('label', 'Nowa umowa')
            ->update(['is_chf_default' => true]);
    }

    public function down(): void
    {
        //
    }
};
