<?php

namespace App\Support\Branches;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Matter;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BranchReport
{
    public const CATEGORY_ALL = 'all';

    private readonly ?string $category;

    private readonly ?Carbon $fromDate;

    private readonly ?Carbon $toDate;

    public function __construct(
        private readonly Branch $branch,
        ?string $category = 'CHF',
        mixed $from = null,
        mixed $to = null,
    ) {
        $this->category = $this->normalizeCategory($category);

        $fromDate = $this->parseDate($from)?->startOfDay();
        $toDate = $this->parseDate($to)?->endOfDay();

        if ($fromDate && $toDate && $fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public static function make(Branch $branch, ?string $category = 'CHF', mixed $from = null, mixed $to = null): self
    {
        return new self($branch, $category, $from, $to);
    }

    public static function categoryOptions(): array
    {
        return [
            'CHF' => 'CHF',
            'Sprawy inne' => 'Sprawy inne',
            self::CATEGORY_ALL => 'Wszystkie',
        ];
    }

    public function toArray(): array
    {
        $months = [];
        $years = [];
        $totals = $this->emptyRow();
        $mattersWithoutStart = collect();

        $this->payments()->each(function (Payment $payment) use (&$months, &$years, &$totals): void {
            if ($payment->is_paid) {
                if (! $this->dateIsInRange($payment->date)) {
                    return;
                }

                $this->addAmount($months, $years, $payment->date, 'paid', (float) $payment->amount);
                $totals['paid'] += (float) $payment->amount;

                return;
            }

            if ($payment->deadline !== null) {
                if (! $this->dateIsInRange($payment->created_at)) {
                    return;
                }

                $this->addAmount($months, $years, $payment->created_at, 'future', (float) $payment->amount);
                $totals['future'] += (float) $payment->amount;

                return;
            }

            if (! $this->dateIsInRange($payment->created_at)) {
                return;
            }

            $this->addAmount($months, $years, $payment->created_at, 'potential', (float) $payment->amount);
            $totals['potential'] += (float) $payment->amount;
        });

        $this->expenses()->each(function (Expense $expense) use (&$months, &$years, &$totals): void {
            if (! $this->dateIsInRange($expense->date)) {
                return;
            }

            $this->addAmount($months, $years, $expense->date, 'expense', (float) $expense->amount);
            $totals['expense'] += (float) $expense->amount;
        });

        $this->matters()->each(function (Matter $matter) use (&$months, &$years, &$totals, $mattersWithoutStart): void {
            if ($matter->start) {
                if ($this->dateIsInRange($matter->start)) {
                    $this->addCount($months, $years, $matter->start, 'matters');
                    $totals['matters']++;
                }
            } else {
                $mattersWithoutStart->push($matter);
            }

            if ($matter->end && $this->dateIsInRange($matter->end)) {
                $this->addCount($months, $years, $matter->end, 'ended');
                $totals['ended']++;
            }
        });

        $this->fillActiveAtPeriodEnd($years);

        ksort($months);
        ksort($years);

        return [
            'months' => $months,
            'years' => $years,
            'totals' => $totals,
            'matters_without_start' => $mattersWithoutStart,
            'summary' => $this->summary(),
            'filters' => $this->filters(),
        ];
    }

    public function monthlyRows(): array
    {
        return $this->toArray()['months'];
    }

    public function yearlyRows(): array
    {
        return $this->toArray()['years'];
    }

    public function totals(): array
    {
        return $this->toArray()['totals'];
    }

    public function mattersWithoutStart(): Collection
    {
        return $this->toArray()['matters_without_start'];
    }

    public function summary(): array
    {
        $matters = $this->matters();

        return [
            'matters' => (clone $matters)->count(),
            'active_matters' => (clone $matters)->active()->count(),
            'ended_matters' => (clone $matters)->whereNotNull('end')->count(),
            'payments' => $this->payments()->count(),
            'expenses' => $this->expenses()->count(),
        ];
    }

    public function filters(): array
    {
        return [
            'category' => $this->category ?? self::CATEGORY_ALL,
            'category_label' => static::categoryOptions()[$this->category ?? self::CATEGORY_ALL] ?? 'Wszystkie',
            'from' => $this->fromDate?->toDateString(),
            'to' => $this->toDate?->toDateString(),
        ];
    }

    private function matters(): Builder
    {
        return Matter::query()
            ->forBranch($this->branch)
            ->where('is_matter', true)
            ->when(
                $this->category === 'CHF',
                fn (Builder $query): Builder => $query->chfMatter(),
                fn (Builder $query): Builder => $this->category
                    ? $query->where('category', $this->category)
                    : $query,
            );
    }

    private function payments(): Builder
    {
        return Payment::query()
            ->whereHas('matter', fn (Builder $query): Builder => $this->applyMatterScope($query));
    }

    private function expenses(): Builder
    {
        return Expense::query()->where('branch_id', $this->branch->getKey());
    }

    private function applyMatterScope(Builder $query): Builder
    {
        return $query
            ->forBranch($this->branch)
            ->where('is_matter', true)
            ->when(
                $this->category === 'CHF',
                fn (Builder $query): Builder => $query->chfMatter(),
                fn (Builder $query): Builder => $this->category
                    ? $query->where('category', $this->category)
                    : $query,
            );
    }

    private function addAmount(array &$months, array &$years, mixed $date, string $key, float $amount): void
    {
        if (! $this->dateIsInRange($date)) {
            return;
        }

        $month = $this->monthKey($date);
        $year = $this->yearKey($date);

        if ($month === null || $year === null) {
            return;
        }

        $this->ensureRow($months, $month);
        $this->ensureRow($years, $year);

        $months[$month][$key] += $amount;
        $years[$year][$key] += $amount;
    }

    private function addCount(array &$months, array &$years, mixed $date, string $key): void
    {
        if (! $this->dateIsInRange($date)) {
            return;
        }

        $month = $this->monthKey($date);
        $year = $this->yearKey($date);

        if ($month === null || $year === null) {
            return;
        }

        $this->ensureRow($months, $month);
        $this->ensureRow($years, $year);

        $months[$month][$key]++;
        $years[$year][$key]++;
    }

    private function ensureRow(array &$rows, string $key): void
    {
        $rows[$key] ??= $this->emptyRow();
    }

    private function emptyRow(): array
    {
        return [
            'matters' => 0,
            'ended' => 0,
            'active_at_period_end' => 0,
            'expense' => 0.0,
            'paid' => 0.0,
            'future' => 0.0,
            'potential' => 0.0,
        ];
    }

    private function fillActiveAtPeriodEnd(array &$years): void
    {
        if (($years !== [] || $this->matters()->exists()) && $this->yearIsInRange(Carbon::today()->year)) {
            $this->ensureRow($years, (string) Carbon::today()->year);
        }

        foreach (array_keys($years) as $year) {
            $years[$year]['active_at_period_end'] = $this->activeMatterCountAtPeriodEnd((int) $year);
        }
    }

    private function activeMatterCountAtPeriodEnd(int $year): int
    {
        $today = Carbon::today();
        $asOfDate = Carbon::create($year, 12, 31)->endOfDay();

        if ($year === $today->year) {
            $asOfDate = $today->endOfDay();
        }

        if ($this->toDate && $this->toDate->year === $year && $this->toDate->lt($asOfDate)) {
            $asOfDate = $this->toDate;
        }

        return $this->matters()
            ->whereNotNull('start')
            ->whereDate('start', '<=', $asOfDate->toDateString())
            ->where(function (Builder $query) use ($asOfDate): void {
                $query
                    ->whereNull('end')
                    ->orWhereDate('end', '>', $asOfDate->toDateString());
            })
            ->count();
    }

    private function normalizeCategory(?string $category): ?string
    {
        if (blank($category) || $category === self::CATEGORY_ALL) {
            return null;
        }

        return $category;
    }

    private function dateIsInRange(mixed $date): bool
    {
        $date = $this->parseDate($date);

        if (! $date) {
            return false;
        }

        if ($this->fromDate && $date->lt($this->fromDate)) {
            return false;
        }

        if ($this->toDate && $date->gt($this->toDate)) {
            return false;
        }

        return true;
    }

    private function yearIsInRange(int $year): bool
    {
        if ($this->fromDate && $year < $this->fromDate->year) {
            return false;
        }

        if ($this->toDate && $year > $this->toDate->year) {
            return false;
        }

        return true;
    }

    private function monthKey(mixed $date): ?string
    {
        return $this->parseDate($date)?->format('Y-m');
    }

    private function yearKey(mixed $date): ?string
    {
        return $this->parseDate($date)?->format('Y');
    }

    private function parseDate(mixed $date): ?Carbon
    {
        if (blank($date)) {
            return null;
        }

        return $date instanceof Carbon
            ? $date->copy()
            : Carbon::parse($date);
    }
}
