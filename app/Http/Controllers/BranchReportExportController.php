<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Support\Branches\BranchReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BranchReportExportController extends Controller
{
    public function __invoke(Request $request, Branch $branch, string $format): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin() === true, 403);

        $report = BranchReport::make(
            $branch,
            $request->query('report_category', 'CHF'),
            $request->query('report_from'),
            $request->query('report_to'),
        )->toArray();

        return match ($format) {
            'xlsx' => $this->xlsx($branch, $report),
            'pdf' => $this->pdf($branch, $report),
            default => abort(404),
        };
    }

    private function xlsx(Branch $branch, array $report): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Raport');

        $row = 1;
        $sheet->setCellValue("A{$row}", 'Raport oddziału: '.$branch->label);
        $sheet->mergeCells("A{$row}:I{$row}");

        $row++;
        $sheet->setCellValue("A{$row}", 'Zakres spraw');
        $sheet->setCellValue("B{$row}", $report['filters']['category_label']);
        $sheet->setCellValue("D{$row}", 'Od');
        $sheet->setCellValue("E{$row}", $report['filters']['from'] ?? '-');
        $sheet->setCellValue("F{$row}", 'Do');
        $sheet->setCellValue("G{$row}", $report['filters']['to'] ?? '-');

        $row += 2;
        $row = $this->writeSection($sheet, $row, 'Raport miesięczny', 'Miesiąc', $report['months']);
        $row += 2;
        $this->writeSection($sheet, $row, 'Podsumowanie roczne', 'Rok', $report['years'], $report['totals'], true);

        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $this->filename($branch, 'xlsx'),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    private function pdf(Branch $branch, array $report): StreamedResponse
    {
        $pdf = Pdf::loadView('pdf.branches.report', [
            'branch' => $branch,
            'report' => $report,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $this->filename($branch, 'pdf'),
            ['Content-Type' => 'application/pdf'],
        );
    }

    private function writeSection(
        mixed $sheet,
        int $row,
        string $title,
        string $firstColumn,
        array $rows,
        ?array $totals = null,
        bool $includeActiveAtPeriodEnd = false,
    ): int {
        $lastColumn = $includeActiveAtPeriodEnd ? 'I' : 'H';

        $sheet->setCellValue("A{$row}", $title);
        $sheet->mergeCells("A{$row}:{$lastColumn}{$row}");

        $row++;
        $headers = [
            $firstColumn,
            'Przyjęte',
            'Zakończone',
            'Wydatki',
            'Przychody',
            'Bilans',
            'Przyszłe',
            'Potencjalne',
        ];

        if ($includeActiveAtPeriodEnd) {
            array_splice($headers, 3, 0, 'Aktywne na koniec roku');
        }

        $sheet->fromArray($headers, null, "A{$row}");

        foreach ($rows as $label => $data) {
            $row++;
            $this->writeReportRow($sheet, $row, $label, $data, $includeActiveAtPeriodEnd);
        }

        if ($totals) {
            $row++;
            $this->writeReportRow($sheet, $row, 'Razem', $totals, $includeActiveAtPeriodEnd, false);
        }

        return $row;
    }

    private function writeReportRow(
        mixed $sheet,
        int $row,
        string $label,
        array $data,
        bool $includeActiveAtPeriodEnd = false,
        bool $showActiveAtPeriodEnd = true,
    ): void {
        $values = [
            $label,
            $data['matters'],
            $data['ended'],
            $data['expense'],
            $data['paid'],
            $data['paid'] - $data['expense'],
            $data['future'],
            $data['potential'],
        ];

        if ($includeActiveAtPeriodEnd) {
            array_splice($values, 3, 0, $showActiveAtPeriodEnd ? $data['active_at_period_end'] : '-');
        }

        $sheet->fromArray($values, null, "A{$row}");
    }

    private function filename(Branch $branch, string $extension): string
    {
        return Str::slug('raport oddzial '.$branch->label.' '.now()->format('Y-m-d')).'.'.$extension;
    }
}
