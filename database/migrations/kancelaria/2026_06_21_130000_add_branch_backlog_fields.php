<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('street')->nullable()->after('is_default_for_new_matters');
            $table->string('postal_code')->nullable()->after('street');
            $table->string('city')->nullable()->after('postal_code');
            $table->string('email')->nullable()->after('city');
            $table->string('phone')->nullable()->after('email');
            $table->unsignedSmallInteger('monthly_matter_goal')->nullable()->after('phone');
            $table->decimal('monthly_revenue_goal', 12, 2)->nullable()->after('monthly_matter_goal');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->string('category')->default('ogolne')->after('label');
            $table->index(['branch_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'category']);
            $table->dropColumn('category');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'street',
                'postal_code',
                'city',
                'email',
                'phone',
                'monthly_matter_goal',
                'monthly_revenue_goal',
            ]);
        });
    }
};
