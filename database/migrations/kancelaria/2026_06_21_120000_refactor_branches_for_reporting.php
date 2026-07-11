<?php

use App\Models\Branch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('type')->default(Branch::TYPE_STATIONARY)->after('user_id');
            $table->boolean('accepts_new_matters')->default(true)->after('type');
            $table->date('closed_at')->nullable()->after('accepts_new_matters');
            $table->boolean('is_default_for_new_matters')->default(false)->after('closed_at');
        });

        Schema::table('matters', function (Blueprint $table) {
            $table->index('branch_id');
        });

        $this->ensureBranches();
        $this->syncMatterBranchIds();
    }

    public function down(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'accepts_new_matters',
                'closed_at',
                'is_default_for_new_matters',
            ]);
        });
    }

    private function ensureBranches(): void
    {
        $fallbackUserId = DB::table('users')->where('id', 1)->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        if ($fallbackUserId === null) {
            return;
        }

        $branches = [
            'Głogów' => [
                'user_id' => $this->existingUserId(1, $fallbackUserId),
                'sort' => 10,
                'type' => Branch::TYPE_STATIONARY,
                'accepts_new_matters' => true,
                'closed_at' => null,
                'is_default_for_new_matters' => true,
            ],
            'Legnica' => [
                'user_id' => $this->existingUserId(2, $fallbackUserId),
                'sort' => 20,
                'type' => Branch::TYPE_STATIONARY,
                'accepts_new_matters' => true,
                'closed_at' => null,
                'is_default_for_new_matters' => false,
            ],
            'Zielona Góra' => [
                'user_id' => $this->existingUserId(2, $fallbackUserId),
                'sort' => 30,
                'type' => Branch::TYPE_STATIONARY,
                'accepts_new_matters' => true,
                'closed_at' => null,
                'is_default_for_new_matters' => false,
            ],
            'Zdalne' => [
                'user_id' => $this->existingUserId(1, $fallbackUserId),
                'sort' => 40,
                'type' => Branch::TYPE_REMOTE,
                'accepts_new_matters' => true,
                'closed_at' => null,
                'is_default_for_new_matters' => false,
            ],
            'Leszno' => [
                'user_id' => $this->existingUserId(3, $fallbackUserId),
                'sort' => 90,
                'type' => Branch::TYPE_STATIONARY,
                'accepts_new_matters' => false,
                'closed_at' => now()->toDateString(),
                'is_default_for_new_matters' => false,
            ],
            'Wrocław' => [
                'user_id' => $this->existingUserId(3, $fallbackUserId),
                'sort' => 100,
                'type' => Branch::TYPE_STATIONARY,
                'accepts_new_matters' => false,
                'closed_at' => now()->toDateString(),
                'is_default_for_new_matters' => false,
            ],
        ];

        DB::table('branches')->update(['is_default_for_new_matters' => false]);

        foreach ($branches as $label => $attributes) {
            DB::table('branches')->updateOrInsert(
                ['label' => $label],
                [
                    'id' => DB::table('branches')->where('label', $label)->value('id') ?? (string) Str::uuid(),
                    ...$attributes,
                    'updated_at' => now(),
                    'created_at' => DB::table('branches')->where('label', $label)->value('created_at') ?? now(),
                ],
            );
        }
    }

    private function existingUserId(int $preferredUserId, int $fallbackUserId): int
    {
        return DB::table('users')->where('id', $preferredUserId)->exists()
            ? $preferredUserId
            : $fallbackUserId;
    }

    private function syncMatterBranchIds(): void
    {
        DB::table('branches')
            ->select(['id', 'label'])
            ->orderBy('label')
            ->get()
            ->each(function (object $branch): void {
                DB::table('matters')
                    ->where('branch', $branch->label)
                    ->where(function ($query): void {
                        $query->whereNull('branch_id')->orWhere('branch_id', '');
                    })
                    ->update(['branch_id' => $branch->id]);
            });
    }
};
