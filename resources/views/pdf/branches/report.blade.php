<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        h1 {
            margin: 0 0 6px;
            font-size: 20px;
        }

        h2 {
            margin: 22px 0 8px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 6px 7px;
            border-bottom: 1px solid #e5e7eb;
            text-align: right;
        }

        th:first-child,
        td:first-child {
            text-align: left;
        }

        thead th {
            background: #f3f4f6;
            font-weight: 700;
        }

        .meta {
            margin-bottom: 14px;
            color: #4b5563;
        }

        .total td {
            font-weight: 700;
            border-top: 2px solid #9ca3af;
        }
    </style>
</head>
<body>
    <h1>Raport oddziału: {{ $branch->label }}</h1>
    <div class="meta">
        Zakres spraw: {{ $report['filters']['category_label'] }};
        od: {{ $report['filters']['from'] ?? '-' }};
        do: {{ $report['filters']['to'] ?? '-' }}
    </div>

    @include('pdf.branches.report-section', [
        'title' => 'Raport miesięczny',
        'firstColumn' => 'Miesiąc',
        'rows' => $report['months'],
        'totals' => null,
        'includeActiveAtPeriodEnd' => false,
    ])

    @include('pdf.branches.report-section', [
        'title' => 'Podsumowanie roczne',
        'firstColumn' => 'Rok',
        'rows' => $report['years'],
        'totals' => $report['totals'],
        'includeActiveAtPeriodEnd' => true,
    ])
</body>
</html>
