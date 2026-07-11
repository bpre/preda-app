<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULT_STATUS = 'Nowy lead';

    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->string('status')->default(self::DEFAULT_STATUS)->after('documents_skipped_at');
            $table->timestamp('status_changed_at')->nullable()->after('status');
            $table->index('status');
        });

        Schema::create('website_lead_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('website_leads')->cascadeOnDelete();
            $table->string('status');
            $table->timestamp('changed_at');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'changed_at']);
            $table->index('status');
        });

        $now = now();

        DB::table('website_leads')
            ->whereNull('status_changed_at')
            ->update([
                'status' => self::DEFAULT_STATUS,
                'status_changed_at' => $now,
            ]);

        DB::table('website_leads')
            ->select(['id', 'status', 'status_changed_at', 'created_at'])
            ->orderBy('id')
            ->chunkById(100, function ($leads) use ($now): void {
                $rows = [];

                foreach ($leads as $lead) {
                    $rows[] = [
                        'lead_id' => $lead->id,
                        'status' => $lead->status ?: self::DEFAULT_STATUS,
                        'changed_at' => $lead->status_changed_at ?: ($lead->created_at ?: $now),
                        'changed_by' => null,
                        'note' => 'Status początkowy.',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('website_lead_status_changes')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_lead_status_changes');

        Schema::table('website_leads', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'status_changed_at',
            ]);
        });
    }
};
