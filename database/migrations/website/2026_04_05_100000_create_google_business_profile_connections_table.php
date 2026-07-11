<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_google_business_profile_connections', function (Blueprint $table) {
            $table->id();
            $table->string('google_account_name')->nullable();
            $table->string('google_account_label')->nullable();
            $table->string('google_location_name')->nullable();
            $table->string('google_location_title')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->text('scopes')->nullable();
            $table->json('available_accounts')->nullable();
            $table->json('available_locations')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_google_business_profile_connections');
    }
};
