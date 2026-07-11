<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_branch_report(): void
    {
        $admin = User::factory()->create(['is_lawyer' => true]);
        $admin->assignRole($this->superAdminRole());

        $branch = Branch::create([
            'label' => 'Głogów',
            'user_id' => $admin->id,
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
        ]);

        $this
            ->actingAs($admin)
            ->get(route('branches.report.export', [
                'branch' => $branch,
                'format' => 'xlsx',
                'report_category' => 'CHF',
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_non_admin_cannot_export_branch_report(): void
    {
        $user = User::factory()->create(['is_lawyer' => true]);

        $branch = Branch::create([
            'label' => 'Głogów',
            'user_id' => $user->id,
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
        ]);

        $this
            ->actingAs($user)
            ->get(route('branches.report.export', ['branch' => $branch, 'format' => 'xlsx']))
            ->assertForbidden();
    }

    private function superAdminRole(): Role
    {
        return Role::findOrCreate(config('filament-shield.super_admin.name', 'super_admin'), 'web');
    }
}
