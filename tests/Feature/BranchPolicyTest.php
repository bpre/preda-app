<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Matter;
use App\Models\User;
use App\Policies\BranchPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_branch_module(): void
    {
        $admin = User::factory()->create(['is_lawyer' => true]);
        $admin->assignRole($this->superAdminRole());

        $lawyer = User::factory()->create(['is_lawyer' => true]);
        $branch = Branch::create([
            'label' => 'Głogów',
            'user_id' => $admin->id,
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
        ]);

        $policy = new BranchPolicy;

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->view($admin, $branch));
        $this->assertFalse($policy->viewAny($lawyer));
        $this->assertFalse($policy->view($lawyer, $branch));
    }

    public function test_branch_with_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['is_lawyer' => true]);
        $admin->assignRole($this->superAdminRole());

        $branch = Branch::create([
            'label' => 'Głogów',
            'user_id' => $admin->id,
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
        ]);

        Matter::create([
            'label' => 'Kowalski / Bank',
            'lawyer_id' => $admin->id,
            'category' => 'CHF',
            'is_chf' => true,
            'is_matter' => true,
            'branch_id' => $branch->id,
        ]);

        $this->assertFalse((new BranchPolicy)->delete($admin, $branch));
    }

    public function test_branch_can_be_closed_and_reopened_for_new_matters(): void
    {
        $admin = User::factory()->create(['is_lawyer' => true]);

        $branch = Branch::create([
            'label' => 'Głogów',
            'user_id' => $admin->id,
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
            'is_default_for_new_matters' => true,
        ]);

        $branch->closeForNewMatters('2026-06-21');
        $branch->refresh();

        $this->assertFalse($branch->accepts_new_matters);
        $this->assertFalse($branch->is_default_for_new_matters);
        $this->assertSame('2026-06-21', $branch->closed_at->toDateString());

        $branch->reopenForNewMatters();
        $branch->refresh();

        $this->assertTrue($branch->accepts_new_matters);
        $this->assertNull($branch->closed_at);
    }

    private function superAdminRole(): Role
    {
        return Role::findOrCreate(config('filament-shield.super_admin.name', 'super_admin'), 'web');
    }
}
