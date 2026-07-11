<?php

namespace Tests\Feature;

use App\Filament\Resources\MatterResource;
use App\Models\Branch;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchMatterOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_matters_only_see_branches_accepting_new_matters(): void
    {
        $user = User::factory()->create(['is_lawyer' => true]);

        $open = Branch::create([
            'label' => 'Głogów',
            'user_id' => $user->id,
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
            'is_default_for_new_matters' => true,
        ]);

        $closed = Branch::create([
            'label' => 'Leszno',
            'user_id' => $user->id,
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => false,
            'closed_at' => '2026-06-21',
        ]);

        $options = MatterResource::branchOptionsForMatter();

        $this->assertArrayHasKey($open->id, $options);
        $this->assertArrayNotHasKey($closed->id, $options);
    }

    public function test_existing_matter_keeps_closed_branch_available_for_editing(): void
    {
        $user = User::factory()->create(['is_lawyer' => true]);

        Branch::create([
            'label' => 'Głogów',
            'user_id' => $user->id,
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
        ]);

        $closed = Branch::create([
            'label' => 'Wrocław',
            'user_id' => $user->id,
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => false,
            'closed_at' => '2026-06-21',
        ]);

        $matter = Matter::create([
            'label' => 'Nowak / Bank',
            'lawyer_id' => $user->id,
            'category' => 'CHF',
            'is_chf' => true,
            'is_matter' => true,
            'branch_id' => $closed->id,
        ]);

        $options = MatterResource::branchOptionsForMatter($matter);

        $this->assertArrayHasKey($closed->id, $options);
    }
}
