<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('website_leads', 'additional_info')) {
                $table->text('additional_info')->nullable()->after('message');
            }
        });

        DB::table('website_leads')
            ->whereNull('additional_info')
            ->where('message', 'like', '%Dodatkowe informacje:%')
            ->orderBy('id')
            ->select(['id', 'message'])
            ->chunkById(200, function ($leads): void {
                foreach ($leads as $lead) {
                    $additionalInfo = $this->extractAdditionalInfo((string) $lead->message);

                    if ($additionalInfo === null) {
                        continue;
                    }

                    DB::table('website_leads')
                        ->where('id', $lead->id)
                        ->update(['additional_info' => $additionalInfo]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            if (Schema::hasColumn('website_leads', 'additional_info')) {
                $table->dropColumn('additional_info');
            }
        });
    }

    private function extractAdditionalInfo(string $message): ?string
    {
        if (! preg_match('/(?:^|\R)Dodatkowe informacje:\s*(.+)\z/su', $message, $matches)) {
            return null;
        }

        $additionalInfo = trim($matches[1]);

        return $additionalInfo === '' ? null : $additionalInfo;
    }
};
