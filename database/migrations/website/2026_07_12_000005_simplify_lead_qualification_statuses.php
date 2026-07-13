<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NEW = 'Nowy lead';
    private const AUTOMATICALLY_QUALIFIED = 'Zakwalifikowany automatycznie';
    private const QUALIFIED = 'Zakwalifikowany';
    private const REJECTED = 'Odrzucony';

    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('website_leads', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('status_changed_at');
            }

            if (! Schema::hasColumn('website_leads', 'rejected_by')) {
                $table->foreignId('rejected_by')
                    ->nullable()
                    ->after('rejected_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('website_leads', 'rejection_reason')) {
                $table->string('rejection_reason')->nullable()->after('rejected_by');
                $table->index('rejection_reason');
            }

            if (! Schema::hasColumn('website_leads', 'rejection_note')) {
                $table->text('rejection_note')->nullable()->after('rejection_reason');
            }
        });

        $now = now();
        $currentStatuses = [
            self::NEW,
            self::QUALIFIED,
            self::AUTOMATICALLY_QUALIFIED,
            self::REJECTED,
        ];

        DB::table('website_leads')
            ->select(['id', 'status', 'potential_matter_id'])
            ->orderBy('id')
            ->chunkById(100, function ($leads) use ($now, $currentStatuses): void {
                foreach ($leads as $lead) {
                    if ($lead->status === self::REJECTED) {
                        continue;
                    }

                    $status = filled($lead->potential_matter_id)
                        ? self::AUTOMATICALLY_QUALIFIED
                        : (in_array($lead->status, $currentStatuses, true) ? $lead->status : self::NEW);

                    if ($status === $lead->status) {
                        continue;
                    }

                    DB::table('website_leads')
                        ->where('id', $lead->id)
                        ->update([
                            'status' => $status,
                            'status_changed_at' => $now,
                            'updated_at' => $now,
                        ]);

                    DB::table('website_lead_status_changes')->insert([
                        'lead_id' => $lead->id,
                        'status' => $status,
                        'changed_at' => $now,
                        'changed_by' => null,
                        'note' => 'Status leada uproszczony po rozdzieleniu kwalifikacji marketingowej od etapów potencjalnej sprawy.',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            if (Schema::hasColumn('website_leads', 'rejection_reason')) {
                $table->dropIndex(['rejection_reason']);
            }

            if (Schema::hasColumn('website_leads', 'rejected_by')) {
                $table->dropConstrainedForeignId('rejected_by');
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('website_leads', 'rejected_at') ? 'rejected_at' : null,
                Schema::hasColumn('website_leads', 'rejection_reason') ? 'rejection_reason' : null,
                Schema::hasColumn('website_leads', 'rejection_note') ? 'rejection_note' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
