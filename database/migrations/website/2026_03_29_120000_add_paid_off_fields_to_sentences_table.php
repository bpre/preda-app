<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $shouldAddIsPaidOff = ! Schema::hasColumn('website_sentences', 'is_paid_off');
        $shouldAddPaidOffYear = ! Schema::hasColumn('website_sentences', 'paid_off_year');

        if (! $shouldAddIsPaidOff && ! $shouldAddPaidOffYear) {
            return;
        }

        Schema::table('website_sentences', function (Blueprint $table) use ($shouldAddIsPaidOff, $shouldAddPaidOffYear) {
            if ($shouldAddIsPaidOff) {
                $table->boolean('is_paid_off')->default(false);
            }

            if ($shouldAddPaidOffYear) {
                $table->string('paid_off_year')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $shouldDropIsPaidOff = Schema::hasColumn('website_sentences', 'is_paid_off');
        $shouldDropPaidOffYear = Schema::hasColumn('website_sentences', 'paid_off_year');

        if (! $shouldDropIsPaidOff && ! $shouldDropPaidOffYear) {
            return;
        }

        Schema::table('website_sentences', function (Blueprint $table) use ($shouldDropIsPaidOff, $shouldDropPaidOffYear) {
            if ($shouldDropPaidOffYear) {
                $table->dropColumn('paid_off_year');
            }

            if ($shouldDropIsPaidOff) {
                $table->dropColumn('is_paid_off');
            }
        });
    }
};
