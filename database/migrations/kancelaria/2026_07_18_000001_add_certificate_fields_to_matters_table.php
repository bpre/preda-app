<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matters', function (Blueprint $table): void {
            if (! Schema::hasColumn('matters', 'has_certificate')) {
                $table->boolean('has_certificate')
                    ->default(false)
                    ->after('current_template_stage_id');
            }

            if (! Schema::hasColumn('matters', 'potential_benefits_amount')) {
                $table->decimal('potential_benefits_amount', 12, 2)
                    ->nullable()
                    ->after('has_certificate');
            }

            if (! Schema::hasColumn('matters', 'future_installments_cancellation_amount')) {
                $table->decimal('future_installments_cancellation_amount', 12, 2)
                    ->nullable()
                    ->after('potential_benefits_amount');
            }

            if (! Schema::hasColumn('matters', 'overpayment_refund_amount')) {
                $table->decimal('overpayment_refund_amount', 12, 2)
                    ->nullable()
                    ->after('future_installments_cancellation_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('matters', function (Blueprint $table): void {
            $columns = [
                'overpayment_refund_amount',
                'future_installments_cancellation_amount',
                'potential_benefits_amount',
                'has_certificate',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('matters', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
