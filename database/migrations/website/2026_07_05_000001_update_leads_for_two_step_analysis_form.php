<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('bank')->nullable()->after('phone');
            $table->string('contract_year_range')->nullable()->after('bank');
            $table->string('credit_currency')->nullable()->after('contract_year_range');
            $table->string('credit_status')->nullable()->after('credit_currency');
            $table->boolean('has_contract')->nullable()->after('credit_status');
            $table->uuid('upload_token')->nullable()->unique()->after('files');
            $table->timestamp('documents_uploaded_at')->nullable()->after('upload_token');
            $table->timestamp('documents_skipped_at')->nullable()->after('documents_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique(['upload_token']);
            $table->dropColumn([
                'bank',
                'contract_year_range',
                'credit_currency',
                'credit_status',
                'has_contract',
                'upload_token',
                'documents_uploaded_at',
                'documents_skipped_at',
            ]);
        });
    }
};
