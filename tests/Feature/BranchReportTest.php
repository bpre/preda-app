<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Matter;
use App\Models\Payment;
use App\Models\User;
use App\Support\Branches\BranchReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BranchReportTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_summarizes_branch_matters_payments_and_expenses(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-21 12:00:00'));

        $user = User::factory()->create(['is_lawyer' => true]);

        $branch = Branch::create([
            'label' => 'Głogów',
            'user_id' => $user->id,
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
            'is_default_for_new_matters' => true,
        ]);

        $matter = Matter::create([
            'label' => 'Kowalski / Bank',
            'lawyer_id' => $user->id,
            'category' => 'CHF',
            'is_chf' => true,
            'is_matter' => true,
            'is_archived' => false,
            'branch_id' => $branch->id,
            'start' => '2026-01-10',
            'end' => '2026-03-05',
        ]);

        Matter::create([
            'label' => 'Nowak / Bank',
            'lawyer_id' => $user->id,
            'category' => 'CHF',
            'is_chf' => true,
            'is_matter' => true,
            'is_archived' => false,
            'branch_id' => $branch->id,
            'start' => '2026-04-15',
            'end' => null,
        ]);

        Matter::create([
            'label' => 'Zieliński / Bank',
            'lawyer_id' => $user->id,
            'category' => 'CHF',
            'is_chf' => true,
            'is_matter' => true,
            'is_archived' => true,
            'branch_id' => $branch->id,
            'start' => '2025-05-20',
            'end' => '2026-01-03',
        ]);

        Matter::create([
            'label' => 'Wiśniewski / Sprawa inna',
            'lawyer_id' => $user->id,
            'category' => 'Sprawy inne',
            'is_chf' => false,
            'is_matter' => true,
            'is_archived' => false,
            'branch_id' => $branch->id,
            'start' => '2026-02-15',
            'end' => null,
        ]);

        Payment::create([
            'label' => 'I rata',
            'amount' => 1000,
            'matter_id' => $matter->id,
            'is_paid' => true,
            'date' => '2026-02-01',
        ]);

        $future = Payment::create([
            'label' => 'II rata',
            'amount' => 500,
            'matter_id' => $matter->id,
            'is_paid' => false,
            'deadline' => '2026-04-01',
        ]);
        $future->forceFill(['created_at' => '2026-02-10 10:00:00'])->save();

        $potential = Payment::create([
            'label' => 'Premia',
            'amount' => 300,
            'matter_id' => $matter->id,
            'is_paid' => false,
            'deadline' => null,
        ]);
        $potential->forceFill(['created_at' => '2026-03-10 10:00:00'])->save();

        Expense::create([
            'label' => 'Czynsz',
            'amount' => 200,
            'date' => '2026-02-05',
            'branch_id' => $branch->id,
        ]);

        $report = BranchReport::make($branch)->toArray();

        $this->assertSame(1, $report['months']['2026-01']['matters']);
        $this->assertSame(1000.0, $report['months']['2026-02']['paid']);
        $this->assertSame(500.0, $report['months']['2026-02']['future']);
        $this->assertSame(200.0, $report['months']['2026-02']['expense']);
        $this->assertSame(1, $report['months']['2026-03']['ended']);
        $this->assertSame(300.0, $report['months']['2026-03']['potential']);

        $this->assertSame(1, $report['years']['2025']['active_at_period_end']);
        $this->assertSame(1, $report['years']['2026']['active_at_period_end']);

        $this->assertSame(3, $report['summary']['matters']);
        $this->assertSame(1, $report['summary']['active_matters']);
        $this->assertSame(2, $report['summary']['ended_matters']);
        $this->assertSame(1, $branch->activeChfMatters()->count());

        $this->assertSame(3, $report['totals']['matters']);
        $this->assertSame(2, $report['totals']['ended']);
        $this->assertSame(1000.0, $report['totals']['paid']);
        $this->assertSame(500.0, $report['totals']['future']);
        $this->assertSame(300.0, $report['totals']['potential']);
        $this->assertSame(200.0, $report['totals']['expense']);

        $februaryReport = BranchReport::make($branch, 'CHF', '2026-02-01', '2026-02-28')->toArray();

        $this->assertArrayNotHasKey('2026-01', $februaryReport['months']);
        $this->assertSame(1000.0, $februaryReport['months']['2026-02']['paid']);
        $this->assertSame(500.0, $februaryReport['months']['2026-02']['future']);
        $this->assertSame(200.0, $februaryReport['months']['2026-02']['expense']);
        $this->assertSame(0, $februaryReport['totals']['matters']);
        $this->assertSame(1, $februaryReport['years']['2026']['active_at_period_end']);
        $this->assertSame('2026-02-01', $februaryReport['filters']['from']);
        $this->assertSame('2026-02-28', $februaryReport['filters']['to']);

        $allCategoriesReport = BranchReport::make($branch, BranchReport::CATEGORY_ALL)->toArray();

        $this->assertSame(4, $allCategoriesReport['totals']['matters']);
        $this->assertSame(2, $allCategoriesReport['years']['2026']['active_at_period_end']);
    }
}
