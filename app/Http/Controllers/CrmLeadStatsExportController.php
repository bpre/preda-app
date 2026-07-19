<?php

namespace App\Http\Controllers;

use App\Services\Crm\LeadStatsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrmLeadStatsExportController extends Controller
{
    public function __invoke(Request $request, LeadStatsService $leadStats): StreamedResponse
    {
        $user = $request->user();

        abort_unless(
            $user !== null
                && method_exists($user, 'canAccessPredaPanel')
                && $user->canAccessPredaPanel('crm')
                && $user->can(LeadStatsService::EXPORT_PERMISSION),
            403,
        );

        $filters = $request->only([
            'leadDateRange',
            'leadCurrency',
            'leadType',
            'leadSource',
        ]);

        return response()->streamDownload(
            function () use ($leadStats, $filters): void {
                echo "\xEF\xBB\xBF";

                $handle = fopen('php://output', 'w');

                foreach ($leadStats->csvRows($filters) as $row) {
                    fputcsv($handle, $row, ';');
                }

                fclose($handle);
            },
            $leadStats->filename($filters),
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
